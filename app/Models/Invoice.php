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

        'fbr_status',
        'fbr_invoice_number',
        'fbr_submitted_at',

        'created_by',
        'updated_by',
        'sandbox_scenario_id',
        'sandbox_validation_status',
        'sandbox_validation_code',
        'sandbox_validated_at',

        'fbr_last_synced_at',
        'fbr_sync_status',
        'fbr_remote_status',
        'fbr_sync_message',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'fbr_submitted_at' => 'datetime',

            'sandbox_validated_at' => 'datetime',
            'fbr_last_synced_at' => 'datetime',
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



    public function fbrInvoiceSyncs()
    {
        return $this->hasMany(FbrInvoiceSync::class);
    }

    public function latestFbrInvoiceSync()
    {
        return $this->hasOne(FbrInvoiceSync::class)
            ->latestOfMany('synced_at');
    }

    public function fbrCorrectionDeadline()
    {
        return $this->fbr_submitted_at
            ? $this->fbr_submitted_at->copy()->addHours(72)
            : null;
    }

    public function isWithinFbrCorrectionWindow(): bool
    {
        $deadline = $this->fbrCorrectionDeadline();

        return (bool) (
            $this->fbr_invoice_number
            && $deadline
            && now()->lt($deadline)
        );
    }

    public function fbrCorrectionWindowExpired(): bool
    {
        $deadline = $this->fbrCorrectionDeadline();

        return (bool) (
            $this->fbr_invoice_number
            && $deadline
            && now()->gte($deadline)
        );
    }

    public function sandboxScenario()
    {
        return $this->belongsTo(
            FbrSandboxScenario::class,
            'sandbox_scenario_id'
        );
    }
}
