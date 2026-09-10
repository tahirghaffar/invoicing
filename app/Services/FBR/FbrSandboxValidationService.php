<?php

namespace App\Services\FBR;

use App\Models\FbrCredential;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FbrSandboxValidationService
{
    public function validate(array $payload): array
    {
        $business = app('currentBusiness');

        $credential = FbrCredential::where(
            'business_id',
            $business->id
        )->firstOrFail();

        if (!$credential->sandbox_token) {
            throw new RuntimeException(
                'Sandbox token is not configured.'
            );
        }

        $response = Http::withToken(
            $credential->sandbox_token
        )
            ->acceptJson()
            ->asJson()
            ->timeout(60)
            ->post(
                config('fbr.sandbox_validate_url'),
                $payload
            );

        return [
            'http_status' => $response->status(),
            'successful' => $response->successful(),
            'response' => $response->json()
                ?? [
                    'raw' => $response->body()
                ],
        ];
    }
}
