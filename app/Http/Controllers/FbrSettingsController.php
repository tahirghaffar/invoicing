<?php

namespace App\Http\Controllers;

use App\Models\FbrCredential;
use Illuminate\Http\Request;

class FbrSettingsController extends Controller
{
    public function edit(Request $request)
    {
        $business = app('currentBusiness');

        $membership = $request->user()
            ->businessMemberships()
            ->where('business_id', $business->id)
            ->where('status', 'active')
            ->firstOrFail();

        if (! $membership->hasPermission('fbr.settings.view')) {
            abort(403);
        }

        $credential = FbrCredential::firstOrCreate(
            [
                'business_id' => $business->id,
            ],
            [
                'environment' => 'sandbox',
                'production_api_url' => config('fbr.production_url'),
                'production_enabled' => false,
            ]
        );

        return view(
            'settings.fbr',
            compact('business', 'credential')
        );
    }


    public function update(Request $request)
    {
        $business = app('currentBusiness');

        $membership = $request->user()
            ->businessMemberships()
            ->where('business_id', $business->id)
            ->where('status', 'active')
            ->firstOrFail();

        if (! $membership->hasPermission('fbr.settings.update')) {
            abort(403);
        }

        $validated = $request->validate([

            'environment' => [
                'required',
                'in:sandbox,production',
            ],

            'sandbox_token' => [
                'nullable',
                'string',
            ],

            'production_token' => [
                'nullable',
                'string',
            ],

            'sandbox_api_url' => [
                'nullable',
                'url',
                'max:500',
            ],

            'production_api_url' => [
                'required',
                'url',
                'max:500',
            ],

            'production_enabled' => [
                'nullable',
                'boolean',
            ],

        ]);

        $credential = FbrCredential::firstOrCreate([
            'business_id' => $business->id,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Do not overwrite existing token when input is blank
        |--------------------------------------------------------------------------
        */

        if (empty($validated['sandbox_token'])) {
            unset($validated['sandbox_token']);
        }

        if (empty($validated['production_token'])) {
            unset($validated['production_token']);
        }

        $validated['production_enabled'] =
            $request->boolean('production_enabled');

        $credential->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'FBR/PRAL configuration updated successfully.',
        ]);
    }
}
