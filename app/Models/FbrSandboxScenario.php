<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FbrSandboxScenario extends Model
{
    protected $fillable = [
        'scenario_code',
        'description',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function businesses()
    {
        return $this->belongsToMany(
            Business::class,
            'business_sandbox_scenarios',
            'sandbox_scenario_id',
            'business_id'
        )
            ->withPivot([
                'status',
                'assigned_at',
                'completed_at',
            ])
            ->withTimestamps();
    }
}
