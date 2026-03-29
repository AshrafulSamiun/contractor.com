<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $templates = EmailTemplate::query()
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->orWhere('is_global', true);
            })
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['success' => true, 'data' => $templates]);
    }

    public function show(Request $request, EmailTemplate $template)
    {
        $this->authorizeTemplate($request, $template);

        return response()->json(['success' => true, 'data' => $template]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'subject' => ['nullable', 'string', 'max:160'],
            'body_html' => ['nullable', 'string'],
            'body_text' => ['nullable', 'string'],
            'is_global' => ['nullable', 'boolean'],
        ]);

        if (!($request->user()->role === 'admin')) {
            $validated['is_global'] = false;
        }

        $template = EmailTemplate::create([
            'user_id' => $request->user()->id,
            ...$validated,
        ]);

        return response()->json(['success' => true, 'data' => $template], 201);
    }

    public function update(Request $request, EmailTemplate $template)
    {
        $this->authorizeTemplate($request, $template);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'subject' => ['nullable', 'string', 'max:160'],
            'body_html' => ['nullable', 'string'],
            'body_text' => ['nullable', 'string'],
            'is_global' => ['nullable', 'boolean'],
        ]);

        if (!($request->user()->role === 'admin')) {
            $validated['is_global'] = $template->is_global;
        }

        $template->update($validated);

        return response()->json(['success' => true, 'data' => $template]);
    }

    public function destroy(Request $request, EmailTemplate $template)
    {
        $this->authorizeTemplate($request, $template);
        $template->delete();

        return response()->json(['success' => true]);
    }

    protected function authorizeTemplate(Request $request, EmailTemplate $template): void
    {
        if ($template->is_global && $request->user()->role === 'admin') {
            return;
        }
        if ($template->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
