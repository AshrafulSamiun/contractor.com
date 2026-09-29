<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $table = 'vehicles';

    public const STATUS = [
        1 => 'In Service',
        2 => 'Available',
        3 => 'In Maintenance',
        4 => 'Out of Service',
    ];

    public const FUEL_TYPES = [
        'Gasoline',
        'Diesel',
        'Hybrid',
        'Electric',
        'CNG',
    ];

    protected $fillable = [
        'project_id',
        'vehicle_code',
        'vehicle_number',
        'make_brand',
        'model',
        'vehicle_type',
        'vehicle_year',
        'color',
        'vin',
        'fuel_type',
        'vehicle_keys_tag_no',
        'plate_number',
        'plate_expiry_date',
        'assignment_start_date',
        'purchase_date',
        'purchase_price',
        'purchase_invoice_number',
        'invoice_date',
        'sales_tax',
        'subtotal',
        'total_paid',
        'number_of_installments',
        'first_installment_amount',
        'first_installment_date',
        'last_installment_amount',
        'last_installment_date',
        'current_mileage',
        'insurance_provider',
        'policy_number',
        'insurance_start_date',
        'insurance_expiry_date',
        'insurance_expired',
        'assigned_driver',
        'in_service',
        'seller_name',
        'seller_company_name',
        'seller_phone',
        'seller_email',
        'seller_website',
        'car_photos',
        'driver_profiles',
        'insurance_documents',
        'safety_equipments',
        'status',
        'last_maintenance_date',
        'next_maintenance_date',
        'notes',
        'inserted_by',
        'updated_by',
        'is_deleted',
    ];

    protected $casts = [
        'project_id' => 'integer',
        'vehicle_year' => 'integer',
        'purchase_price' => 'decimal:2',
        'sales_tax' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'first_installment_amount' => 'decimal:2',
        'last_installment_amount' => 'decimal:2',
        'number_of_installments' => 'integer',
        'current_mileage' => 'integer',
        'status' => 'integer',
        'plate_expiry_date' => 'date',
        'assignment_start_date' => 'date',
        'purchase_date' => 'date',
        'invoice_date' => 'date',
        'insurance_start_date' => 'date',
        'insurance_expiry_date' => 'date',
        'insurance_expired' => 'boolean',
        'in_service' => 'boolean',
        'car_photos' => 'array',
        'driver_profiles' => 'array',
        'insurance_documents' => 'array',
        'safety_equipments' => 'array',
        'first_installment_date' => 'date',
        'last_installment_date' => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'inserted_by' => 'integer',
        'updated_by' => 'integer',
        'is_deleted' => 'boolean',
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? self::STATUS[2];
    }

    public function getDriverNameAttribute(): ?string
    {
        $driver = collect($this->driver_profiles ?? [])
            ->first(fn ($item) => ! empty($item['name']));

        return $driver['name'] ?? $this->assigned_driver;
    }

    public function getDriverInitialsAttribute(): ?string
    {
        $name = $this->driver_name;

        if (! $name) {
            return null;
        }

        return collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->implode('');
    }

    public function getPrimaryInsuranceDocumentAttribute(): ?array
    {
        $document = collect($this->insurance_documents ?? [])
            ->first(fn ($item) => ! empty($item['policy_number']) || ! empty($item['insurance_company']));

        return $document ?: null;
    }
}
