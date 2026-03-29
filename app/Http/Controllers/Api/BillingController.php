<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StripeBillingService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BillingController extends Controller
{
    public function checkout(Request $request, StripeBillingService $billing)
    {
        try {
            $validated = $request->validate([
                'plan' => ['required', 'in:basic,standard,enterprise'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }

        $user = $request->user();
        try {
            $url = $billing->createCheckoutSession($user, $validated['plan']);
            return response()->json([
                'success' => true,
                'data' => [
                    'url' => $url,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Stripe checkout failed.',
            ], 422);
        }
    }

    public function portal(Request $request, StripeBillingService $billing)
    {
        $user = $request->user();
        try {
            $url = $billing->createPortalSession($user);
            return response()->json([
                'success' => true,
                'data' => [
                    'url' => $url,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Stripe portal failed.',
            ], 422);
        }
    }
}
