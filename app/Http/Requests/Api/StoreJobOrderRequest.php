<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quotation_id' => 'nullable|integer|exists:quotations,id',
            'estimation_id' => 'nullable|integer|exists:estimations,id',
            'issue_date' => 'required|date',
            'status' => ['required', 'integer', Rule::in([1, 2, 3, 4, 5, 6])],
            'job_type' => ['nullable', 'integer', Rule::in([1, 2, 3, 4, 5, 6])],
            'job_description' => 'nullable|string',
            'customer_id' => 'nullable|integer|exists:account_holders,id',
            'job_site_id' => 'nullable|integer|exists:job_sites,id',
            'site_contact_person' => 'nullable|string|max:255',
            'site_contact_number' => 'nullable|string|max:255',
            'map_link' => 'nullable|string|max:500',
            'schedule_start_date' => 'nullable|date',
            'schedule_end_date' => 'nullable|date|after:schedule_start_date',
            'scope_of_work' => 'nullable|string',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
            'payment_method' => ['nullable', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'deposit_received' => 'nullable|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_status' => ['required', 'integer', Rule::in([1, 2, 3])],
            'progress' => 'nullable|integer|min:0|max:100',
            'converted_to_invoice' => 'boolean',
            'invoice_reference' => 'nullable|string|max:100',
            'job_order_time' => 'nullable|date_format:H:i',
            'priority' => 'nullable|string|max:30',
            'customer_approved' => 'boolean',
            'customer_type' => 'nullable|string|max:40',
            'site_floor_level' => 'nullable|string|max:80', 'site_suite_unit' => 'nullable|string|max:80',
            'site_city' => 'nullable|string|max:120', 'site_province' => 'nullable|string|max:120',
            'site_postal_code' => 'nullable|string|max:30', 'site_access_details' => 'nullable|string',
            'request_date' => 'nullable|date', 'request_time' => 'nullable|date_format:H:i',
            'requested_by' => 'nullable|string|max:255', 'request_method' => 'nullable|string|max:50',
            'reference_no' => 'nullable|string|max:120', 'account_no' => 'nullable|string|max:120',
            'alternate_date' => 'nullable|date', 'alternate_start_time' => 'nullable|date_format:H:i',
            'alternate_end_time' => 'nullable|date_format:H:i', 'alternate_duration' => 'nullable|string|max:80',
            'service_notes' => 'nullable|string', 'access_requirements' => 'nullable|string',
            'key_fob_required' => 'nullable|string|max:10', 'key_provided_by' => 'nullable|string|max:255',
            'equipment_required' => 'nullable|string', 'permits_required' => 'nullable|string|max:10',
            'permit_details' => 'nullable|string', 'safety_requirements' => 'nullable|string',
            'insurance_provided' => 'nullable|string|max:10', 'insurance_expiry_date' => 'nullable|date',
            'wcb_no' => 'nullable|string|max:120', 'customer_notes' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.item_name' => 'required|string|max:150',
            'details.*.item_description' => 'nullable|string|max:500',
            'details.*.quantity' => 'required|numeric|min:0.01',
            'details.*.unit_price' => 'required|numeric|min:0',
            'details.*.sale_tax_percentage' => 'nullable|numeric|min:0|max:100',
        ];
    }
}
