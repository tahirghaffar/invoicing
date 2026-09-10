<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'scope',
        'description',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permissions'
        )->withTimestamps();
    }

    public function systemUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_roles'
        )->withTimestamps();
    }

    public function businessUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            BusinessUser::class,
            'business_user_roles',
            'role_id',
            'business_user_id'
        )->withTimestamps();
    }
}
