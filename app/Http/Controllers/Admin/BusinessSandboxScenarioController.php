<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\FbrSandboxScenario;
use Illuminate\Http\Request;

class BusinessSandboxScenarioController extends Controller
{
    public function edit(Business $business)
    {
        $scenarios = FbrSandboxScenario::where('active', true)
            ->orderBy('scenario_code')
            ->get();

        $assignedIds = $business
            ->sandboxScenarios()
            ->pluck('fbr_sandbox_scenarios.id')
            ->toArray();

        return view(
            'admin.businesses.sandbox-scenarios',
            compact(
                'business',
                'scenarios',
                'assignedIds'
            )
        );
    }


    public function update(
        Request $request,
        Business $business
    ) {
        $validated = $request->validate([
            'scenario_ids' => [
                'nullable',
                'array',
            ],

            'scenario_ids.*' => [
                'integer',
                'exists:fbr_sandbox_scenarios,id',
            ],
        ]);

        $selected =
            $validated['scenario_ids'] ?? [];


        $existing = $business
            ->sandboxScenarios()
            ->pluck('fbr_sandbox_scenarios.id')
            ->toArray();


        // Remove scenarios no longer assigned
        $business->sandboxScenarios()->detach(
            array_diff($existing, $selected)
        );


        // Add newly assigned scenarios
        foreach (
            array_diff($selected, $existing)
            as $scenarioId
        ) {

            $business->sandboxScenarios()->attach(
                $scenarioId,
                [
                    'status' => 'pending',
                    'assigned_at' => now(),
                ]
            );
        }


        return redirect()
            ->back()
            ->with(
                'success',
                'Sandbox scenarios updated successfully.'
            );
    }
}
