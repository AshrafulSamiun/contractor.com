<?php

namespace App\Services;

use App\Models\BillingInvoice;
use App\Models\User;
use Stripe\StripeClient;

class StripeBillingService
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $secret = config('services.stripe.secret');
        $this->stripe = new StripeClient($secret ?: '');
    }

    public function ensureCustomer(User $user): string
    {
        if ($user->stripe_customer_id) {
            return $user->stripe_customer_id;
        }

        $customer = $this->stripe->customers->create([
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => [
                'user_id' => (string) $user->id,
            ],
        ]);

        $user->stripe_customer_id = $customer->id;
        $user->save();

        return $customer->id;
    }

    public function resolvePriceId(string $plan): ?string
    {
        return match ($plan) {
            'basic' => config('services.stripe.price_basic'),
            'standard' => config('services.stripe.price_standard'),
            'enterprise' => config('services.stripe.price_enterprise'),
            default => null,
        };
    }

    public function createCheckoutSession(User $user, string $plan): string
    {
        $priceId = $this->resolvePriceId($plan);
        if (!$priceId) {
            throw new \RuntimeException('Stripe price ID is not configured for this plan.');
        }

        $customerId = $this->ensureCustomer($user);

        $successUrl = config('services.stripe.success_url') ?: config('app.url') . '/account/billing?success=1';
        $cancelUrl = config('services.stripe.cancel_url') ?: config('app.url') . '/account/billing?canceled=1';

        $session = $this->stripe->checkout->sessions->create([
            'mode' => 'subscription',
            'customer' => $customerId,
            'line_items' => [
                [
                    'price' => $priceId,
                    'quantity' => 1,
                ],
            ],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'user_id' => (string) $user->id,
                'plan' => $plan,
            ],
            'subscription_data' => [
                'metadata' => [
                    'user_id' => (string) $user->id,
                    'plan' => $plan,
                ],
            ],
        ]);

        return $session->url;
    }

    public function createPortalSession(User $user): string
    {
        $customerId = $this->ensureCustomer($user);
        $returnUrl = config('services.stripe.portal_return_url') ?: config('app.url') . '/account/billing';

        $portal = $this->stripe->billingPortal->sessions->create([
            'customer' => $customerId,
            'return_url' => $returnUrl,
        ]);

        return $portal->url;
    }

    public function syncSubscription(User $user, array $data): void
    {
        $user->stripe_subscription_id = $data['subscription_id'] ?? $user->stripe_subscription_id;
        $user->stripe_subscription_status = $data['status'] ?? $user->stripe_subscription_status;
        $user->stripe_payment_method_id = $data['payment_method_id'] ?? $user->stripe_payment_method_id;
        $user->save();
    }

    public function recordInvoice(User $user, array $payload): void
    {
        BillingInvoice::updateOrCreate(
            ['stripe_invoice_id' => $payload['stripe_invoice_id']],
            [
                'user_id' => $user->id,
                'stripe_subscription_id' => $payload['stripe_subscription_id'] ?? null,
                'status' => $payload['status'] ?? null,
                'currency' => $payload['currency'] ?? null,
                'amount_due' => $payload['amount_due'] ?? null,
                'amount_paid' => $payload['amount_paid'] ?? null,
                'amount_remaining' => $payload['amount_remaining'] ?? null,
                'plan_name' => $payload['plan_name'] ?? null,
                'tax_amount' => $payload['tax_amount'] ?? null,
                'period_start' => $payload['period_start'] ?? null,
                'period_end' => $payload['period_end'] ?? null,
                'hosted_invoice_url' => $payload['hosted_invoice_url'] ?? null,
                'invoice_pdf' => $payload['invoice_pdf'] ?? null,
            ]
        );
    }
}
