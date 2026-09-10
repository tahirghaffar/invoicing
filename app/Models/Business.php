<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Business extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'legal_name',

        'ntn',
        'strn',
        'registration_type',
        'province',
        'city',
        'address',

        'province_code',
        'principal_activity_code',
        'principal_activity_description',
        'profile_completed_at',

        'slug',
        'email',
        'phone',
        'logo_path',
        'timezone',
        'currency',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'profile_completed_at' => 'datetime',
        ];
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(BusinessUser::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'business_users')
            ->withPivot(['id', 'status', 'joined_at'])
            ->withTimestamps();
    }

    public function fbrCredential(): HasOne
    {
        return $this->hasOne(FbrCredential::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function sandboxScenarios()
    {
        return $this->belongsToMany(
            FbrSandboxScenario::class,
            'business_sandbox_scenarios',
            'business_id',
            'sandbox_scenario_id'
        )
            ->withPivot([
                'status',
                'assigned_at',
                'completed_at',
            ])
            ->withTimestamps();
    }
}
