<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StripeBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request, StripeBillingService $billing)
    {
        $secret = config('services.stripe.webhook_secret');
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            if (empty($secret)) {
                Log::warning('Stripe webhook secret is missing.');
                return response()->json(['success' => false], 400);
            }
            $event = $secret
                ? Webhook::constructEvent($payload, $sigHeader, $secret)
                : json_decode($payload, false);
        } catch (\Throwable $e) {
            Log::warning('Stripe webhook signature failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false], 400);
        }

        if (!$event) {
            return response()->json(['success' => false], 400);
        }

        $type = $event->type ?? '';
        $object = $event->data->object ?? null;

        if (!$object) {
            return response()->json(['success' => true]);
        }

        $customerId = $object->customer ?? null;
        $user = $customerId ? User::where('stripe_customer_id', $customerId)->first() : null;

        if (!$user && isset($object->metadata->user_id)) {
            $user = User::find($object->metadata->user_id);
        }

        if (!$user) {
            return response()->json(['success' => true]);
        }

        if (in_array($type, ['customer.subscription.created', 'customer.subscription.updated', 'customer.subscription.deleted'], true)) {
            $billing->syncSubscription($user, [
                'subscription_id' => $object->id ?? null,
                'status' => $object->status ?? null,
                'payment_method_id' => $object->default_payment_method ?? null,
            ]);
        }

        if (str_starts_with($type, 'invoice.')) {
            $taxAmount = collect($object->total_tax_amounts ?? [])
                ->sum(fn ($tax) => (int) ($tax->amount ?? 0));
            $planName = data_get($object, 'lines.data.0.description')
                ?: data_get($object, 'subscription_details.metadata.plan')
                ?: data_get($object, 'metadata.plan');

            $billing->recordInvoice($user, [
                'stripe_invoice_id' => $object->id ?? null,
                'stripe_subscription_id' => $object->subscription ?? null,
                'status' => $object->status ?? null,
                'currency' => $object->currency ?? null,
                'amount_due' => $object->amount_due ?? null,
                'amount_paid' => $object->amount_paid ?? null,
                'amount_remaining' => $object->amount_remaining ?? null,
                'plan_name' => $planName,
                'tax_amount' => $taxAmount ?: null,
                'period_start' => isset($object->period_start) ? now()->setTimestamp($object->period_start) : null,
                'period_end' => isset($object->period_end) ? now()->setTimestamp($object->period_end) : null,
                'hosted_invoice_url' => $object->hosted_invoice_url ?? null,
                'invoice_pdf' => $object->invoice_pdf ?? null,
            ]);
        }

        return response()->json(['success' => true]);
    }
}
