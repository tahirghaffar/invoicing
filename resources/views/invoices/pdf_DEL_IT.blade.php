<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <title>
        {{ $invoice->invoice_number }}
    </title>

</head>

<body>

@include(
    'invoices._document',
    ['invoice' => $invoice]
)

</body>

</html>
