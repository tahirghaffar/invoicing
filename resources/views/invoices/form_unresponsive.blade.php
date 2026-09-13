@extends('layouts.app')

@section('title', 'Invoice')

@section('content')

<style>
    /* ============================================================
       Compact single-row invoice items
    ============================================================ */
    #invoice-items {
        width: 100%;
        overflow: visible !important;
        padding: 2px 2px 8px;
        position: relative;
    }

    .invoice-item {
        position: relative;
        display: grid;
        grid-template-columns:
            minmax(210px, 1.7fr)
            minmax(95px, .75fr)
            minmax(175px, 1.35fr)
            minmax(145px, 1.15fr)
            minmax(75px, .60fr)
            minmax(110px, .85fr)
            minmax(70px, .55fr)
            minmax(100px, .78fr)
            minmax(85px, .68fr)
            minmax(105px, .82fr)
            40px;
        gap: 9px;
        align-items: end;
        min-width: 1280px;
        padding: 11px 12px;
        margin-bottom: 10px;
        background: #fff;
        border: 1px solid #e7ebf2;
        border-radius: 12px;
        box-shadow: 0 3px 12px rgba(29, 36, 50, .035);
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .invoice-item:hover {
        border-color: #d9ddf7;
        box-shadow: 0 8px 22px rgba(29, 36, 50, .06);
        transform: translateY(-1px);
    }

    .invoice-item .item-field {
        position: relative;
        min-width: 0;
    }

    .invoice-item .item-label {
        display: block;
        margin: 0 0 5px;
        color: #7a8293;
        font-size: 10px;
        line-height: 1.1;
        font-weight: 700;
        letter-spacing: .35px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .invoice-item input[type='text'],
    .invoice-item input[type='number'],
    .invoice-item select {
        width: 100%;
        height: 38px;
        min-height: 38px;
        padding: 7px 9px;
        margin: 0;
        border: 1px solid #e1e6ee;
        border-radius: 8px;
        background: #fff;
        color: #252936;
        font-size: 12px;
        line-height: 1.2;
        outline: none;
        box-sizing: border-box;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .invoice-item input[type='text']:focus,
    .invoice-item input[type='number']:focus,
    .invoice-item select:focus {
        border-color: #7767ed;
        box-shadow: 0 0 0 3px rgba(108, 92, 231, .08);
    }

    .invoice-item input[readonly] {
        background: #f7f9fc;
        color: #616b7c;
    }

    .invoice-item .product-field {
        position: relative;
    }

    .invoice-item .product-results {
        display: none;
        position: absolute;
        z-index: 99999;
        top: calc(100% + 5px);
        left: 0;
        right: 0;
        max-height: 280px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #e2e7ef;
        border-radius: 10px;
        box-shadow: 0 15px 40px rgba(31, 38, 54, .18);
    }

    .invoice-item .product-option:hover {
        background: #f7f6ff;
    }

    .invoice-item .line-total-box {
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding: 7px 10px;
        border: 1px solid #e1e6ee;
        border-radius: 8px;
        background: #f5f6ff;
        color: #252936;
        font-size: 13px;
        font-weight: 800;
        white-space: nowrap;
        box-sizing: border-box;
    }

    .invoice-item .remove-item {
        width: 38px;
        height: 38px;
        min-height: 38px;
        padding: 0;
        margin: 0;
        border: 1px solid #ffd4d9;
        border-radius: 8px;
        background: #fff3f4;
        color: #e34d59;
        font-size: 21px;
        line-height: 1;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background .15s ease, color .15s ease, border-color .15s ease;
    }

    .invoice-item .remove-item:hover {
        background: #e34d59;
        border-color: #e34d59;
        color: #fff;
    }

    .invoice-items-card {
        overflow: visible !important;
        position: relative;
        z-index: 10;
    }

    .invoice-item {
        overflow: visible !important;
    }

    .invoice-item .product-field {
        position: relative;
        z-index: 100;
    }

    #add-item {
        margin-bottom: 2px;
    }

    @media (max-width: 1199.98px) {
        .invoice-item {
            min-width: 1240px;
        }
    }
</style>


    <h1>
        {{ $invoice ? 'Edit Invoice' : 'Create Invoice' }}
    </h1>

    <div
        id="save-message"
        class="success"
        style="display:none;">
    </div>

    <div
        id="invoice-errors"
        class="error"
        style="display:none;">
    </div>


    <form id="invoice-form">

        @csrf

        <input
            type="hidden"
            name="invoice_id"
            id="invoice_id"
            value="{{ $invoice->id ?? '' }}"
        >

        <input
            type="hidden"
            name="customer_id"
            id="customer_id"
            value="{{ $invoice->customer_id ?? '' }}"
        >


        <div class="card">

            <h3>Invoice</h3>

            <label>Invoice Number</label>

            <input
                type="text"
                id="invoice_number"
                value="{{ $invoice->invoice_number ?? 'Generated on first save' }}"
                readonly
            >


            <label>Invoice Date</label>

            <input
                type="date"
                name="invoice_date"
                id="invoice_date"
                value="{{ $invoice
            ? $invoice->invoice_date->format('Y-m-d')
            : now()->format('Y-m-d')
        }}"
                required
            >

        </div>


        <div class="card">

            <h3>Customer</h3>

            <label>Search Customer</label>

            <input
                type="text"
                id="customer_search"
                value="{{ $invoice->buyer_business_name ?? '' }}"
                autocomplete="off"
                placeholder="Type customer name or NTN..."
            >

            <div
                id="customer-results"
                style="
            display:none;
            border:1px solid #ddd;
            background:#fff;
            max-height:250px;
            overflow:auto;
        ">
            </div>

            <div id="customer-info">

                @if($invoice)

                    <strong>
                        {{ $invoice->buyer_business_name }}
                    </strong>

                    <br>

                    {{ ucfirst(
                        $invoice->buyer_registration_type
                    ) }}

                    <br>

                    NTN/CNIC:
                    {{ $invoice->buyer_ntn_cnic ?: '-' }}

                    <br>

                    {{ $invoice->buyer_province }}

                @endif

            </div>

        </div>


        <div class="card invoice-items-card">
            <h3>Invoice Items</h3>

            <button
                type="button"
                id="add-item"
                class="btn"
            >
                + Add Item
            </button>

            <br><br>


            <div id="invoice-items">

                @if($invoice)

                    @foreach($invoice->items as $item)

                        @include(
                            'invoices.item-row',
                            [
                                'item' => $item,
                                'index' => $loop->index
                            ]
                        )

                    @endforeach

                @endif

            </div>

        </div>


        <div class="card">

            <h3>Totals</h3>

            <p>
                Subtotal:
                <strong id="subtotal">0.00</strong>
            </p>

            <p>
                Sales Tax:
                <strong id="sales-tax">0.00</strong>
            </p>

            <p>
                Grand Total:
                <strong id="grand-total">0.00</strong>
            </p>

        </div>


        <button
            type="button"
            id="save-draft"
            class="btn btn-primary"
        >
            Save Draft
        </button>

        <a
            href="{{ $invoice ? route('invoices.preview', $invoice) : '#'}}"
            id="preview-invoice"
            class="btn"
            style="{{ $invoice ? '' : 'display:none;' }}"
        >
            Preview Invoice
        </a>

    </form>

