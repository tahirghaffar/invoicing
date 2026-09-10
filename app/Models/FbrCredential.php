<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FbrCredential extends Model
{
    protected $fillable = [
        'business_id',
        'environment',
        'sandbox_token',
        'production_token',
        'sandbox_api_url',
        'production_api_url',
        'production_enabled',
        'last_connection_test_at',
    ];

    protected function casts(): array
    {
        return [
            'sandbox_token' => 'encrypted',
            'production_token' => 'encrypted',
            'production_enabled' => 'boolean',
            'last_connection_test_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
