<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobOrderDailyWorkLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'job_order_id' => 'required|integer|exists:job_orders,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'project_manager' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'entries' => 'required|array|min:1',
            'entries.*.work_date' => 'required|date',
            'entries.*.start_time' => 'nullable|date_format:H:i',
            'entries.*.end_time' => 'nullable|date_format:H:i',
            'entries.*.activity_description' => 'nullable|string|max:1000',
            'entries.*.wages' => 'nullable|numeric|min:0',
            'entries.*.materials' => 'nullable|numeric|min:0',
            'entries.*.overhead' => 'nullable|numeric|min:0',
        ];
    }
}
