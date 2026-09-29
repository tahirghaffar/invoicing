<?php

return [
    /*
    |--------------------------------------------------------------------------
    | FBR Invoice Details / Synchronization
    |--------------------------------------------------------------------------
    |
    | FBR's public DI documentation currently does not publish a production
    | invoice-details URL for the current API version. Keep the endpoint and
    | request shape configurable so the application can use the exact endpoint
    | made available to the taxpayer/integrator without changing application
    | code.
    |
    */

    'details_url' => env('FBR_INVOICE_DETAILS_URL'),

    'details_method' => strtoupper(
        env('FBR_INVOICE_DETAILS_METHOD', 'POST')
    ),

    'details_parameter' => env(
        'FBR_INVOICE_DETAILS_PARAMETER',
        'invoiceNumber'
    ),

    /* json | query */
    'request_mode' => strtolower(
        env('FBR_INVOICE_DETAILS_REQUEST_MODE', 'json')
    ),

    'timeout' => (int) env(
        'FBR_INVOICE_DETAILS_TIMEOUT',
        60
    ),

    'iris_url' => env(
        'FBR_IRIS_URL',
        'https://iris.fbr.gov.pk/'
    ),
];
