<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FbrDocumentType extends Model
{
    protected $fillable = [
        'fbr_id',
        'description',
        'active',
    ];
}
