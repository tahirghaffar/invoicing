<div class="invoice-item"
     style="
        border:1px solid #ddd;
        padding:15px;
        margin-bottom:15px;
     ">

    <input
        type="hidden"
        class="product-id"
        name="items[{{ $index }}][product_id]"
        value="{{ $item->product_id ?? '' }}"
    >


    <label>Product / Service</label>

    <input
        type="text"
        class="product-search"
        value="{{ $item->product_description ?? '' }}"
        autocomplete="off"
        placeholder="Search product..."
    >

    <div class="product-results"></div>


    <label>HS Code</label>

    <input
        type="text"
        class="hs-code"
        name="items[{{ $index }}][hs_code]"
        value="{{ $item->hs_code ?? '' }}"
        readonly
    >


    <label>Description</label>

    <input
        type="text"
        class="product-description"
        name="items[{{ $index }}][product_description]"
        value="{{ $item->product_description ?? '' }}"
        required
    >


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


    <label>Sale Type</label>

    <input
        type="text"
        class="sale-type-display"
        value="{{ $item->sale_type ?? '' }}"
        readonly
    >


    <input
        type="hidden"
        class="uom-id"
        name="items[{{ $index }}][uom_id]"
        value="{{ $item->uom_id ?? '' }}"
    >


    <label>UOM</label>

    <input
        type="text"
        class="uom"
        name="items[{{ $index }}][uom]"
        value="{{ $item->uom ?? '' }}"
        readonly
    >


    <label>Rate</label>

    <select
        class="rate-id"
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


    <label>Quantity</label>

    <input
        type="number"
        class="quantity"
        name="items[{{ $index }}][quantity]"
        value="{{ $item->quantity ?? 1 }}"
        min="0.0001"
        step="0.0001"
    >


    <label>Unit Price</label>

    <input
        type="number"
        class="unit-price"
        name="items[{{ $index }}][unit_price]"
        value="{{ $item->unit_price ?? 0 }}"
        min="0"
        step="0.0001"
    >


    <label>Discount</label>

    <input
        type="number"
        class="discount"
        name="items[{{ $index }}][discount]"
        value="{{ $item->discount ?? 0 }}"
        min="0"
        step="0.0001"
    >


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


    <p>
        Line Total:
        <strong class="line-total">
            0.00
        </strong>
    </p>


    <button
        type="button"
        class="btn remove-item"
    >
        Remove
    </button>

</div>
