<?php

namespace App\Http\Controllers;

use App\Models\FbrHsCode;
use App\Models\FbrProvince;
use App\Models\FbrTransactionType;
use App\Models\FbrUom;
use App\Services\FBR\FbrReferenceService;
use Illuminate\Http\Request;

class FbrReferenceController extends Controller
{
    public function sync(
        Request $request,
        FbrReferenceService $service
    ) {
        $business = app('currentBusiness');

        $membership = $request->user()
            ->businessMemberships()
            ->where('business_id', $business->id)
            ->where('status', 'active')
            ->firstOrFail();

        if (
            ! $membership
                ->hasPermission('fbr.settings.update')
        ) {
            abort(403);
        }

        try {

            $result = $service->syncAll();

            return response()->json([
                'success' => true,
                'message' =>
                    'FBR reference data synchronized successfully.',
                'data' => $result,
            ]);

        } catch (\Throwable $e) {

            report($e);

            return response()->json([
                'success' => false,
                'message' =>
                    'Unable to synchronize FBR reference data.',
            ], 500);
        }
    }


    public function provinces()
    {
        return response()->json(
            FbrProvince::where('active', true)
                ->orderBy('description')
                ->get([
                    'code',
                    'description'
                ])
        );
    }


    public function transactionTypes()
    {
        return response()->json(
            FbrTransactionType::where('active', true)
                ->orderBy('description')
                ->get([
                    'fbr_id',
                    'description'
                ])
        );
    }


    public function uoms()
    {
        return response()->json(
            FbrUom::where('active', true)
                ->orderBy('description')
                ->get([
                    'fbr_id',
                    'description'
                ])
        );
    }


    public function hsCodes(Request $request)
    {
        $search = trim(
            $request->get('q', '')
        );

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $data = FbrHsCode::where('active', true)
            ->where(function ($query) use ($search) {

                $query
                    ->where(
                        'hs_code',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'description',
                        'like',
                        "%{$search}%"
                    );

            })
            ->limit(20)
            ->get([
                'hs_code',
                'description'
            ]);

        return response()->json($data);
    }


    public function rates(
        Request $request,
        FbrReferenceService $service
    ) {
        $validated = $request->validate([

            'transaction_type_id' => [
                'required',
                'integer',
            ],

            'date' => [
                'required',
                'date',
            ],

        ]);

        $business = app('currentBusiness');

        if (! $business->province_code) {

            return response()->json([
                'message' =>
                    'Please select the seller province in Business Profile first.'
            ], 422);
        }

        $rates = $service->rates(
            $validated['transaction_type_id'],
            $business->province_code,
            $validated['date']
        );

        return response()->json($rates);
    }
}
