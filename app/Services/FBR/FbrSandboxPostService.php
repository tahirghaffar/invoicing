<?php

namespace App\Services\FBR;

use App\Models\FbrCredential;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FbrSandboxPostService
{
    public function post(array $payload): array
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

            // IMPORTANT: no automatic retry for POST
            ->post(
                config('fbr.sandbox_post_url'),
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
