<?php

namespace Database\Seeders;

use App\Models\FbrSandboxScenario;
use Illuminate\Database\Seeder;

class SandboxScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $codes = [
            'SN001',
            'SN002',
            'SN008',
            'SN018',
            'SN019',
            'SN026',
            'SN027',
            'SN028',
        ];

        foreach ($codes as $code) {

            FbrSandboxScenario::updateOrCreate(
                [
                    'scenario_code' => $code,
                ],
                [
                    'active' => true,
                ]
            );
        }
    }
}
