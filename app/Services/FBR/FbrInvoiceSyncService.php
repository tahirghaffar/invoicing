<?php

namespace App\Services\FBR;

use App\Models\FbrCredential;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FbrInvoiceSyncService
{
    public function fetch(Invoice $invoice): array
    {
        if (!$invoice->fbr_invoice_number) {
            throw new RuntimeException(
                'This invoice does not have an FBR production invoice number.'
            );
        }

        $business = app('currentBusiness');

        $credential = FbrCredential::where(
            'business_id',
            $business->id
        )->firstOrFail();

        if (!$credential->production_token) {
            throw new RuntimeException(
                'Production token is not configured for this business.'
            );
        }

        $url = trim((string) config(
            'fbr_invoice_sync.details_url'
        ));

        if ($url === '') {
            throw new RuntimeException(
                'FBR invoice-details API URL is not configured. Set FBR_INVOICE_DETAILS_URL in .env.'
            );
        }

        $method = strtoupper((string) config(
            'fbr_invoice_sync.details_method',
            'POST'
        ));

        if (!in_array($method, ['GET', 'POST'], true)) {
            throw new RuntimeException(
                'FBR_INVOICE_DETAILS_METHOD must be GET or POST.'
            );
        }

        $parameter = (string) config(
            'fbr_invoice_sync.details_parameter',
            'invoiceNumber'
        );

        $requestMode = strtolower((string) config(
            'fbr_invoice_sync.request_mode',
            'json'
        ));

        $timeout = max(
            10,
            (int) config(
                'fbr_invoice_sync.timeout',
                60
            )
        );

        $requestPayload = [
            $parameter => $invoice->fbr_invoice_number,
        ];

        $resolvedUrl = str_replace(
            [
                '{invoiceNumber}',
                '{invoice}',
            ],
            rawurlencode($invoice->fbr_invoice_number),
            $url
        );

        $containsPlaceholder =
            $resolvedUrl !== $url;

        $client = Http::withToken(
            $credential->production_token
        )
            ->acceptJson()
            ->timeout($timeout);

        if ($method === 'GET') {
            $response = $containsPlaceholder
                ? $client->get($resolvedUrl)
                : $client->get(
                    $resolvedUrl,
                    $requestPayload
                );
        } else {
            if (
                $requestMode === 'query'
                && !$containsPlaceholder
            ) {
                $response = $client->post(
                    $resolvedUrl . '?' . http_build_query(
                        $requestPayload
                    )
                );
            } else {
                $response = $client
                    ->asJson()
                    ->post(
                        $resolvedUrl,
                        $containsPlaceholder
                            ? []
                            : $requestPayload
                    );
            }
        }

        return [
            'method' => $method,
            'endpoint' => $resolvedUrl,
            'request_payload' => $containsPlaceholder
                ? []
                : $requestPayload,
            'http_status' => $response->status(),
            'successful' => $response->successful(),
            'response' => $response->json() ?? [
                'raw' => $response->body(),
            ],
        ];
    }


    public function normalize(
        array $response,
        string $expectedInvoiceNumber
    ): array {
        $invoiceData = $this->findInvoiceData(
            $response,
            $expectedInvoiceNumber
        );

        if (!$invoiceData) {
            throw new RuntimeException(
                'The FBR response did not contain recognizable invoice-detail data.'
            );
        }

        $items = $this->findItems($invoiceData);

        $normalizedItems = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $normalizedItems[] = [
                'hs_code' => $this->value($item, [
                    'hsCode',
                    'hs_code',
                    'hscode',
                ]),
                'product_description' => $this->value($item, [
                    'productDescription',
                    'product_description',
                    'description',
                    'itemDescription',
                ]),
                'rate_description' => $this->value($item, [
                    'rate',
                    'rateDescription',
                    'rate_description',
                ]),
                'tax_rate' => $this->numberOrNull(
                    $this->value($item, [
                        'taxRate',
                        'tax_rate',
                    ])
                ),
                'uom' => $this->value($item, [
                    'uoM',
                    'uom',
                    'unitOfMeasure',
                ]),
                'quantity' => $this->numberOrNull(
                    $this->value($item, [
                        'quantity',
                        'qty',
                    ])
                ),
                'line_total' => $this->numberOrNull(
                    $this->value($item, [
                        'totalValues',
                        'totalValue',
                        'lineTotal',
                        'line_total',
                    ])
                ),
                'value_sales_excluding_st' => $this->numberOrNull(
                    $this->value($item, [
                        'valueSalesExcludingST',
                        'value_sales_excluding_st',
                        'salesValueExcludingTax',
                    ])
                ),
                'fixed_notified_value_or_retail_price' => $this->numberOrNull(
                    $this->value($item, [
                        'fixedNotifiedValueOrRetailPrice',
                        'fixed_notified_value_or_retail_price',
                        'retailPrice',
                    ])
                ),
                'sales_tax_applicable' => $this->numberOrNull(
                    $this->value($item, [
                        'salesTaxApplicable',
                        'sales_tax_applicable',
                        'salesTax',
                    ])
                ),
                'sales_tax_withheld_at_source' => $this->numberOrNull(
                    $this->value($item, [
                        'salesTaxWithheldAtSource',
                        'sales_tax_withheld_at_source',
                        'stWithheldAtSource',
                    ])
                ),
                'extra_tax' => $this->numberOrNull(
                    $this->value($item, [
                        'extraTax',
                        'extra_tax',
                    ])
                ),
                'further_tax' => $this->numberOrNull(
                    $this->value($item, [
                        'furtherTax',
                        'further_tax',
                    ])
                ),
                'fed_payable' => $this->numberOrNull(
                    $this->value($item, [
                        'fedPayable',
                        'fed_payable',
                        'fed',
                    ])
                ),
                'discount' => $this->numberOrNull(
                    $this->value($item, [
                        'discount',
                    ])
                ),
                'sale_type' => $this->value($item, [
                    'saleType',
                    'sale_type',
                    'transactionType',
                ]),
                'sro_schedule_no' => $this->value($item, [
                    'sroScheduleNo',
                    'sro_schedule_no',
                ]),
                'sro_item_serial_no' => $this->value($item, [
                    'sroItemSerialNo',
                    'sro_item_serial_no',
                ]),
            ];
        }

        $normalized = [
            'fbr_invoice_number' => $this->value(
                $invoiceData,
                [
                    'invoiceNumber',
                    'fbrInvoiceNumber',
                    'fbr_invoice_number',
                    'invoiceNo',
                ]
            ),
            'remote_status' => $this->resolveRemoteStatus(
                $response,
                $invoiceData
            ),
            'invoice_type' => $this->value($invoiceData, [
                'invoiceType',
                'invoice_type',
            ]),
            'invoice_date' => $this->value($invoiceData, [
                'invoiceDate',
                'invoice_date',
            ]),
            'seller_ntn' => $this->value($invoiceData, [
                'sellerNTNCNIC',
                'sellerNTN',
                'seller_ntn',
            ]),
            'seller_business_name' => $this->value($invoiceData, [
                'sellerBusinessName',
                'seller_business_name',
            ]),
            'seller_province' => $this->value($invoiceData, [
                'sellerProvince',
                'seller_province',
            ]),
            'seller_address' => $this->value($invoiceData, [
                'sellerAddress',
                'seller_address',
            ]),
            'buyer_ntn_cnic' => $this->value($invoiceData, [
                'buyerNTNCNIC',
                'buyerNTN',
                'buyer_ntn_cnic',
            ]),
            'buyer_business_name' => $this->value($invoiceData, [
                'buyerBusinessName',
                'buyer_business_name',
            ]),
            'buyer_registration_type' => $this->value($invoiceData, [
                'buyerRegistrationType',
                'buyer_registration_type',
            ]),
            'buyer_province' => $this->value($invoiceData, [
                'buyerProvince',
                'buyer_province',
            ]),
            'buyer_address' => $this->value($invoiceData, [
                'buyerAddress',
                'buyer_address',
            ]),
            'subtotal' => $this->numberOrNull(
                $this->value($invoiceData, [
                    'subtotal',
                    'totalSalesValue',
                    'salesValue',
                    'valueSalesExcludingST',
                ])
            ),
            'sales_tax' => $this->numberOrNull(
                $this->value($invoiceData, [
                    'salesTax',
                    'totalSalesTaxApplicable',
                    'totalSalesTax',
                ])
            ),
            'further_tax' => $this->numberOrNull(
                $this->value($invoiceData, [
                    'furtherTax',
                    'totalFurtherTax',
                ])
            ),
            'extra_tax' => $this->numberOrNull(
                $this->value($invoiceData, [
                    'extraTax',
                    'totalExtraTax',
                ])
            ),
            'fed' => $this->numberOrNull(
                $this->value($invoiceData, [
                    'fed',
                    'fedPayable',
                    'totalFEDPayable',
                ])
            ),
            'discount' => $this->numberOrNull(
                $this->value($invoiceData, [
                    'discount',
                    'totalDiscount',
                ])
            ),
            'grand_total' => $this->numberOrNull(
                $this->value($invoiceData, [
                    'grandTotal',
                    'invoiceTotal',
                    'totalInvoiceValue',
                    'totalValues',
                    'totalValue',
                ])
            ),
            'items' => $normalizedItems,
        ];

        $this->deriveMissingTotals($normalized);

        if (
            $normalized['fbr_invoice_number']
            && trim((string) $normalized['fbr_invoice_number'])
                !== trim($expectedInvoiceNumber)
        ) {
            throw new RuntimeException(
                'FBR returned details for a different invoice number. Local data was not changed.'
            );
        }

        return $normalized;
    }


    public function apply(
        Invoice $invoice,
        array $remote
    ): array {
        $invoice = Invoice::whereKey($invoice->id)
            ->lockForUpdate()
            ->firstOrFail();

            $invoice->load('items');

            $oldSnapshot = $this->snapshot($invoice);

            $headerMap = [
                'invoice_type',
                'invoice_date',
                'seller_ntn',
                'seller_business_name',
                'seller_province',
                'seller_address',
                'buyer_ntn_cnic',
                'buyer_business_name',
                'buyer_registration_type',
                'buyer_province',
                'buyer_address',
                'subtotal',
                'sales_tax',
                'further_tax',
                'extra_tax',
                'fed',
                'discount',
                'grand_total',
            ];

            foreach ($headerMap as $field) {
                if ($remote[$field] !== null) {
                    $invoice->{$field} = $remote[$field];
                }
            }

            if (!empty($remote['remote_status'])) {
                $invoice->fbr_remote_status =
                    $remote['remote_status'];
            }

            if (!empty($remote['items'])) {
                $existingItems = $invoice->items
                    ->values();

                $invoice->items()->delete();

                foreach (
                    $remote['items']
                    as $index => $remoteItem
                ) {
                    $existing = $this->matchExistingItem(
                        $existingItems->all(),
                        $remoteItem,
                        $index
                    );

                    $productTemplate = null;

                    if (!$existing) {
                        $productTemplate = $this->findProductTemplate(
                            $invoice->business_id,
                            $remoteItem
                        );

                        if (
                            !$productTemplate
                            || !$productTemplate->transaction_type_id
                            || !$productTemplate->rate_id
                        ) {
                            throw new RuntimeException(
                                'FBR contains a line item that cannot be safely mapped to an existing local product. Local data was not changed. Review the raw FBR response in Sync History.'
                            );
                        }
                    }

                    $quantity = $remoteItem['quantity']
                        ?? (float) ($existing?->quantity ?? 0);

                    $valueExcluding =
                        $remoteItem['value_sales_excluding_st']
                        ?? (float) ($existing?->value_sales_excluding_st ?? 0);

                    $unitPrice = $quantity > 0
                        ? round(
                            $valueExcluding / $quantity,
                            4
                        )
                        : (float) ($existing?->unit_price ?? 0);

                    $rateDescription =
                        $remoteItem['rate_description']
                        ?? $existing?->rate_description;

                    $taxRate =
                        $remoteItem['tax_rate']
                        ?? $this->extractPercent(
                            $rateDescription
                        )
                        ?? (float) ($existing?->tax_rate ?? 0);

                    $discount =
                        $remoteItem['discount']
                        ?? (float) ($existing?->discount ?? 0);

                    $totalValue = round(
                        $valueExcluding + $discount,
                        4
                    );

                    $lineTotal =
                        $remoteItem['line_total']
                        ?? (float) ($existing?->line_total ?? 0);

                    $invoice->items()->create([
                        'business_id' => $invoice->business_id,
                        'product_id' => $existing?->product_id
                            ?? $productTemplate?->id,
                        'hs_code' => $remoteItem['hs_code']
                            ?? $existing?->hs_code,
                        'product_description' =>
                            $remoteItem['product_description']
                            ?? $existing?->product_description,
                        'rate_id' => $existing?->rate_id
                            ?? $productTemplate?->rate_id,
                        'rate_description' => $rateDescription,
                        'tax_rate' => $taxRate,
                        'uom_id' => $existing?->uom_id
                            ?? $productTemplate?->uom_id,
                        'uom' => $remoteItem['uom']
                            ?? $existing?->uom,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_value' => $totalValue,
                        'value_sales_excluding_st' =>
                            $valueExcluding,
                        'fixed_notified_value_or_retail_price' =>
                            $remoteItem['fixed_notified_value_or_retail_price']
                            ?? (float) ($existing?->fixed_notified_value_or_retail_price ?? 0),
                        'sales_tax_applicable' =>
                            $remoteItem['sales_tax_applicable']
                            ?? (float) ($existing?->sales_tax_applicable ?? 0),
                        'sales_tax_withheld_at_source' =>
                            $remoteItem['sales_tax_withheld_at_source']
                            ?? (float) ($existing?->sales_tax_withheld_at_source ?? 0),
                        'extra_tax' =>
                            $remoteItem['extra_tax']
                            ?? (float) ($existing?->extra_tax ?? 0),
                        'further_tax' =>
                            $remoteItem['further_tax']
                            ?? (float) ($existing?->further_tax ?? 0),
                        'fed_payable' =>
                            $remoteItem['fed_payable']
                            ?? (float) ($existing?->fed_payable ?? 0),
                        'discount' => $discount,
                        'transaction_type_id' =>
                            $existing?->transaction_type_id
                            ?? $productTemplate?->transaction_type_id,
                        'sale_type' =>
                            $remoteItem['sale_type']
                            ?? $existing?->sale_type,
                        'sro_schedule_no' =>
                            $remoteItem['sro_schedule_no']
                            ?? $existing?->sro_schedule_no,
                        'sro_item_serial_no' =>
                            $remoteItem['sro_item_serial_no']
                            ?? $existing?->sro_item_serial_no,
                        'line_total' => $lineTotal,
                        'sort_order' => $index,
                    ]);
                }
            }

            $invoice->save();
            $invoice->refresh()->load('items');

            $newSnapshot = $this->snapshot($invoice);

        return [
            'invoice' => $invoice,
            'old_snapshot' => $oldSnapshot,
            'new_snapshot' => $newSnapshot,
            'differences' => $this->differences(
                $oldSnapshot,
                $newSnapshot
            ),
        ];
    }


    public function snapshot(Invoice $invoice): array
    {
        $invoice->loadMissing('items');

        return [
            'invoice_type' => $invoice->invoice_type,
            'invoice_date' => $invoice->invoice_date?->format('Y-m-d'),
            'seller_ntn' => $invoice->seller_ntn,
            'seller_business_name' => $invoice->seller_business_name,
            'seller_province' => $invoice->seller_province,
            'seller_address' => $invoice->seller_address,
            'buyer_ntn_cnic' => $invoice->buyer_ntn_cnic,
            'buyer_business_name' => $invoice->buyer_business_name,
            'buyer_registration_type' => $invoice->buyer_registration_type,
            'buyer_province' => $invoice->buyer_province,
            'buyer_address' => $invoice->buyer_address,
            'subtotal' => $this->floatOrNull($invoice->subtotal),
            'sales_tax' => $this->floatOrNull($invoice->sales_tax),
            'further_tax' => $this->floatOrNull($invoice->further_tax),
            'extra_tax' => $this->floatOrNull($invoice->extra_tax),
            'fed' => $this->floatOrNull($invoice->fed),
            'discount' => $this->floatOrNull($invoice->discount),
            'grand_total' => $this->floatOrNull($invoice->grand_total),
            'fbr_remote_status' => $invoice->fbr_remote_status,
            'items' => $invoice->items
                ->values()
                ->map(function ($item) {
                    return [
                        'hs_code' => $item->hs_code,
                        'product_description' =>
                            $item->product_description,
                        'rate_description' =>
                            $item->rate_description,
                        'tax_rate' => $this->floatOrNull(
                            $item->tax_rate
                        ),
                        'uom' => $item->uom,
                        'quantity' => $this->floatOrNull(
                            $item->quantity
                        ),
                        'unit_price' => $this->floatOrNull(
                            $item->unit_price
                        ),
                        'total_value' => $this->floatOrNull(
                            $item->total_value
                        ),
                        'line_total' => $this->floatOrNull(
                            $item->line_total
                        ),
                        'value_sales_excluding_st' =>
                            $this->floatOrNull(
                                $item->value_sales_excluding_st
                            ),
                        'sales_tax_applicable' =>
                            $this->floatOrNull(
                                $item->sales_tax_applicable
                            ),
                        'extra_tax' => $this->floatOrNull(
                            $item->extra_tax
                        ),
                        'further_tax' => $this->floatOrNull(
                            $item->further_tax
                        ),
                        'fed_payable' => $this->floatOrNull(
                            $item->fed_payable
                        ),
                        'discount' => $this->floatOrNull(
                            $item->discount
                        ),
                        'sale_type' => $item->sale_type,
                        'sro_schedule_no' =>
                            $item->sro_schedule_no,
                        'sro_item_serial_no' =>
                            $item->sro_item_serial_no,
                    ];
                })
                ->all(),
        ];
    }


    public function differences(
        array $old,
        array $new
    ): array {
        $oldFlat = Arr::dot($old);
        $newFlat = Arr::dot($new);

        $paths = array_unique(array_merge(
            array_keys($oldFlat),
            array_keys($newFlat)
        ));

        $differences = [];

        foreach ($paths as $path) {
            $oldValue = $oldFlat[$path] ?? null;
            $newValue = $newFlat[$path] ?? null;

            if ($this->comparable($oldValue)
                === $this->comparable($newValue)) {
                continue;
            }

            $differences[] = [
                'field' => $path,
                'old' => $oldValue,
                'new' => $newValue,
            ];
        }

        return $differences;
    }


    private function findInvoiceData(
        array $response,
        string $expectedInvoiceNumber
    ): ?array {
        $queue = [$response];
        $fallback = null;

        while ($queue) {
            $current = array_shift($queue);

            if (!is_array($current)) {
                continue;
            }

            if ($this->looksLikeInvoice($current)) {
                $number = $this->value($current, [
                    'invoiceNumber',
                    'fbrInvoiceNumber',
                    'fbr_invoice_number',
                    'invoiceNo',
                ]);

                if (
                    $number
                    && trim((string) $number)
                        === trim($expectedInvoiceNumber)
                ) {
                    return $current;
                }

                $fallback ??= $current;
            }

            foreach ($current as $value) {
                if (is_array($value)) {
                    $queue[] = $value;
                }
            }
        }

        return $fallback;
    }


    private function looksLikeInvoice(array $data): bool
    {
        $keys = array_map(
            fn ($key) => $this->normalizeKey((string) $key),
            array_keys($data)
        );

        $expected = [
            'invoicenumber',
            'invoicedate',
            'sellerntncnic',
            'buyerbusinessname',
            'items',
            'invoiceitems',
        ];

        return count(array_intersect(
            $keys,
            $expected
        )) >= 2;
    }


    private function findItems(array $invoiceData): array
    {
        foreach ($invoiceData as $key => $value) {
            $normalized = $this->normalizeKey(
                (string) $key
            );

            if (
                in_array($normalized, [
                    'items',
                    'invoiceitems',
                    'itemdetails',
                    'lineitems',
                    'details',
                ], true)
                && is_array($value)
            ) {
                if ($this->isList($value)) {
                    return $value;
                }

                return [$value];
            }
        }

        return [];
    }


    private function resolveRemoteStatus(
        array $response,
        array $invoiceData
    ): ?string {
        $status = $this->value($invoiceData, [
            'invoiceStatus',
            'fbrStatus',
            'status',
        ]);

        if ($status !== null) {
            return (string) $status;
        }

        return $this->deepValue($response, [
            'invoiceStatus',
            'fbrStatus',
            'status',
        ]);
    }


    private function value(
        array $data,
        array $keys
    ): mixed {
        $wanted = array_map(
            fn ($key) => $this->normalizeKey($key),
            $keys
        );

        foreach ($data as $key => $value) {
            if (in_array(
                $this->normalizeKey((string) $key),
                $wanted,
                true
            )) {
                return $value === '' ? null : $value;
            }
        }

        return null;
    }


    private function deepValue(
        array $data,
        array $keys
    ): mixed {
        $direct = $this->value($data, $keys);

        if ($direct !== null) {
            return $direct;
        }

        foreach ($data as $value) {
            if (is_array($value)) {
                $found = $this->deepValue(
                    $value,
                    $keys
                );

                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }


    private function deriveMissingTotals(
        array &$normalized
    ): void {
        if (empty($normalized['items'])) {
            return;
        }

        $sum = function (string $key) use ($normalized) {
            return round(array_sum(array_map(
                fn ($item) => (float) ($item[$key] ?? 0),
                $normalized['items']
            )), 4);
        };

        $normalized['subtotal'] ??=
            $sum('value_sales_excluding_st');

        $normalized['sales_tax'] ??=
            $sum('sales_tax_applicable');

        $normalized['further_tax'] ??=
            $sum('further_tax');

        $normalized['extra_tax'] ??=
            $sum('extra_tax');

        $normalized['fed'] ??=
            $sum('fed_payable');

        $normalized['discount'] ??=
            $sum('discount');

        $normalized['grand_total'] ??=
            $sum('line_total');
    }


    private function matchExistingItem(
        array $existingItems,
        array $remoteItem,
        int $index
    ) {
        $remoteHs = trim((string) (
            $remoteItem['hs_code'] ?? ''
        ));

        $remoteDescription = mb_strtolower(
            trim((string) (
                $remoteItem['product_description'] ?? ''
            ))
        );

        foreach ($existingItems as $existing) {
            if (
                $remoteHs !== ''
                && trim((string) $existing->hs_code)
                    === $remoteHs
                && $remoteDescription !== ''
                && mb_strtolower(trim((string) $existing->product_description))
                    === $remoteDescription
            ) {
                return $existing;
            }
        }

        return $existingItems[$index] ?? null;
    }


    private function findProductTemplate(
        int $businessId,
        array $remoteItem
    ): ?Product {
        $hsCode = trim((string) (
            $remoteItem['hs_code'] ?? ''
        ));

        $description = trim((string) (
            $remoteItem['product_description'] ?? ''
        ));

        if ($hsCode === '') {
            return null;
        }

        $query = Product::where(
            'business_id',
            $businessId
        )
            ->where('status', 'active')
            ->where('hs_code', $hsCode);

        if ($description !== '') {
            $exact = (clone $query)
                ->where(function ($q) use ($description) {
                    $q->where('name', $description)
                        ->orWhere(
                            'description',
                            $description
                        );
                })
                ->first();

            if ($exact) {
                return $exact;
            }
        }

        $candidates = $query->limit(2)->get();

        return $candidates->count() === 1
            ? $candidates->first()
            : null;
    }


    private function numberOrNull(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $value = str_replace([',', '%'], '', $value);
        }

        return is_numeric($value)
            ? (float) $value
            : null;
    }


    private function extractPercent(
        mixed $value
    ): ?float {
        if (!is_string($value)) {
            return null;
        }

        if (preg_match(
            '/(-?\d+(?:\.\d+)?)\s*%/',
            $value,
            $matches
        )) {
            return (float) $matches[1];
        }

        return null;
    }


    private function floatOrNull(mixed $value): ?float
    {
        return $value === null
            ? null
            : (float) $value;
    }


    private function comparable(mixed $value): string
    {
        if (is_float($value) || is_int($value)) {
            return number_format((float) $value, 4, '.', '');
        }

        if ($value === null) {
            return '__NULL__';
        }

        return trim((string) $value);
    }


    private function normalizeKey(string $key): string
    {
        return strtolower((string) preg_replace(
            '/[^a-z0-9]/i',
            '',
            $key
        ));
    }


    private function isList(array $value): bool
    {
        return array_is_list($value);
    }
}
