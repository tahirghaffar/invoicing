<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FbrHsCode extends Model
{
    protected $fillable = [
        'hs_code',
        'description',
        'active',
    ];
}
