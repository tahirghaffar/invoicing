<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceImport extends Model
{
    protected $fillable = [
        'business_id',
        'uploaded_by',
        'original_filename',
        'stored_filename',
        'storage_disk',
        'storage_path',
        'mime_type',
        'file_extension',
        'file_kind',
        'source',
        'size_bytes',
        'status',
        'extracted_text',
        'parsed_data',
        'failure_message',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'parsed_data' => 'array',
            'size_bytes' => 'integer',
            'processed_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(
            Business::class
        );
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }

    public function isPdf(): bool
    {
        return $this->file_kind === 'pdf';
    }

    public function isImage(): bool
    {
        return $this->file_kind === 'image';
    }
}
