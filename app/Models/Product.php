<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'type',
        'sku',
        'name',
        'description',
        'hs_code',
        'uom',
        'sale_type',
        'tax_rate',
        'unit_price',
        'sro_schedule_no',
        'sro_item_serial_no',
        'fixed_notified_value_or_retail_price',
        'status',
        'uom_id',
        'transaction_type_id',
        'rate_id',
        'tax_rate_description',
    ];

    protected function casts(): array
    {
        return [
            'tax_rate' => 'decimal:4',
            'unit_price' => 'decimal:4',
            'fixed_notified_value_or_retail_price' => 'decimal:4',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
