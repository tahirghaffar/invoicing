@once
<style>
    .invoice-item {
        position: relative;
        display: grid;
        grid-template-columns:
            minmax(230px, 1.8fr)
            minmax(105px, .8fr)
            minmax(200px, 1.5fr)
            minmax(145px, 1.1fr)
            minmax(85px, .65fr)
            minmax(120px, .9fr)
            minmax(90px, .7fr)
            minmax(110px, .85fr)
            minmax(90px, .7fr)
            minmax(115px, .9fr)
            42px;
        gap: 10px;
        align-items: end;
        min-width: 1380px;
        padding: 12px 14px;
        margin-bottom: 10px;
        background: #fff;
        border: 1px solid #e9edf3;
        border-radius: 14px;
        box-shadow: 0 3px 12px rgba(29, 36, 50, .035);
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .invoice-item:hover {
        border-color: #d9ddf7;
        box-shadow: 0 8px 22px rgba(29, 36, 50, .065);
        transform: translateY(-1px);
    }

    .invoice-item-row-scroll {
        width: 100%;
        overflow-x: auto;
        padding-bottom: 2px;
    }

    .invoice-item .item-field {
        min-width: 0;
        position: relative;
    }

    .invoice-item .item-label {
        display: block;
        margin: 0 0 5px;
        color: #7a8293;
        font-size: 10px;
        line-height: 1.15;
        font-weight: 700;
        letter-spacing: .35px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .invoice-item .form-control,
    .invoice-item .form-select,
    .invoice-item input[type="text"],
    .invoice-item input[type="number"],
    .invoice-item select {
        width: 100%;
        height: 38px;
        border: 1px solid #e4e8ef;
        border-radius: 9px;
        background: #fff;
        padding: 7px 9px;
        color: #252936;
        font-size: 12px;
        line-height: 1.2;
        outline: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .invoice-item input:focus,
    .invoice-item select:focus {
        border-color: #8f84f7;
        box-shadow: 0 0 0 3px rgba(108, 92, 231, .08);
    }

    .invoice-item input[readonly] {
        background: #f8f9fc;
        color: #626b7b;
    }

    .invoice-item .product-field {
        position: relative;
    }

    .invoice-item .product-results {
        display: none;
        position: absolute;
        z-index: 1060;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        max-height: 260px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #e5e8ef;
        border-radius: 10px;
        box-shadow: 0 14px 34px rgba(31, 38, 54, .14);
    }

    .invoice-item .line-total-box {
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding: 7px 10px;
        border: 1px solid #e4e8ef;
        border-radius: 9px;
        background: #f7f8ff;
        color: #252936;
        font-size: 13px;
        font-weight: 800;
        white-space: nowrap;
    }

    .invoice-item .remove-item {
        width: 38px;
        height: 38px;
        padding: 0;
        border: 1px solid #ffd8dc;
        border-radius: 9px;
        background: #fff4f5;
        color: #e34d59;
        font-size: 21px;
        line-height: 1;
        font-weight: 500;
        transition: background .15s ease, color .15s ease, border-color .15s ease;
    }

    .invoice-item .remove-item:hover {
        background: #e34d59;
        border-color: #e34d59;
        color: #fff;
    }

    @media (max-width: 991.98px) {
        .invoice-item {
            min-width: 1260px;
        }
    }
</style>
@endonce

<div class="invoice-item-row-scroll">
    <div class="invoice-item">

        <input
            type="hidden"
            class="product-id"
            name="items[{{ $index }}][product_id]"
            value="{{ $item->product_id ?? '' }}"
        >

        <div class="item-field product-field">
            <label class="item-label">Product / Service</label>

            <input
                type="text"
                class="product-search form-control"
                value="{{ $item->product_description ?? '' }}"
                autocomplete="off"
                placeholder="Search product..."
            >

            <div class="product-results"></div>
        </div>

        <div class="item-field">
            <label class="item-label">HS Code</label>

            <input
                type="text"
                class="hs-code form-control"
                name="items[{{ $index }}][hs_code]"
                value="{{ $item->hs_code ?? '' }}"
                readonly
            >
        </div>

        <div class="item-field">
            <label class="item-label">Description</label>

            <input
                type="text"
                class="product-description form-control"
                name="items[{{ $index }}][product_description]"
                value="{{ $item->product_description ?? '' }}"
                required
            >
        </div>

        <div class="item-field">

            <input
                type="hidden"
                class="transaction-type-id"
                name="items[{{ $index }}][transaction_type_id]"
                value="{{ $item->transaction_type_id ?? '' }}"
            >

            <input
                type="hidden"
                class="sale-type"
                name="items[{{ $index }}][sale_type]"
                value="{{ $item->sale_type ?? '' }}"
            >

            <label class="item-label">Sale Type</label>

            <input
                type="text"
                class="sale-type-display form-control"
                value="{{ $item->sale_type ?? '' }}"
                readonly
            >
        </div>

        <div class="item-field">

            <input
                type="hidden"
                class="uom-id"
                name="items[{{ $index }}][uom_id]"
                value="{{ $item->uom_id ?? '' }}"
            >

            <label class="item-label">UOM</label>

            <input
                type="text"
                class="uom form-control"
                name="items[{{ $index }}][uom]"
                value="{{ $item->uom ?? '' }}"
                readonly
            >
        </div>

        <div class="item-field">
            <label class="item-label">Rate</label>

            <select
                class="rate-id form-select"
                name="items[{{ $index }}][rate_id]"
                required
            >
                @if(isset($item))
                    <option
                        value="{{ $item->rate_id }}"
                        selected
                    >
                        {{ $item->rate_description }}
                    </option>
                @else
                    <option value="">
                        Select rate
                    </option>
                @endif
            </select>

            <input
                type="hidden"
                class="rate-description"
                name="items[{{ $index }}][rate_description]"
                value="{{ $item->rate_description ?? '' }}"
            >

            <input
                type="hidden"
                class="tax-rate"
                name="items[{{ $index }}][tax_rate]"
                value="{{ $item->tax_rate ?? 0 }}"
            >
        </div>

        <div class="item-field">
            <label class="item-label">Qty</label>

            <input
                type="number"
                class="quantity form-control"
                name="items[{{ $index }}][quantity]"
                value="{{ $item->quantity ?? 1 }}"
                min="0.0001"
                step="0.0001"
            >
        </div>

        <div class="item-field">
            <label class="item-label">Unit Price</label>

            <input
                type="number"
                class="unit-price form-control"
                name="items[{{ $index }}][unit_price]"
                value="{{ $item->unit_price ?? 0 }}"
                min="0"
                step="0.0001"
            >
        </div>

        <div class="item-field">
            <label class="item-label">Discount</label>

            <input
                type="number"
                class="discount form-control"
                name="items[{{ $index }}][discount]"
                value="{{ $item->discount ?? 0 }}"
                min="0"
                step="0.0001"
            >
        </div>

        <div class="item-field">
            <label class="item-label">Line Total</label>

            <div class="line-total-box">
                <span class="line-total">
                    {{ isset($item) ? number_format((float)($item->line_total ?? 0), 2) : '0.00' }}
                </span>
            </div>
        </div>

        <div class="item-field">
            <label class="item-label">&nbsp;</label>

            <button
                type="button"
                class="btn remove-item"
                title="Remove item"
                aria-label="Remove item"
            >
                &times;
            </button>
        </div>

        <input
            type="hidden"
            name="items[{{ $index }}][extra_tax]"
            value="{{ $item->extra_tax ?? 0 }}"
        >

        <input
            type="hidden"
            name="items[{{ $index }}][further_tax]"
            value="{{ $item->further_tax ?? 0 }}"
        >

        <input
            type="hidden"
            name="items[{{ $index }}][fed_payable]"
            value="{{ $item->fed_payable ?? 0 }}"
        >

        <input
            type="hidden"
            name="items[{{ $index }}][fixed_notified_value_or_retail_price]"
            value="{{ $item->fixed_notified_value_or_retail_price ?? 0 }}"
        >

        <input
            type="hidden"
            name="items[{{ $index }}][sro_schedule_no]"
            value="{{ $item->sro_schedule_no ?? '' }}"
        >

        <input
            type="hidden"
            name="items[{{ $index }}][sro_item_serial_no]"
            value="{{ $item->sro_item_serial_no ?? '' }}"
        >

    </div>
</div>
