<?php

namespace App\Services\FBR;

use App\Models\FbrCredential;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FbrProductionService
{
    protected function credential()
    {
        $business = app('currentBusiness');

        $credential = FbrCredential::where(
            'business_id',
            $business->id
        )->firstOrFail();

        if (!config('fbr.production_enabled')) {
            throw new RuntimeException(
                'Application-wide FBR production is disabled.'
            );
        }

        if (!$credential->production_enabled) {
            throw new RuntimeException(
                'Production is disabled for this business.'
            );
        }

        if (!$credential->production_token) {
            throw new RuntimeException(
                'Production token is not configured.'
            );
        }

        return $credential;
    }


    public function validate(array $payload): array
    {
        $credential = $this->credential();

        $response = Http::withToken(
            $credential->production_token
        )
            ->acceptJson()
            ->asJson()
            ->timeout(60)
            ->post(
                config('fbr.production_validate_url'),
                $payload
            );

        return [
            'http_status' => $response->status(),
            'response' => $response->json() ?? [
                    'raw' => $response->body(),
                ],
        ];
    }


    public function post(array $payload): array
    {
        $credential = $this->credential();

        // NO automatic retry for production POST.
        $response = Http::withToken(
            $credential->production_token
        )
            ->acceptJson()
            ->asJson()
            ->timeout(60)
            ->post(
                config('fbr.production_url'),
                $payload
            );

        return [
            'http_status' => $response->status(),
            'response' => $response->json() ?? [
                    'raw' => $response->body(),
                ],
        ];
    }
}
