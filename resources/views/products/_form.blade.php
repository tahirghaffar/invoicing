<div class="card">

    <form id="product-form">

        @csrf

        @if(isset($product))
            @method('PUT')
        @endif


        <h3>Product / Service Information</h3>


        <label>Type *</label>

        <select
            name="type"
            required
        >

            <option
                value="product"
                {{ old('type', $product->type ?? 'product') === 'product' ? 'selected' : '' }}
            >
                Product
            </option>

            <option
                value="service"
                {{ old('type', $product->type ?? '') === 'service' ? 'selected' : '' }}
            >
                Service
            </option>

        </select>


        <label>SKU / Internal Code</label>

        <input
            type="text"
            name="sku"
            value="{{ old('sku', $product->sku ?? '') }}"
        >


        <label>Name *</label>

        <input
            type="text"
            name="name"
            value="{{ old('name', $product->name ?? '') }}"
            required
        >


        <label>Description</label>

        <textarea
            name="description"
            rows="3"
        >{{ old('description', $product->description ?? '') }}</textarea>


        <hr>


        <h3>FBR Information</h3>


        <label>HS Code</label>

        <input
            type="text"
            id="hs_code"
            name="hs_code"
            autocomplete="off"
            value="{{ old(
                'hs_code',
                $product->hs_code ?? ''
            ) }}"
        >
        <div
            id="hs-results"
            style="
        display:none;
        border:1px solid #ddd;
        background:white;
        max-height:250px;
        overflow:auto;
        margin-top:-15px;
        margin-bottom:15px;
    ">
        </div>


        <label>UOM</label>

        <select
            name="uom_id"
            id="uom_id"
        >

            <option value="">
                Select UOM
            </option>

            @foreach($uoms as $uom)

                <option
                    value="{{ $uom->fbr_id }}"

                    {{ (string)old(
                        'uom_id',
                        $product->uom_id ?? ''
                    ) === (string)$uom->fbr_id
                        ? 'selected'
                        : ''
                    }}
                >

                    {{ $uom->description }}

                </option>

            @endforeach

        </select>


        <label>Sale Type *</label>

        <select
            name="transaction_type_id"
            id="transaction_type_id"
            required
        >

            <option value="">
                Select Sale Type
            </option>

            @foreach($transactionTypes as $type)

                <option
                    value="{{ $type->fbr_id }}"

                    {{ (string)old(
                        'transaction_type_id',
                        $product->transaction_type_id ?? ''
                    ) === (string)$type->fbr_id
                        ? 'selected'
                        : ''
                    }}
                >

                    {{ $type->description }}

                </option>

            @endforeach

        </select>


        <label>Default Tax Rate *</label>

        <select
            name="rate_id"
            id="rate_id"
        >

            @if(isset($product) && $product->rate_id)

                <option
                    value="{{ $product->rate_id }}"
                    selected
                >
                    {{ $product->tax_rate_description }}
                </option>

            @else

                <option value="">
                    Select Sale Type First
                </option>

            @endif

        </select>


        <hr>


        <h3>Pricing</h3>


        <label>Default Unit Price *</label>

        <input
            type="number"
            step="0.0001"
            min="0"
            name="unit_price"
            value="{{ old('unit_price', $product->unit_price ?? 0) }}"
            required
        >


        <label>
            Fixed / Notified Value or Retail Price
        </label>

        <input
            type="number"
            step="0.0001"
            min="0"
            name="fixed_notified_value_or_retail_price"
            value="{{ old(
            'fixed_notified_value_or_retail_price',
            $product->fixed_notified_value_or_retail_price ?? 0
        ) }}"
        >


        <hr>


        <h3>SRO Information</h3>


        <label>SRO Schedule No</label>

        <input
            type="text"
            name="sro_schedule_no"
            value="{{ old(
            'sro_schedule_no',
            $product->sro_schedule_no ?? ''
        ) }}"
        >


        <label>SRO Item Serial No</label>

        <input
            type="text"
            name="sro_item_serial_no"
            value="{{ old(
            'sro_item_serial_no',
            $product->sro_item_serial_no ?? ''
        ) }}"
        >


        <label>Status</label>

        <select name="status">

            <option
                value="active"
                {{ old('status', $product->status ?? 'active') === 'active' ? 'selected' : '' }}
            >
                Active
            </option>

            <option
                value="inactive"
                {{ old('status', $product->status ?? '') === 'inactive' ? 'selected' : '' }}
            >
                Inactive
            </option>

        </select>


        <button
            type="submit"
            id="save-product"
            class="btn btn-primary"
        >

            {{ isset($product)
                ? 'Update Product / Service'
                : 'Save Product / Service'
            }}

        </button>

    </form>

</div>
