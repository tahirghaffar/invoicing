<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceSequence extends Model
{
    protected $fillable = [
        'business_id',
        'prefix',
        'year',
        'current_number',
        'padding',
    ];
}
