<?php

return [

    'production_enabled' => env(
        'FBR_PRODUCTION_ENABLED',
        false
    ),

    'sandbox_url' => env(
        'FBR_SANDBOX_URL'
    ),

//    'production_url' => env(
//        'FBR_PRODUCTION_URL',
//        'https://gw.fbr.gov.pk/di_data/v1/di/postinvoicedata'
//    ),

    'sandbox_validate_url' => env(
        'FBR_SANDBOX_VALIDATE_URL',
        'https://gw.fbr.gov.pk/di_data/v1/di/validateinvoicedata_sb'
    ),

    'sandbox_post_url' => env(
        'FBR_SANDBOX_POST_URL',
        'https://gw.fbr.gov.pk/di_data/v1/di/postinvoicedata_sb'
    ),

    'production_enabled' => env('FBR_PRODUCTION_ENABLED', false),

    'production_validate_url' => env(
        'FBR_PRODUCTION_VALIDATE_URL',
        'https://gw.fbr.gov.pk/di_data/v1/di/validateinvoicedata'
    ),

    'production_url' => env(
        'FBR_PRODUCTION_URL',
        'https://gw.fbr.gov.pk/di_data/v1/di/postinvoicedata'
    ),

    'reference_urls' => [

        'provinces' =>
            'https://gw.fbr.gov.pk/pdi/v1/provinces',

        'document_types' =>
            'https://gw.fbr.gov.pk/pdi/v1/doctypecode',

        'hs_codes' =>
            'https://gw.fbr.gov.pk/pdi/v1/itemdesccode',

        'transaction_types' =>
            'https://gw.fbr.gov.pk/pdi/v1/transtypecode',

        'uoms' =>
            'https://gw.fbr.gov.pk/pdi/v1/uom',

        'rates' =>
            'https://gw.fbr.gov.pk/pdi/v2/SaleTypeToRate',

        'hs_uom' =>
            'https://gw.fbr.gov.pk/pdi/v2/HS_UOM',

    ],
];
