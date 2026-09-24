<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'customer_id',
        'invoice_number',
        'invoice_type',
        'invoice_date',

        'seller_ntn',
        'seller_strn',
        'seller_business_name',
        'seller_province',
        'seller_address',

        'buyer_ntn_cnic',
        'buyer_strn',
        'buyer_business_name',
        'buyer_registration_type',
        'buyer_province',
        'buyer_address',

        'subtotal',
        'sales_tax',
        'further_tax',
        'extra_tax',
        'fed',
        'discount',
        'grand_total',

        'status',

        'created_by',
        'updated_by',
        'sandbox_scenario_id',
        'sandbox_validation_status',
        'sandbox_validation_code',
        'sandbox_validated_at',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'fbr_submitted_at' => 'datetime',

            'sandbox_validated_at' => 'datetime',
        ];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class)
            ->orderBy('sort_order');
    }

    public function fbrSubmissions()
    {
        return $this->hasMany(FbrSubmission::class);
    }

    public function latestFbrSubmission()
    {
        return $this->hasOne(FbrSubmission::class)
            ->latestOfMany();
    }

    public function successfulSandboxSubmission()
    {
        return $this->hasOne(FbrSubmission::class)
            ->where('environment', 'sandbox')
            ->where('fbr_status_code', '00')
            ->whereNotNull('fbr_invoice_number')
            ->latestOfMany('submitted_at');
    }

    public function isFbrLocked(): bool
    {
        if (!empty($this->fbr_invoice_number)) {
            return true;
        }

        if ($this->relationLoaded('successfulSandboxSubmission')) {
            return $this->successfulSandboxSubmission !== null;
        }

        return $this->successfulSandboxSubmission()
            ->exists();
    }

    public function sandboxScenario()
    {
        return $this->belongsTo(
            FbrSandboxScenario::class,
            'sandbox_scenario_id'
        );
    }
}
