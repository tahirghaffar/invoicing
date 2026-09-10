<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FbrProvince extends Model
{
    protected $fillable = [
        'code',
        'description',
        'active',
    ];
}
