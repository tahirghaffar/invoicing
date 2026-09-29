<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FbrInvoiceSync extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'response_payload' => 'array',
            'old_snapshot' => 'array',
            'new_snapshot' => 'array',
            'differences' => 'array',
            'synced_at' => 'datetime',
        ];
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
