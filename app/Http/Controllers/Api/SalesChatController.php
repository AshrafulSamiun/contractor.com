<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\SalesChatMessage;
use App\Models\SalesChatSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Throwable;

class SalesChatController extends Controller
{
    public function reply(Request $request)
    {
        $request->merge([
            'business_phone' => $this->normalizePhoneValue($request->input('business_phone')),
        ]);

        $request->validate([
            'message' => 'required|string|max:2000',
            'work_email' => 'nullable|email|max:255',
            'business_phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
            'country_id' => 'nullable|integer|exists:countries,id',
            'country' => 'nullable|string|max:120',
        ], [
            'business_phone.regex' => 'Phone number must be in E.164 format (e.g. +14165550100).',
        ]);
        $countryPayload = $this->resolveCountryPayload($request);

        $apiKey = config('services.groq.key');
        $fallbackMessage = 'Thanks for your message. A specialist will follow up within 1 business day.';
        $autoReply = $this->buildAutoReply((string) $request->input('message'));

        $email = $request->input('work_email');
        if ($email) {
            $domain = strtolower((string) substr(strrchr($email, '@'), 1));
            $blocked = [
                'gmail.com',
                'googlemail.com',
                'yahoo.com',
                'yahoo.co.uk',
                'yahoo.in',
                'yahoo.com.bd',
                'outlook.com',
                'hotmail.com',
                'live.com',
                'msn.com',
                'aol.com',
                'icloud.com',
                'me.com',
                'mac.com',
                'protonmail.com',
                'pm.me',
                'gmx.com',
                'mail.com',
                'yandex.com',
                'yandex.ru',
            ];
            if ($domain && in_array($domain, $blocked, true)) {
                return response()->json([
                    'error' => 'Please use a business email address (no gmail, yahoo, outlook, or hotmail).',
                ], 422);
            }
        }

        $chatNo = $request->input('chat_no');
        if (!$chatNo) {
            $chatNo = 'CHAT-' . now()->format('Ymd') . '-' . random_int(1000, 9999);
        }

        $chatDatetime = $request->input('chat_datetime');
        if ($chatDatetime === '') {
            $chatDatetime = null;
        }

        $kbPath = resource_path('data/sales_chat_kb.txt');
        $kbText = '';
        if (is_file($kbPath)) {
            $kbText = trim((string) file_get_contents($kbPath));
        }

        $systemPrompt = <<<PROMPT
You are a Parcel Management Advisor for DeskDrop.

Goals:
- Provide a "chat with our team" experience.
- Qualify users before support.
- Explain parcel operations, onboarding, pricing, and integrations.
- Guide users clearly and professionally.

Rules:
- Be concise, professional, and helpful.
- Do not ask unnecessary questions.
- Do not hallucinate features or information.
- If something is unclear, recommend checking documentation.
- Provide step-by-step guidance when appropriate.
- Reply in English by default. If the user asks for Bangla ("bangla" or "বাংলা"), reply in Bangla.

Style:
- Short paragraphs.
- Bullet points when useful.
- Code blocks only when needed.
PROMPT;

        if ($kbText !== '') {
            $systemPrompt .= "\n\nKnowledge base:\n" . $kbText;
        }

        $details = [
            'Chat No' => $chatNo,
            'Date-time' => $chatDatetime,
            'First name' => $request->input('first_name'),
            'Last name' => $request->input('last_name'),
            'Company name' => $request->input('company_name'),
            'Business email' => $request->input('work_email'),
            'Business phone' => $request->input('business_phone'),
            'Country' => $countryPayload['country'],
            'City' => $request->input('city'),
            'Call time' => $request->input('call_time'),
            'Inquiry' => $request->input('inquiry'),
        ];

        $detailLines = [];
        foreach ($details as $label => $value) {
            if ($value !== null && $value !== '') {
                $detailLines[] = $label . ': ' . $value;
            }
        }

        $userPrompt = "User details:\n" . implode("\n", $detailLines) . "\n\nUser message:\n" . $request->input('message');

        $session = null;
        $isNewSession = false;
        try {
            $session = SalesChatSession::updateOrCreate(
                ['chat_no' => $chatNo],
                [
                    'chat_datetime' => $chatDatetime,
                    'first_name' => $request->input('first_name'),
                    'last_name' => $request->input('last_name'),
                    'company_name' => $request->input('company_name'),
                    'work_email' => $request->input('work_email'),
                    'business_phone' => $request->input('business_phone'),
                    'country_id' => $countryPayload['country_id'],
                    'country' => $countryPayload['country'],
                    'city' => $request->input('city'),
                    'call_time' => $request->input('call_time'),
                    'inquiry' => $request->input('inquiry'),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent() ? substr($request->userAgent(), 0, 255) : null,
                ]
            );

            if ($session) {
                $isNewSession = $session->wasRecentlyCreated;
                SalesChatMessage::create([
                    'sales_chat_session_id' => $session->id,
                    'role' => 'user',
                    'message' => $request->input('message'),
                ]);
            }
        } catch (Throwable $e) {
            report($e);
        }

        $notifyEmail = config('services.sales_chat.notify_email');
        if ($session && $isNewSession && $notifyEmail) {
            logger()->info('Sales chat lead captured.', [
                'chat_no' => $chatNo,
                'email' => $request->input('work_email'),
            ]);
        }

        if (!$apiKey) {
            logger()->warning('GROQ_API_KEY is missing for sales chat. Auto-reply enabled.');
            if ($session) {
                try {
                    SalesChatMessage::create([
                        'sales_chat_session_id' => $session->id,
                        'role' => 'assistant',
                        'message' => $autoReply ?: $fallbackMessage,
                    ]);
                } catch (Throwable $e) {
                    report($e);
                }
            }

            return response()->json([
                'reply' => $autoReply ?: $fallbackMessage,
            ]);
        }

        $verifySsl = filter_var(config('services.groq.verify', true), FILTER_VALIDATE_BOOLEAN);

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(15)
                ->withOptions(['verify' => $verifySsl])
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => config('services.groq.model', 'llama-3.1-8b-instant'),
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 250,
                ]);
        } catch (Throwable $e) {
            $message = $fallbackMessage;
            if (stripos($e->getMessage(), 'cURL error 60') !== false) {
                logger()->warning('Sales chat SSL error: ' . $e->getMessage());
            }
            return response()->json([
                'error' => $message,
            ], 502);
        }

        if (!$response->ok()) {
            return response()->json([
                'error' => $fallbackMessage,
            ], 502);
        }

        $reply = data_get($response->json(), 'choices.0.message.content');
        if (!$reply) {
            return response()->json([
                'error' => $fallbackMessage,
            ], 502);
        }

        if ($session) {
            try {
                SalesChatMessage::create([
                    'sales_chat_session_id' => $session->id,
                    'role' => 'assistant',
                    'message' => trim($reply),
                ]);
            } catch (Throwable $e) {
                report($e);
            }
        }

        return response()->json([
            'reply' => trim($reply),
        ]);
    }

    private function buildAutoReply(string $message): string
    {
        $text = mb_strtolower(trim($message));

        if ($text === '') {
            return 'Tell us what you need help with (pricing, setup, notifications, or reporting) and we will guide you.';
        }

        if (str_contains($text, 'price') || str_contains($text, 'pricing') || str_contains($text, 'plan') || str_contains($text, 'cost')) {
            return "I can help with pricing and plans.\n- Number of buildings/units\n- Team size (admin/staff)\n- Required features\n\nShare these and I will guide you.";
        }

        if (str_contains($text, 'setup') || str_contains($text, 'onboard') || str_contains($text, 'start') || str_contains($text, 'getting started')) {
            return "Getting started steps:\n1) Create your account\n2) Complete account setup (facility + notifications)\n3) Add recipients and storage locations\n4) Start logging parcels\n\nIf you want, tell me your building size and I will tailor the steps.";
        }

        if (str_contains($text, 'notification') || str_contains($text, 'sms') || str_contains($text, 'email')) {
            return "DeskDrop supports notifications via email and SMS.\n- You can set preferences in Account Setup.\n- Delivery status updates are logged.\n\nDo you want SMS, email, or both?";
        }

        if (str_contains($text, 'recipient')) {
            return "Recipients:\n- Create recipients with type, contact, and unit info.\n- Mark active/inactive.\n\nDo you want to add a new recipient or search the list?";
        }

        if (str_contains($text, 'facility') || str_contains($text, 'property')) {
            return "Facilities/Properties:\n- Add building details, contact info, and status.\n- Manage multiple facilities from Profiles.\n\nTell me the facility name and type to get started.";
        }

        if (str_contains($text, 'storage') || str_contains($text, 'parcel room')) {
            return "Parcel Storage:\n- Define storage rooms/locations by facility and floor.\n- Mark active/inactive and add notes.\n\nDo you want to create a new storage or view the list?";
        }

        if (str_contains($text, 'courier')) {
            return "Couriers:\n- Add courier companies with contact and website.\n- Set active status.\n\nDo you want to add a new courier or view the list?";
        }

        if (str_contains($text, 'delivery item') || str_contains($text, 'item category')) {
            return "Parcel/Delivery Items:\n- Create item types with categories (e.g., Food, Medicine).\n- Set active status and notes.\n\nTell me the item name and category to add.";
        }

        if (str_contains($text, 'delivery method') || str_contains($text, 'pickup')) {
            return "Delivery Methods:\n- Define pickup/delivery methods (front desk, lockers, courier handoff).\n- Set active status and notes.\n\nShare the method name and type to add it.";
        }

        if (str_contains($text, 'seller') || str_contains($text, 'vendor')) {
            return "Sellers:\n- Add seller/vendor details with contact info and website.\n- Mark active/inactive.\n\nDo you want to add a new seller or view the list?";
        }

        if (str_contains($text, 'todo') || str_contains($text, 'task')) {
            return "To-Do Tasks:\n- Create tasks with due dates and reminders.\n- Track status and assignments.\n\nTell me the task details to add a new task.";
        }

        if (str_contains($text, 'user') || str_contains($text, 'staff') || str_contains($text, 'admin')) {
            return "User Management:\n- Admins can add users, set roles (admin/staff), and activate/deactivate.\n\nDo you want to add a user or manage roles?";
        }

        if (str_contains($text, 'report') || str_contains($text, 'export') || str_contains($text, 'csv')) {
            return "Reporting:\n- Export parcel reports as CSV from the dashboard.\n- Filter by status or date as needed.\n\nTell me which report you need and I will guide you.";
        }

        if (str_contains($text, 'integration') || str_contains($text, 'api') || str_contains($text, 'webhook')) {
            return "Integrations:\n- API access is available for parcel workflows.\n- Webhooks can be added if needed.\n\nTell me which system you want to connect.";
        }

        if (str_contains($text, 'security') || str_contains($text, 'secure') || str_contains($text, 'compliance')) {
            return "Security:\n- Role-based access for admin/staff.\n- Audit-friendly logs for parcel status updates.\n\nTell me your compliance needs and I will guide you.";
        }

        return "Thanks for reaching out. I can help with:\n- Parcel intake and tracking\n- Notifications (email/SMS)\n- Reporting and exports\n- Pricing and onboarding\n\nShare your use case and I will guide you.";
    }

    private function resolveCountryPayload(Request $request): array
    {
        $country = null;

        $countryId = $request->input('country_id');
        $countryName = $request->input('country');

        if (!empty($countryId)) {
            $country = Country::query()
                ->select(['id', 'country_name'])
                ->find($countryId);
        } elseif (!empty($countryName)) {
            $country = Country::query()
                ->select(['id', 'country_name'])
                ->where('country_name', $countryName)
                ->first();
        }

        if ($country) {
            return [
                'country_id' => $country->id,
                'country' => $country->country_name,
            ];
        }

        return [
            'country_id' => null,
            'country' => $countryName ?: null,
        ];
    }

    private function normalizePhoneValue(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        $normalized = preg_replace('/[^\d+]/', '', $trimmed);
        if (!is_string($normalized) || $normalized === '') {
            return null;
        }

        if (str_starts_with($normalized, '00')) {
            $normalized = '+' . substr($normalized, 2);
        }

        if (str_starts_with($normalized, '+')) {
            $normalized = '+' . preg_replace('/\D/', '', substr($normalized, 1));
        } else {
            $normalized = '+' . preg_replace('/\D/', '', $normalized);
        }

        return $normalized === '+' ? null : $normalized;
    }
}
