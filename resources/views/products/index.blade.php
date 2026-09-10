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


    {{ $products->links() }}

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
