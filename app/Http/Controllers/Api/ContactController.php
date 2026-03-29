<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:120'],
                'company' => ['nullable', 'string', 'max:160'],
                'email' => ['required', 'email', 'max:190'],
                'phone' => ['nullable', 'string', 'max:30'],
                'subject' => ['nullable', 'string', 'max:160'],
                'message' => ['required', 'string', 'max:2000'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }

        $data['ip_address'] = $request->ip();
        $data['user_agent'] = $request->userAgent();
        $data['status'] = 'new';
        $data['source'] = 'web';

        ContactMessage::create($data);
        Log::info('Contact form submitted', ['email' => $data['email'], 'subject' => $data['subject'] ?? null]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for contacting us.',
        ]);
    }
}
