<?php

namespace App\Services\FBR;

use App\Models\Invoice;

class FbrPayloadBuilder
{
    public function build(
        Invoice $invoice,
        ?string $scenarioId = null
    ): array {

        $invoice->loadMissing('items');

        $payload = [

            'invoiceType' =>
                $invoice->invoice_type,

            'invoiceDate' =>
                $invoice->invoice_date->format('Y-m-d'),

            'sellerNTNCNIC' =>
                $invoice->seller_ntn,

            'sellerBusinessName' =>
                $invoice->seller_business_name,

            'sellerProvince' =>
                $invoice->seller_province,

            'sellerAddress' =>
                $invoice->seller_address,

            'buyerNTNCNIC' =>
                $invoice->buyer_ntn_cnic ?: '',

            'buyerBusinessName' =>
                $invoice->buyer_business_name,

            'buyerProvince' =>
                $invoice->buyer_province,

            'buyerAddress' =>
                $invoice->buyer_address,

            'buyerRegistrationType' =>
                ucfirst(
                    strtolower(
                        $invoice->buyer_registration_type
                    )
                ),

            'invoiceRefNo' => '',

        ];


        /*
        |--------------------------------------------------------------------------
        | Sandbox only
        |--------------------------------------------------------------------------
        */

        if ($scenarioId) {
            $payload['scenarioId'] = $scenarioId;
        }


        /*
        |--------------------------------------------------------------------------
        | Invoice Items
        |--------------------------------------------------------------------------
        */

        $payload['items'] = [];

        foreach ($invoice->items as $item) {

            $payload['items'][] = [

                'hsCode' =>
                    $item->hs_code,

                'productDescription' =>
                    $item->product_description,

                'rate' =>
                    $item->rate_description,

                'uoM' =>
                    $item->uom,

                'quantity' =>
                    (float) $item->quantity,

                'totalValues' =>
                    round(
                        (float) $item->line_total,
                        4
                    ),

                'valueSalesExcludingST' =>
                    round(
                        (float) $item->value_sales_excluding_st,
                        4
                    ),

                'fixedNotifiedValueOrRetailPrice' =>
                    round(
                        (float) $item->fixed_notified_value_or_retail_price,
                        4
                    ),

                'salesTaxApplicable' =>
                    round(
                        (float) $item->sales_tax_applicable,
                        4
                    ),

                'salesTaxWithheldAtSource' =>
                    round(
                        (float) $item->sales_tax_withheld_at_source,
                        4
                    ),

                'extraTax' =>
                    round(
                        (float) $item->extra_tax,
                        4
                    ),

                'furtherTax' =>
                    round(
                        (float) $item->further_tax,
                        4
                    ),

                'sroScheduleNo' =>
                    $item->sro_schedule_no ?: '',

                'fedPayable' =>
                    round(
                        (float) $item->fed_payable,
                        4
                    ),

                'discount' =>
                    round(
                        (float) $item->discount,
                        4
                    ),

                'saleType' =>
                    $item->sale_type,

                'sroItemSerialNo' =>
                    $item->sro_item_serial_no ?: '',
            ];
        }

        return $payload;
    }
}
