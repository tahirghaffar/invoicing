@extends('layouts.app')

@section('title', 'Customers')

@section('content')

    <h1>Customers</h1>


    <div style="margin-bottom:20px;">

        <a
            href="{{ route('customers.create') }}"
            class="btn btn-primary">

            + Add Customer

        </a>

    </div>


    <div class="card">

        <form
            method="GET"
            action="{{ route('customers.index') }}"
        >

            <label>Search Customer</label>

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Business name, NTN, STRN or phone..."
            >

            <button
                type="submit"
                class="btn">

                Search

            </button>

        </form>

    </div>


    <div
        id="customer-message"
        class="success"
        style="display:none;">
    </div>


    <div class="card">

        <table>

            <thead>

            <tr>

                <th>Name</th>

                <th>Type</th>

                <th>NTN / CNIC</th>

                <th>Province</th>

                <th>Phone</th>

                <th>Status</th>

                <th>Action</th>

            </tr>

            </thead>


            <tbody>

            @forelse($customers as $customer)

                <tr id="customer-row-{{ $customer->id }}">

                    <td>

                        <strong>
                            {{ $customer->business_name }}
                        </strong>

                    </td>


                    <td>
                        {{ ucfirst($customer->registration_type) }}
                    </td>


                    <td>
                        {{ $customer->ntn_cnic ?: '-' }}
                    </td>


                    <td>
                        {{ $customer->province ?: '-' }}
                    </td>


                    <td>
                        {{ $customer->phone ?: '-' }}
                    </td>


                    <td>
                        {{ ucfirst($customer->status) }}
                    </td>


                    <td>

                        <a
                            href="{{ route('customers.edit', $customer) }}"
                            class="btn">

                            Edit

                        </a>


                        <button
                            type="button"
                            class="btn delete-customer"
                            data-id="{{ $customer->id }}"
                            data-url="{{ route('customers.destroy', $customer) }}"
                        >

                            Delete

                        </button>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="7">

                        No customers found.

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
                <strong>{{ $customers->count() ? $customers->firstItem() : 0 }}</strong>
                to
                <strong>{{ $customers->count() ? $customers->lastItem() : 0 }}</strong>
                of
                <strong>{{ $customers->total() }}</strong>
                customers
            </div>

            <div
                style="
                    display:flex;
                    align-items:center;
                    gap:8px;
                    flex-wrap:wrap;
                "
            >
                @if($customers->onFirstPage())
                    <span
                        class="btn"
                        style="opacity:.45;cursor:not-allowed;"
                    >
                        Previous
                    </span>
                @else
                    <a
                        href="{{ $customers->previousPageUrl() }}"
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
                    Page {{ $customers->currentPage() }}
                    of {{ max(1, $customers->lastPage()) }}
                </span>

                @if($customers->hasMorePages())
                    <a
                        href="{{ $customers->nextPageUrl() }}"
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

            $('.delete-customer').on(
                'click',
                function () {

                    if (
                        !confirm(
                            'Are you sure you want to delete this customer?'
                        )
                    ) {
                        return;
                    }

                    let button = $(this);

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

                            $('#customer-row-' + id)
                                .fadeOut(
                                    300,
                                    function () {

                                        $(this).remove();

                                    }
                                );

                            $('#customer-message')
                                .html(response.message)
                                .show();

                        },

                        error: function () {

                            alert(
                                'Unable to delete customer.'
                            );

                        }

                    });

                }
            );

        });

    </script>

@endpush
