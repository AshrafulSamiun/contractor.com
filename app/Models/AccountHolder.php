<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountHolder extends Model
{
    protected $fillable = [
        'project_id',
        'system_prefix',
        'system_no',
        'account_type',
        'customer_type',
        'total_invoices',
        'total_outstanding',
        'current_balance',
        'employee_id', 'department', 'position', 'hire_date', 'account_no', 'screening_expires_at',
        'account_name',
        'company_name',
        'legal_company_name', 'incorporation_no', 'year_established', 'primary_contact_name', 'job_title', 'mobile_phone', 'accounts_email', 'business_fields', 'industry', 'supplier_type', 'credit_limit', 'payment_terms', 'invoice_terms', 'seller_notes',
        'liability_insurance','insurance_company','policy_number','policy_expiry_date','license_no','license_type','license_expiry_date','license_issued_by','aging_0_30','aging_31_60','aging_61_90','aging_over_90',
        'business_number',
        'tax_id_no',
        'currency_id',
        'house_number',
        'street_number',
        'city',
        'state',
        'country',
        'zip_code',
        'office_phone',
        'cell_phone',
        'email',
        'website',
        'prefer_contact_method',
        'linked_transaction_sales',
        'linked_transaction_purchase',
        'status_active',
        'inserted_by',
        'updated_by',
        'is_deleted',
    ];

    protected $casts = [
        'system_prefix' => 'string',
        'account_type' => 'integer',
        'prefer_contact_method' => 'integer',
        'status_active' => 'boolean',
        'total_invoices' => 'integer',
        'total_outstanding' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'hire_date' => 'date', 'screening_expires_at' => 'date',
    ];

    public function getAddressAttribute(): string
    {
        return collect([
            $this->house_number,
            $this->street_number,
            $this->city,
            $this->state,
            $this->country,
            $this->zip_code,
        ])->filter()->implode(', ');
    }
}
