<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <title>
        {{ $invoice->invoice_number }}
    </title>

    <style>

        body {
            margin: 0;
            background: #fff;
        }

        .no-print {
            margin: 15px;
        }

        @media print {

            .no-print {
                display: none;
            }

        }

    </style>

</head>


<body>


<div class="no-print">

    <button
        type="button"
        onclick="window.print()"
    >
        Print Invoice
    </button>

</div>


@include(
    'invoices._document',
    ['invoice' => $invoice]
)


</body>

</html>
