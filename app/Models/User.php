<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'company_name',
        'email',
        'phone',
        'country_id',
        'country',
        'postal_code',
        'verify_via',
        'avatar_url',
        'role',
        'is_active',
        'project_id',
        'selected_plan',
        'email_otp',
        'phone_otp',
        'otp_expires_at',
        'account_setup_completed_at',
        'stripe_customer_id',
        'stripe_subscription_id',
        'stripe_subscription_status',
        'stripe_payment_method_id',
        'password',
        'security_pin_code',
        'expiry_date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'security_pin_code',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'account_setup_completed_at' => 'datetime',
            'password' => 'hashed',
            'security_pin_code' => 'hashed',
            'expiry_date' => 'date',
        ];
    }
}
