@extends('layouts.app')

@section('title', 'Products / Services')

@section('content')

    <h1>Products / Services</h1>


    <p>

        <a
            href="{{ route('products.create') }}"
            class="btn btn-primary"
        >
            + Add Product / Service
        </a>

    </p>


    <div class="card">

        <form
            method="GET"
            action="{{ route('products.index') }}"
        >

            <label>Search</label>

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Name, SKU or HS Code..."
            >

            <button
                type="submit"
                class="btn"
            >
                Search
            </button>

        </form>

    </div>


    <div
        id="product-message"
        class="success"
        style="display:none;">
    </div>


    <div class="card">

        <table>

            <thead>

            <tr>

                <th>Name</th>
                <th>Type</th>
                <th>SKU</th>
                <th>HS Code</th>
                <th>UOM</th>
                <th>Tax</th>
                <th>Price</th>
                <th>Status</th>
                <th>Action</th>

            </tr>

            </thead>


            <tbody>

            @forelse($products as $product)

                <tr id="product-row-{{ $product->id }}">

                    <td>
                        <strong>
                            {{ $product->name }}
                        </strong>
                    </td>

                    <td>
                        {{ ucfirst($product->type) }}
                    </td>

                    <td>
                        {{ $product->sku ?: '-' }}
                    </td>

                    <td>
                        {{ $product->hs_code ?: '-' }}
                    </td>

                    <td>
                        {{ $product->uom ?: '-' }}
                    </td>

                    <td>

                        @if($product->tax_rate !== null)

                            {{ rtrim(
                                rtrim($product->tax_rate, '0'),
                                '.'
                            ) }}%

                        @else

                            -

                        @endif

                    </td>

                    <td>
                        {{ number_format(
                            (float) $product->unit_price,
                            2
                        ) }}
                    </td>

                    <td>
                        {{ ucfirst($product->status) }}
                    </td>

                    <td>

                        <a
                            href="{{ route('products.edit', $product) }}"
                            class="btn"
                        >
                            Edit
                        </a>


                        <button
                            type="button"
                            class="btn delete-product"
                            data-id="{{ $product->id }}"
                            data-url="{{ route('products.destroy', $product) }}"
                        >
                            Delete
                        </button>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="9">
                        No products or services found.
                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>



    <div class="card" style="margin-top:15px;">
        <div
            style="
                display:flex;
                justify-content:space-between;
                align-items:center;
                gap:12px;
                flex-wrap:wrap;
            "
        >
            <div style="color:#667085;font-size:13px;">
                Showing
                <strong>{{ $products->count() ? $products->firstItem() : 0 }}</strong>
                to
                <strong>{{ $products->count() ? $products->lastItem() : 0 }}</strong>
                of
                <strong>{{ $products->total() }}</strong>
                products / services
            </div>

            <div
                style="
                    display:flex;
                    align-items:center;
                    gap:8px;
                    flex-wrap:wrap;
                "
            >
                @if($products->onFirstPage())
                    <span
                        class="btn"
                        style="opacity:.45;cursor:not-allowed;"
                    >
                        Previous
                    </span>
                @else
                    <a
                        href="{{ $products->previousPageUrl() }}"
                        class="btn"
                    >
                        Previous
                    </a>
                @endif

                <span
                    style="
                        padding:7px 11px;
                        border:1px solid #e4e7ec;
                        border-radius:7px;
                        background:#f9fafb;
                        color:#475467;
                        font-size:13px;
                        font-weight:600;
                    "
                >
                    Page {{ $products->currentPage() }}
                    of {{ max(1, $products->lastPage()) }}
                </span>

                @if($products->hasMorePages())
                    <a
                        href="{{ $products->nextPageUrl() }}"
                        class="btn"
                    >
                        Next
                    </a>
                @else
                    <span
                        class="btn"
                        style="opacity:.45;cursor:not-allowed;"
                    >
                        Next
                    </span>
                @endif
            </div>
        </div>
    </div>


@endsection


@push('scripts')

    <script>

        $(function () {

            $('.delete-product').on(
                'click',
                function () {

                    if (
                        !confirm(
                            'Are you sure you want to delete this product / service?'
                        )
                    ) {
                        return;
                    }

                    let button =
                        $(this);

                    let id =
                        button.data('id');

                    let url =
                        button.data('url');


                    $.ajax({

                        url: url,

                        type: "POST",

                        data: {

                            _token:
                                "{{ csrf_token() }}",

                            _method:
                                "DELETE"

                        },

                        success: function (response) {

                            $('#product-row-' + id)
                                .fadeOut(
                                    300,
                                    function () {
                                        $(this).remove();
                                    }
                                );

                            $('#product-message')
                                .html(response.message)
                                .show();

                        },

                        error: function () {

                            alert(
                                'Unable to delete product / service.'
                            );

                        }

                    });

                }
            );

        });

    </script>

@endpush
