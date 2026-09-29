<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEstimationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $estimationId = $this->route('estimation');

        return [
            'issue_date' => 'required|date',
            'expire_date' => 'required|date|after:issue_date',
            'currency' => ['required', 'integer', Rule::in([1, 2, 3, 4])],
            'status' => ['required', 'integer', Rule::in([1, 2, 3, 4])],
            'job_type' => ['required', 'integer', Rule::in([1, 2, 3, 4, 5, 6])],
            'job_description' => 'nullable|string',
            'customer_id' => 'nullable|integer|exists:account_holders,id',
            'job_site_id' => 'nullable|integer|exists:job_sites,id',
            'schedule_start_date' => 'required|date',
            'schedule_end_date' => 'required|date|after:schedule_start_date',
            'scope_of_work' => 'nullable|string',
            'sub_total' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
            'payment_method' => ['nullable', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'deposit_required' => 'boolean',
            'payment_term' => 'nullable|string',
            'customer_approval_date' => 'nullable|date',
            'customer_approve_by' => 'nullable|string|max:300',
            'approval_method' => ['nullable', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'convert_to_job_order' => 'boolean',
            'customer_type' => 'nullable|string|max:40', 'site_floor_level' => 'nullable|string|max:80', 'site_suite_unit' => 'nullable|string|max:80',
            'site_city' => 'nullable|string|max:120', 'site_province' => 'nullable|string|max:120', 'site_postal_code' => 'nullable|string|max:30', 'site_access_details' => 'nullable|string',
            'tax_rate' => 'nullable|numeric|min:0|max:100', 'tax_registration_no' => 'nullable|string|max:120', 'payment_methods' => 'nullable|array', 'payment_methods.*' => 'string|max:60',
            'deposit_percentage' => 'nullable|numeric|min:0|max:100', 'deposit_amount' => 'nullable|numeric|min:0', 'notes_to_customer' => 'nullable|string',
            'invoice_to' => 'nullable|string|max:120', 'invoice_title' => 'nullable|string|max:255', 'invoice_prefix' => 'nullable|string|max:30', 'invoice_next_number' => 'nullable|integer|min:1', 'create_invoice_after_approval' => 'boolean',
            'details' => 'required|array|min:1',
            'details.*.item_name' => 'required|string|max:150',
            'details.*.item_description' => 'nullable|string|max:500',
            'details.*.quantity' => 'required|numeric|min:0.01',
            'details.*.unit_price' => 'required|numeric|min:0',
            'details.*.sale_tax_percentage' => 'nullable|numeric|min:0|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'details.required' => 'At least one estimation detail is required.',
            'details.*.item_name.required' => 'Item name is required for each detail.',
            'details.*.quantity.required' => 'Quantity is required for each detail.',
            'details.*.unit_price.required' => 'Unit price is required for each detail.',
            'expire_date.after' => 'Expiry date must be after issue date.',
            'schedule_end_date.after' => 'End date must be after start date.',
        ];
    }
}