@endsection

@push('scripts')

    <script>

        $(function () {

            let itemIndex =
                $('.invoice-item').length;

            let saveTimer = null;


            /*
            |--------------------------------------------------------------------------
            | Add item
            |--------------------------------------------------------------------------
            */

            $('#add-item').on('click', function () {
                let html = `
                    <div class="invoice-item">

                        <input type="hidden"
                               class="product-id">

                        <div class="item-field product-field">
                            <label class="item-label">Product / Service</label>
                            <input type="text"
                                   class="product-search"
                                   autocomplete="off"
                                   placeholder="Search product...">
                            <div class="product-results"></div>
                        </div>

                        <div class="item-field">
                            <label class="item-label">HS Code</label>
                            <input type="text"
                                   class="hs-code"
                                   readonly>
                        </div>

                        <div class="item-field">
                            <label class="item-label">Description</label>
                            <input type="text"
                                   class="product-description">
                        </div>

                        <div class="item-field">
                            <input type="hidden" class="transaction-type-id">
                            <input type="hidden" class="sale-type">
                            <label class="item-label">Sale Type</label>
                            <input type="text"
                                   class="sale-type-display"
                                   readonly>
                        </div>

                        <div class="item-field">
                            <input type="hidden" class="uom-id">
                            <label class="item-label">UOM</label>
                            <input type="text"
                                   class="uom"
                                   readonly>
                        </div>

                        <div class="item-field">
                            <label class="item-label">Rate</label>
                            <select class="rate-id">
                                <option value="">Select product first</option>
                            </select>
                            <input type="hidden" class="rate-description">
                            <input type="hidden" class="tax-rate">
                        </div>

                        <div class="item-field">
                            <label class="item-label">Qty</label>
                            <input type="number"
                                   class="quantity"
                                   value="1"
                                   min="0.0001"
                                   step="0.0001">
                        </div>

                        <div class="item-field">
                            <label class="item-label">Unit Price</label>
                            <input type="number"
                                   class="unit-price"
                                   value="0"
                                   min="0"
                                   step="0.0001">
                        </div>

                        <div class="item-field">
                            <label class="item-label">Discount</label>
                            <input type="number"
                                   class="discount"
                                   value="0"
                                   min="0"
                                   step="0.0001">
                        </div>

                        <div class="item-field">
                            <label class="item-label">Line Total</label>
                            <div class="line-total-box">
                                <strong class="line-total">0.00</strong>
                            </div>
                        </div>

                        <div class="item-field">
                            <label class="item-label">&nbsp;</label>
                            <button
                                type="button"
                                class="btn remove-item"
                                title="Remove item"
                                aria-label="Remove item">&times;</button>
                        </div>

                        <input type="hidden" class="extra-tax" value="0">
                        <input type="hidden" class="further-tax" value="0">
                        <input type="hidden" class="fed-payable" value="0">
                        <input type="hidden" class="fixed-value" value="0">
                        <input type="hidden" class="sro-schedule-no">
                        <input type="hidden" class="sro-item-serial-no">

                    </div>
                `;

                $('#invoice-items').append(html);

                reindexItems();
            });


            /*
            |--------------------------------------------------------------------------
            | Reindex dynamic names
            |--------------------------------------------------------------------------
            */

            function reindexItems()
            {
                $('.invoice-item').each(
                    function (index) {

                        let row = $(this);

                        row.find('.product-id')
                            .attr(
                                'name',
                                `items[${index}][product_id]`
                            );

                        row.find('.hs-code')
                            .attr(
                                'name',
                                `items[${index}][hs_code]`
                            );

                        row.find('.product-description')
                            .attr(
                                'name',
                                `items[${index}][product_description]`
                            );

                        row.find('.transaction-type-id')
                            .attr(
                                'name',
                                `items[${index}][transaction_type_id]`
                            );

                        row.find('.sale-type')
                            .attr(
                                'name',
                                `items[${index}][sale_type]`
                            );

                        row.find('.uom-id')
                            .attr(
                                'name',
                                `items[${index}][uom_id]`
                            );

                        row.find('.uom')
                            .attr(
                                'name',
                                `items[${index}][uom]`
                            );

                        row.find('.rate-id')
                            .attr(
                                'name',
                                `items[${index}][rate_id]`
                            );

                        row.find('.rate-description')
                            .attr(
                                'name',
                                `items[${index}][rate_description]`
                            );

                        row.find('.tax-rate')
                            .attr(
                                'name',
                                `items[${index}][tax_rate]`
                            );

                        row.find('.quantity')
                            .attr(
                                'name',
                                `items[${index}][quantity]`
                            );

                        row.find('.unit-price')
                            .attr(
                                'name',
                                `items[${index}][unit_price]`
                            );

                        row.find('.discount')
                            .attr(
                                'name',
                                `items[${index}][discount]`
                            );

                        row.find('.extra-tax')
                            .attr(
                                'name',
                                `items[${index}][extra_tax]`
                            );

                        row.find('.further-tax')
                            .attr(
                                'name',
                                `items[${index}][further_tax]`
                            );

                        row.find('.fed-payable')
                            .attr(
                                'name',
                                `items[${index}][fed_payable]`
                            );

                        row.find('.fixed-value')
                            .attr(
                                'name',
                                `items[${index}][fixed_notified_value_or_retail_price]`
                            );

                        row.find('.sro-schedule-no')
                            .attr(
                                'name',
                                `items[${index}][sro_schedule_no]`
                            );

                        row.find('.sro-item-serial-no')
                            .attr(
                                'name',
                                `items[${index}][sro_item_serial_no]`
                            );

                    }
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Customer search
            |--------------------------------------------------------------------------
            */

            let customerTimer = null;

            $('#customer_search').on(
                'keyup',
                function () {

                    clearTimeout(customerTimer);

                    let q = $(this).val().trim();

                    if (q.length < 2) {
                        $('#customer-results').hide();
                        return;
                    }

                    customerTimer = setTimeout(
                        function () {

                            $.get(
                                "{{ route('customers.search') }}",
                                { q: q },
                                function (customers) {

                                    let html = '';

                                    $.each(
                                        customers,
                                        function (i, c) {

                                            html += `
                                        <div
                                            class="customer-option"
                                            data-customer='${JSON.stringify(c)}'
                                            style="padding:10px;cursor:pointer;border-bottom:1px solid #eee;">

                                            <strong>
                                                ${c.business_name}
                                            </strong>

                                            <br>

                                            ${c.ntn_cnic ?? ''}

                                        </div>
                                    `;
                                        }
                                    );

                                    $('#customer-results')
                                        .html(html)
                                        .show();

                                }
                            );

                        },
                        300
                    );
                }
            );


            $(document).on(
                'click',
                '.customer-option',
                function () {

                    let c =
                        $(this).data('customer');

                    $('#customer_id').val(c.id);

                    $('#customer_search')
                        .val(c.business_name);

                    $('#customer-info').html(`
                <strong>${c.business_name}</strong>
                <br>
                ${c.registration_type}
                <br>
                NTN/CNIC: ${c.ntn_cnic ?? '-'}
                <br>
                ${c.province ?? ''}
            `);

                    $('#customer-results').hide();

                    scheduleAutosave();
                }
            );


            /*
            |--------------------------------------------------------------------------
            | Product AJAX search
            |--------------------------------------------------------------------------
            */

            let productTimer = null;

            $(document).on(
                'keyup',
                '.product-search',
                function () {

                    clearTimeout(productTimer);

                    let input = $(this);
                    let row = input.closest('.invoice-item');

                    let q = input.val().trim();

                    if (q.length < 2) {
                        row.find('.product-results').hide();
                        return;
                    }
                    row.find('.product-results').empty();
                    productTimer = setTimeout(
                        function () {

                            $.get(
                                "{{ route('products.search') }}",
                                { q: q },
                                function (products) {

                                    let html = '';

                                    $.each(
                                        products,
                                        function (i, p) {

                                            /*html += `
<div class="product-option"
    data-product='${JSON.stringify(p)}'
    data-id="${p.id}"
    data-transaction-type-id="${p.transaction_type_id}"
    data-rate-id="${p.rate_id}"
    style="padding:10px;cursor:pointer;border-bottom:1px solid #eee;">

    <strong>${p.name}</strong>
    <br>
    ${p.hs_code ?? ''}
    <br>
</div>`;*/
                                            let option = $('<div>')
                                                .addClass('product-option')
                                                .css({
                                                    padding: '10px',
                                                    cursor: 'pointer',
                                                    borderBottom: '1px solid #eee'
                                                })
                                                .html(
                                                    '<strong>' + $('<div>').text(p.name).html() + '</strong><br>' +
                                                    $('<div>').text(p.hs_code ?? '').html()
                                                )
                                                .data('product', p);

                                            row.find('.product-results').append(option);
                                        }
                                    );

                                    row.find('.product-results').show();
                                }
                            );

                        },
                        300
                    );
                }
            );


            $(document).on('click','.product-option', function () {
                let p = $(this).data('product');
                let transactionTypeId = $(this).data('transaction-type-id');
                let rateId = $(this).data('rate-id');
                console.log(p);
                console.log(p.transaction_type_id);
                console.log(p.rate_id);
                let row = $(this).closest('.invoice-item');

                row.find('.product-id').val(p.id);

                row.find('.product-search').val(p.name);

                row.find('.product-description')
                    .val(
                        p.description || p.name
                    );

                row.find('.hs-code')
                    .val(p.hs_code);

                row.find('.transaction-type-id')
                    .val(p.transaction_type_id);

                row.find('.sale-type')
                    .val(p.sale_type);

                row.find('.sale-type-display')
                    .val(p.sale_type);

                row.find('.uom-id')
                    .val(p.uom_id);

                row.find('.uom')
                    .val(p.uom);

                row.find('.unit-price')
                    .val(p.unit_price);

                row.find('.product-results')
                    .hide();


                loadRates(
                    row,
                    p.transaction_type_id,
                    p.rate_id
                );


                calculateInvoice();

                scheduleAutosave();
            });


            /*
            |--------------------------------------------------------------------------
            | Load current FBR rates
            |--------------------------------------------------------------------------
            */

            function loadRates(
                row,
                transactionTypeId,
                selectedRateId
            ) {

                let date =
                    $('#invoice_date').val();

                let select =
                    row.find('.rate-id');

                select.html(
                    '<option value="">Loading...</option>'
                );


                $.get(
                    "{{ route('fbr.references.rates') }}",
                    {
                        transaction_type_id:
                        transactionTypeId,

                        date:
                        date
                    }
                )
                    .done(function (rates) {

                        let html =
                            '<option value="">Select Rate</option>';

                        $.each(
                            rates,
                            function (i, rate) {

                                let selected =
                                    String(rate.ratE_ID)
                                    === String(selectedRateId)
                                        ? 'selected'
                                        : '';

                                html += `
                        <option
                            value="${rate.ratE_ID}"
                            data-value="${rate.ratE_VALUE}"
                            data-description="${rate.ratE_DESC}"
                            ${selected}
                        >
                            ${rate.ratE_DESC}
                        </option>
                    `;
                            }
                        );

                        select.html(html);

                        select.trigger('change');
                    });
            }


            /*
            |--------------------------------------------------------------------------
            | Rate changed
            |--------------------------------------------------------------------------
            */

            $(document).on(
                'change',
                '.rate-id',
                function () {

                    let row =
                        $(this).closest('.invoice-item');

                    let selected =
                        $(this).find(':selected');

                    row.find('.tax-rate')
                        .val(
                            selected.data('value') || 0
                        );

                    row.find('.rate-description')
                        .val(
                            selected.data('description') || ''
                        );

                    calculateInvoice();

                    scheduleAutosave();
                }
            );


            /*
            |--------------------------------------------------------------------------
            | Remove item
            |--------------------------------------------------------------------------
            */

            $(document).on(
                'click',
                '.remove-item',
                function () {

                    $(this)
                        .closest('.invoice-item')
                        .remove();

                    reindexItems();

                    calculateInvoice();

                    scheduleAutosave();
                }
            );


            /*
            |--------------------------------------------------------------------------
            | Browser-side calculations
            |--------------------------------------------------------------------------
            */

            $(document).on(
                'input change',
                '.quantity, .unit-price, .discount',
                function () {

                    calculateInvoice();

                    scheduleAutosave();
                }
            );


            function calculateInvoice()
            {
                let subtotal = 0;
                let salesTax = 0;
                let grandTotal = 0;


                $('.invoice-item').each(
                    function () {

                        let row = $(this);

                        let qty =
                            parseFloat(
                                row.find('.quantity').val()
                            ) || 0;

                        let price =
                            parseFloat(
                                row.find('.unit-price').val()
                            ) || 0;

                        let discount =
                            parseFloat(
                                row.find('.discount').val()
                            ) || 0;

                        let rate =
                            parseFloat(
                                row.find('.tax-rate').val()
                            ) || 0;


                        let taxable =
                            Math.max(
                                0,
                                qty * price - discount
                            );

                        let tax =
                            taxable * rate / 100;

                        let total =
                            taxable + tax;


                        row.find('.line-total')
                            .text(
                                total.toFixed(2)
                            );

                        subtotal += taxable;
                        salesTax += tax;
                        grandTotal += total;

                    }
                );


                $('#subtotal')
                    .text(subtotal.toFixed(2));

                $('#sales-tax')
                    .text(salesTax.toFixed(2));

                $('#grand-total')
                    .text(grandTotal.toFixed(2));
            }


            /*
            |--------------------------------------------------------------------------
            | Manual Save
            |--------------------------------------------------------------------------
            */

            $('#save-draft').on(
                'click',
                function () {

                    saveDraft(true);
                }
            );


            /*
            |--------------------------------------------------------------------------
            | Autosave
            |--------------------------------------------------------------------------
            */

            function scheduleAutosave()
            {
                clearTimeout(saveTimer);

                saveTimer = setTimeout(
                    function () {

                        if (canAutosave()) {
                            saveDraft(false);
                        }

                    },
                    1500
                );
            }


            function canAutosave()
            {
                return (
                    $('#customer_id').val()
                    &&
                    $('.invoice-item').length > 0
                    &&
                    $('.invoice-item')
                        .first()
                        .find('.product-id')
                        .val()
                );
            }


            function saveDraft(showMessage)
            {
                reindexItems();

                let button =
                    $('#save-draft');

                if (showMessage) {

                    button
                        .prop('disabled', true)
                        .text('Saving...');

                }


                $.ajax({

                    url:
                        "{{ route('invoices.save') }}",

                    type:
                        "POST",

                    data:
                        $('#invoice-form').serialize(),

                    success:
                        function (response) {

                            $('#invoice_id')
                                .val(
                                    response.invoice_id
                                );

                            $('#invoice_number')
                                .val(
                                    response.invoice_number
                                );

                            if (showMessage) {

                                $('#save-message')
                                    .html(response.message)
                                    .show();
                            }

                            /*
                            After first save change URL
                            from /create to /{id}/edit.
                            */

                            if (
                                window.location.pathname
                                    .endsWith('/create')
                            ) {

                                window.history.replaceState(
                                    {},
                                    '',
                                    response.edit_url
                                );
                            }
                            $('#preview-invoice')
                                .attr(
                                    'href',
                                    '/invoices/' +
                                    response.invoice_id +
                                    '/preview'
                                )
                                .show();

                        },

                    error:
                        function (xhr) {

                            if (!showMessage) {
                                return;
                            }

                            let message =
                                xhr.responseJSON?.message
                                ?? 'Unable to save invoice.';

                            let html =
                                '<div>' +
                                message +
                                '</div>';


                            if (
                                xhr.responseJSON?.errors
                            ) {

                                $.each(
                                    xhr.responseJSON.errors,
                                    function (
                                        field,
                                        messages
                                    ) {

                                        $.each(
                                            messages,
                                            function (
                                                i,
                                                error
                                            ) {

                                                html +=
                                                    '<div>' +
                                                    error +
                                                    '</div>';

                                            }
                                        );
                                    }
                                );
                            }


                            $('#invoice-errors')
                                .html(html)
                                .show();

                        },

                    complete:
                        function () {

                            button
                                .prop('disabled', false)
                                .text('Save Draft');
                        }

                });
            }


            /*
            |--------------------------------------------------------------------------
            | Initial setup
            |--------------------------------------------------------------------------
            */

            reindexItems();

            calculateInvoice();


            @if(!$invoice)

            $('#add-item').trigger('click');

            @endif

        });

    </script>

@endpush
