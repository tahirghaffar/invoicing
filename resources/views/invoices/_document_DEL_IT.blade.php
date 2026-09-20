<style>

    .invoice-document {
        background: #fff;
        color: #222;
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 12px;
        padding: 25px;
    }

    .invoice-document table {
        width: 100%;
        border-collapse: collapse;
    }

    .invoice-document th,
    .invoice-document td {
        padding: 7px;
        border: 1px solid #ddd;
        vertical-align: top;
    }

    .invoice-document th {
        background: #f2f2f2;
    }

    .invoice-title {
        font-size: 25px;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .company-name {
        font-size: 18px;
        font-weight: bold;
    }

    .info-table td {
        border: none;
        padding: 3px;
    }

    .party-table {
        margin-top: 20px;
        margin-bottom: 20px;
    }

    .items-table {
        margin-top: 20px;
    }

    .text-right {
        text-align: right;
    }

    .text-center {
        text-align: center;
    }

    .totals-table {
        width: 45% !important;
        margin-left: auto;
        margin-top: 20px;
    }

    .grand-total {
        font-size: 15px;
        font-weight: bold;
    }

    .small {
        font-size: 10px;
    }
    .fbr-header-table {
        width: auto !important;
        margin-left: auto;
        margin-bottom: 4px;
        border-collapse: collapse;
    }

    .fbr-header-table td {
        border: none !important;
        padding: 0 0 0 8px !important;
        vertical-align: middle;
    }

    .fbr-logo-top {
        width: 68px;
        height: auto;
        display: block;
    }

    .fbr-qr-top {
        width: 0.85in;
        height: 0.85in;
        display: block;
    }

    .fbr-number-top {
        font-size: 9px;
        line-height: 1.25;
        text-align: right;
        margin-top: 2px;
        margin-bottom: 4px;
        word-break: break-all;
    }

    .fbr-sandbox-label {
        font-size: 9px;
        font-weight: bold;
        text-align: right;
        margin-bottom: 7px;
    }
</style>
@php

    $businessLogo = null;

    if (
        $invoice->business
        &&
        $invoice->business->logo_path
    ) {

        $businessLogoPath =
            storage_path(
                'app/public/' .
                $invoice->business->logo_path
            );

        if (file_exists($businessLogoPath)) {

            $extension =
                pathinfo(
                    $businessLogoPath,
                    PATHINFO_EXTENSION
                );

            $businessLogo =
                'data:image/' .
                $extension .
                ';base64,' .
                base64_encode(
                    file_get_contents(
                        $businessLogoPath
                    )
                );
        }
    }

@endphp

<div class="invoice-document">


    <table class="info-table">

        <tr>

            <td width="60%">

                @if($businessLogo)

                    <div style="margin-bottom:8px;">

                        <img
                            src="{{ $businessLogo }}"
                            alt="Business Logo"
                            style="
                max-width:130px;
                max-height:65px;
                object-fit:contain;
            "
                        >

                    </div>

                @endif


                <div class="company-name">
                    {{ $invoice->seller_business_name }}
                </div>

                {{ $invoice->seller_address }}

                <br>

                {{ $invoice->seller_province }}

                <br><br>

                <strong>NTN:</strong>
                {{ $invoice->seller_ntn }}

                @if($invoice->seller_strn)

                    <br>

                    <strong>STRN:</strong>
                    {{ $invoice->seller_strn }}

                @endif

            </td>


            <td width="40%"
                style="text-align:right;">

                @if(!empty($displayFbrInvoiceNumber))

                    @php
                        $logoPath = public_path(
                            'images/fbr-digital-invoicing.png'
                        );

                        $fbrLogo = file_exists($logoPath)
                            ? 'data:image/png;base64,' .
                                base64_encode(
                                    file_get_contents($logoPath)
                                )
                            : null;
                    @endphp

                    <table class="fbr-header-table">
                        <tr>

                            @if($fbrLogo)
                                <td>
                                    <img
                                        src="{{ $fbrLogo }}"
                                        class="fbr-logo-top"
                                        alt="FBR Digital Invoicing"
                                    >
                                </td>
                            @endif

                            @if(!empty($qrCode))
                                <td>
                                    <img
                                        src="{{ $qrCode }}"
                                        class="fbr-qr-top"
                                        alt="FBR QR Code"
                                    >
                                </td>
                            @endif

                        </tr>
                    </table>

                    <div class="fbr-number-top">
                        <strong>FBR Invoice No:</strong><br>
                        {{ $displayFbrInvoiceNumber }}
                    </div>

                    @if(!empty($isSandbox))
                        <div class="fbr-sandbox-label">
                            SANDBOX / TEST INVOICE
                        </div>
                    @endif

                @endif

                <div class="invoice-title">
                    INVOICE
                </div>

                <strong>
                    {{ $invoice->invoice_number }}
                </strong>

                <br><br>

                <strong>Date:</strong>

                {{ $invoice->invoice_date->format('d-M-Y') }}

                <br>

                <strong>Status:</strong>

                {{ strtoupper($invoice->status) }}

            </td>

        </tr>

    </table>

    <table class="party-table">

        <tr>

            <th width="50%">
                Seller
            </th>

            <th width="50%">
                Buyer
            </th>

        </tr>


        <tr>

            <td>

                <strong>
                    {{ $invoice->seller_business_name }}
                </strong>

                <br>

                NTN:
                {{ $invoice->seller_ntn }}

                @if($invoice->seller_strn)

                    <br>

                    STRN:
                    {{ $invoice->seller_strn }}

                @endif

                <br>

                {{ $invoice->seller_address }}

                <br>

                {{ $invoice->seller_province }}

            </td>


            <td>

                <strong>
                    {{ $invoice->buyer_business_name }}
                </strong>

                <br>

                Registration:
                {{ ucfirst(
                    $invoice->buyer_registration_type
                ) }}

                <br>

                NTN/CNIC:
                {{ $invoice->buyer_ntn_cnic ?: '-' }}

                @if($invoice->buyer_strn)

                    <br>

                    STRN:
                    {{ $invoice->buyer_strn }}

                @endif

                <br>

                {{ $invoice->buyer_address }}

                <br>

                {{ $invoice->buyer_province }}

            </td>

        </tr>

    </table>


    <table class="items-table">

        <thead>

        <tr>

            <th width="4%">#</th>

            <th width="13%">
                HS Code
            </th>

            <th>
                Description
            </th>

            <th width="10%">
                UOM
            </th>

            <th width="8%">
                Qty
            </th>

            <th width="11%">
                Unit Price
            </th>

            <th width="8%">
                Rate
            </th>

            <th width="11%">
                Tax
            </th>

            <th width="12%">
                Total
            </th>

        </tr>

        </thead>


        <tbody>

        @foreach($invoice->items as $item)

            <tr>

                <td class="text-center">
                    {{ $loop->iteration }}
                </td>

                <td>
                    {{ $item->hs_code }}
                </td>

                <td>

                    {{ $item->product_description }}

                    <br>

                    <span class="small">
            {{ $item->sale_type }}
        </span>

                </td>

                <td>
                    {{ $item->uom }}
                </td>

                <td class="text-right">

                    {{ rtrim(
                        rtrim(
                            number_format(
                                (float)$item->quantity,
                                4,
                                '.',
                                ''
                            ),
                            '0'
                        ),
                        '.'
                    ) }}

                </td>

                <td class="text-right">

                    {{ number_format(
                        (float)$item->unit_price,
                        2
                    ) }}

                </td>

                <td class="text-right">

                    {{ $item->rate_description }}

                </td>

                <td class="text-right">

                    {{ number_format(
                        (float)$item->sales_tax_applicable,
                        2
                    ) }}

                </td>

                <td class="text-right">

                    {{ number_format(
                        (float)$item->line_total,
                        2
                    ) }}

                </td>

            </tr>

        @endforeach

        </tbody>

    </table>


    <table class="totals-table">

        <tr>

            <td>
                Subtotal
            </td>

            <td class="text-right">
                {{ number_format(
                    (float)$invoice->subtotal,
                    2
                ) }}
            </td>

        </tr>


        <tr>

            <td>
                Sales Tax
            </td>

            <td class="text-right">
                {{ number_format(
                    (float)$invoice->sales_tax,
                    2
                ) }}
            </td>

        </tr>


        @if((float)$invoice->further_tax > 0)

            <tr>

                <td>
                    Further Tax
                </td>

                <td class="text-right">
                    {{ number_format(
                        (float)$invoice->further_tax,
                        2
                    ) }}
                </td>

            </tr>

        @endif


        @if((float)$invoice->extra_tax > 0)

            <tr>

                <td>
                    Extra Tax
                </td>

                <td class="text-right">
                    {{ number_format(
                        (float)$invoice->extra_tax,
                        2
                    ) }}
                </td>

            </tr>

        @endif


        @if((float)$invoice->fed > 0)

            <tr>

                <td>
                    FED
                </td>

                <td class="text-right">
                    {{ number_format(
                        (float)$invoice->fed,
                        2
                    ) }}
                </td>

            </tr>

        @endif


        @if((float)$invoice->discount > 0)

            <tr>

                <td>
                    Discount
                </td>

                <td class="text-right">
                    {{ number_format(
                        (float)$invoice->discount,
                        2
                    ) }}
                </td>

            </tr>

        @endif


        <tr class="grand-total">

            <td>
                Grand Total
            </td>

            <td class="text-right">

                {{ number_format(
                    (float)$invoice->grand_total,
                    2
                ) }}

            </td>

        </tr>

    </table>


    <br><br>

    <div class="small">

        This invoice was generated through the
        Digital Invoicing System.

        @if(!$invoice->fbr_invoice_number)

            <br>

            <strong>
                FBR submission pending / not yet submitted.
            </strong>

        @endif

    </div>


</div>
