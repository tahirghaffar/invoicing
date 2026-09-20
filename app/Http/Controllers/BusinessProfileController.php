<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FbrProvince;
use Illuminate\Support\Facades\Storage;

class BusinessProfileController extends Controller
{
    public function edit(Request $request)
    {
        $business = app('currentBusiness');

        $membership = $request->user()
            ->businessMemberships()
            ->where('business_id', $business->id)
            ->where('status', 'active')
            ->firstOrFail();

        if (! $membership->hasPermission('business.profile.view')) {
            abort(403);
        }

        $provinces = FbrProvince::where('active', true)
            ->orderBy('description')
            ->get();

        return view(
            'settings.business-profile',
            compact(
                'business',
                'provinces'
            )
        );
        //return view('settings.business-profile', compact('business'));
    }


    public function update(Request $request)
    {
        $business = app('currentBusiness');

        $membership = $request->user()
            ->businessMemberships()
            ->where('business_id', $business->id)
            ->where('status', 'active')
            ->firstOrFail();

        if (! $membership->hasPermission('business.profile.update')) {
            abort(403);
        }

        $validated = $request->validate([

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'legal_name' => [
                'required',
                'string',
                'max:255',
            ],

            'ntn' => [
                'required',
                'string',
                'max:30',
            ],

            'strn' => [
                'nullable',
                'string',
                'max:30',
            ],

            'registration_type' => [
                'required',
                'in:registered',
            ],

            'province_code' => [
                'required',
                'integer',
                'exists:fbr_provinces,code',
            ],

            'city' => [
                'required',
                'string',
                'max:100',
            ],

            'address' => [
                'required',
                'string',
                'max:1000',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'principal_activity_code' => [
                'nullable',
                'string',
                'max:50',
            ],

            'principal_activity_description' => [
                'nullable',
                'string',
                'max:255',
            ],

        ]);

        $validated['profile_completed_at'] = now();

        $province = FbrProvince::where(
            'code',
            $validated['province_code']
        )->firstOrFail();

        $validated['province'] = $province->description;

        if ($request->hasFile('logo')) {

            /*
            |--------------------------------------------------------------------------
            | Delete previous logo
            |--------------------------------------------------------------------------
            */

            if (
                $business->logo_path
                &&
                Storage::disk('public')->exists(
                    $business->logo_path
                )
            ) {

                Storage::disk('public')->delete(
                    $business->logo_path
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Store new logo
            |--------------------------------------------------------------------------
            */

            $logoPath = $request
                ->file('logo')
                ->store(
                    'business-logos/' . $business->id,
                    'public'
                );

            $business->logo_path = $logoPath;
        }

        $business->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Business profile updated successfully.',
        ]);
    }
}
