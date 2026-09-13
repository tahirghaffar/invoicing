<div class="invoice-item">

    <input type="hidden"
           class="product-id"
           name="items[{{ $index }}][product_id]"
           value="{{ $item->product_id ?? '' }}">

    <div class="item-field product-field">
        <label class="item-label">Product / Service</label>
        <input type="text"
               class="product-search"
               value="{{ $item->product_description ?? '' }}"
               autocomplete="off"
               placeholder="Search product...">
        <div class="product-results"></div>
    </div>

    <div class="item-field">
        <label class="item-label">HS Code</label>
        <input type="text"
               class="hs-code"
               name="items[{{ $index }}][hs_code]"
               value="{{ $item->hs_code ?? '' }}"
               readonly>
    </div>

    <div class="item-field">
        <label class="item-label">Description</label>
        <input type="text"
               class="product-description"
               name="items[{{ $index }}][product_description]"
               value="{{ $item->product_description ?? '' }}"
               required>
    </div>

    <div class="item-field">
        <input type="hidden"
               class="transaction-type-id"
               name="items[{{ $index }}][transaction_type_id]"
               value="{{ $item->transaction_type_id ?? '' }}">
        <input type="hidden"
               class="sale-type"
               name="items[{{ $index }}][sale_type]"
               value="{{ $item->sale_type ?? '' }}">
        <label class="item-label">Sale Type</label>
        <input type="text"
               class="sale-type-display"
               value="{{ $item->sale_type ?? '' }}"
               readonly>
    </div>

    <div class="item-field">
        <input type="hidden"
               class="uom-id"
               name="items[{{ $index }}][uom_id]"
               value="{{ $item->uom_id ?? '' }}">
        <label class="item-label">UOM</label>
        <input type="text"
               class="uom"
               name="items[{{ $index }}][uom]"
               value="{{ $item->uom ?? '' }}"
               readonly>
    </div>

    <div class="item-field">
        <label class="item-label">Rate</label>
        <select class="rate-id"
                name="items[{{ $index }}][rate_id]"
                required>
            @if(isset($item))
                <option value="{{ $item->rate_id }}" selected>
                    {{ $item->rate_description }}
                </option>
            @else
                <option value="">Select product first</option>
            @endif
        </select>
        <input type="hidden"
               class="rate-description"
               name="items[{{ $index }}][rate_description]"
               value="{{ $item->rate_description ?? '' }}">
        <input type="hidden"
               class="tax-rate"
               name="items[{{ $index }}][tax_rate]"
               value="{{ $item->tax_rate ?? 0 }}">
    </div>

    <div class="item-field">
        <label class="item-label">Qty</label>
        <input type="number"
               class="quantity"
               name="items[{{ $index }}][quantity]"
               value="{{ $item->quantity ?? 1 }}"
               min="0.0001"
               step="0.0001">
    </div>

    <div class="item-field">
        <label class="item-label">Unit Price</label>
        <input type="number"
               class="unit-price"
               name="items[{{ $index }}][unit_price]"
               value="{{ $item->unit_price ?? 0 }}"
               min="0"
               step="0.0001">
    </div>

    <div class="item-field">
        <label class="item-label">Discount</label>
        <input type="number"
               class="discount"
               name="items[{{ $index }}][discount]"
               value="{{ $item->discount ?? 0 }}"
               min="0"
               step="0.0001">
    </div>

    <div class="item-field">
        <label class="item-label">Line Total</label>
        <div class="line-total-box">
            <strong class="line-total">
                {{ isset($item) ? number_format((float)($item->line_total ?? 0), 2) : '0.00' }}
            </strong>
        </div>
    </div>

    <div class="item-field">
        <label class="item-label">&nbsp;</label>
        <button type="button"
                class="btn remove-item"
                title="Remove item"
                aria-label="Remove item">&times;</button>
    </div>

    <input type="hidden"
           class="extra-tax"
           name="items[{{ $index }}][extra_tax]"
           value="{{ $item->extra_tax ?? 0 }}">

    <input type="hidden"
           class="further-tax"
           name="items[{{ $index }}][further_tax]"
           value="{{ $item->further_tax ?? 0 }}">

    <input type="hidden"
           class="fed-payable"
           name="items[{{ $index }}][fed_payable]"
           value="{{ $item->fed_payable ?? 0 }}">

    <input type="hidden"
           class="fixed-value"
           name="items[{{ $index }}][fixed_notified_value_or_retail_price]"
           value="{{ $item->fixed_notified_value_or_retail_price ?? 0 }}">

    <input type="hidden"
           class="sro-schedule-no"
           name="items[{{ $index }}][sro_schedule_no]"
           value="{{ $item->sro_schedule_no ?? '' }}">

    <input type="hidden"
           class="sro-item-serial-no"
           name="items[{{ $index }}][sro_item_serial_no]"
           value="{{ $item->sro_item_serial_no ?? '' }}">

</div>
