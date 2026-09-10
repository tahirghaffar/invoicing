<?php

namespace App\Services\FBR;

use App\Models\FbrCredential;
use App\Models\FbrDocumentType;
use App\Models\FbrHsCode;
use App\Models\FbrProvince;
use App\Models\FbrTransactionType;
use App\Models\FbrUom;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FbrReferenceService
{
    private function credential(): FbrCredential
    {
        $business = app('currentBusiness');

        return FbrCredential::where(
            'business_id',
            $business->id
        )->firstOrFail();
    }


    private function token(): string
    {
        $credential = $this->credential();

        if (
            $credential->environment === 'production'
            && $credential->production_token
        ) {
            return $credential->production_token;
        }

        if ($credential->sandbox_token) {
            return $credential->sandbox_token;
        }

        if ($credential->production_token) {
            return $credential->production_token;
        }

        throw new \RuntimeException(
            'No FBR/PRAL security token is configured.'
        );
    }


    private function get(string $url, array $query = []): array
    {
        return Http::withToken($this->token())
            ->acceptJson()
            ->timeout(60)
            ->retry(2, 500)
            ->get($url, $query)
            ->throw()
            ->json();
    }


    public function syncAll(): array
    {
        return [
            'provinces' =>
                $this->syncProvinces(),

            'document_types' =>
                $this->syncDocumentTypes(),

            'hs_codes' =>
                $this->syncHsCodes(),

            'transaction_types' =>
                $this->syncTransactionTypes(),

            'uoms' =>
                $this->syncUoms(),
        ];
    }


    public function syncProvinces(): int
    {
        $data = $this->get(
            config('fbr.reference_urls.provinces')
        );

        foreach ($data as $row) {

            FbrProvince::updateOrCreate(
                [
                    'code' =>
                        $row['stateProvinceCode']
                ],
                [
                    'description' =>
                        $row['stateProvinceDesc'],

                    'active' => true,
                ]
            );
        }

        return count($data);
    }


    public function syncDocumentTypes(): int
    {
        $data = $this->get(
            config('fbr.reference_urls.document_types')
        );

        foreach ($data as $row) {

            FbrDocumentType::updateOrCreate(
                [
                    'fbr_id' =>
                        $row['docTypeId']
                ],
                [
                    'description' =>
                        $row['docDescription'],

                    'active' => true,
                ]
            );
        }

        return count($data);
    }


    public function syncHsCodes(): int
    {
        $data = $this->get(
            config('fbr.reference_urls.hs_codes')
        );

        foreach ($data as $row) {

            FbrHsCode::updateOrCreate(
                [
                    'hs_code' =>
                        $row['hS_CODE']
                ],
                [
                    'description' =>
                        $row['description'] ?? null,

                    'active' => true,
                ]
            );
        }

        return count($data);
    }


    public function syncTransactionTypes(): int
    {
        $data = $this->get(
            config('fbr.reference_urls.transaction_types')
        );

        foreach ($data as $row) {

            FbrTransactionType::updateOrCreate(
                [
                    'fbr_id' =>
                        $row['transactioN_TYPE_ID']
                ],
                [
                    'description' =>
                        $row['transactioN_DESC'],

                    'active' => true,
                ]
            );
        }

        return count($data);
    }


    public function syncUoms(): int
    {
        $data = $this->get(
            config('fbr.reference_urls.uoms')
        );

        foreach ($data as $row) {

            FbrUom::updateOrCreate(
                [
                    'fbr_id' =>
                        $row['uoM_ID']
                ],
                [
                    'description' =>
                        $row['description'],

                    'active' => true,
                ]
            );
        }

        return count($data);
    }


    public function rates(
        int $transactionTypeId,
        int $provinceCode,
        string $date
    ): array {

        $formattedDate =
            Carbon::parse($date)->format('d-M-Y');

        $cacheKey =
            'fbr_rates_' .
            $transactionTypeId . '_' .
            $provinceCode . '_' .
            $formattedDate;


        return Cache::remember(
            $cacheKey,
            now()->addHours(6),
            function () use (
                $transactionTypeId,
                $provinceCode,
                $formattedDate
            ) {

                return $this->get(
                    config('fbr.reference_urls.rates'),
                    [
                        'date' =>
                            $formattedDate,

                        'transTypeId' =>
                            $transactionTypeId,

                        'originationSupplier' =>
                            $provinceCode,
                    ]
                );

            }
        );
    }


    public function hsUoms(
        string $hsCode,
        int $annexureId
    ): array {

        return $this->get(
            config('fbr.reference_urls.hs_uom'),
            [
                'hs_code' =>
                    $hsCode,

                'annexure_id' =>
                    $annexureId,
            ]
        );
    }
}
