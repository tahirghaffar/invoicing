<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'business_name',
        'contact_person',
        'registration_type',
        'ntn_cnic',
        'strn',
        'province',
        'province_code',
        'city',
        'address',
        'phone',
        'email',
        'status',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
