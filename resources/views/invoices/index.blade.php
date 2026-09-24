@extends('layouts.app')

@section('title', 'Invoices')

@section('content')

    <h1>Invoices</h1>

    @if($errors->has('invoice_limit'))
        <div class="alert alert-danger">
            {{ $errors->first('invoice_limit') }}
        </div>
    @endif

    <div class="card" style="margin-bottom:15px;">
        <strong>Monthly Invoice Usage:</strong>
        {{ $monthlyInvoiceCount }} / {{ $monthlyInvoiceLimit }}

        @if($monthlyInvoiceLimitReached)
            <div style="margin-top:8px;color:#b42318;font-weight:700;">
                Your invoices have reached the maximum monthly limit of
                {{ $monthlyInvoiceLimit }}.
                You cannot create more invoices this month.
            </div>
        @elseif(
            $monthlyInvoiceCount
            >= ($monthlyInvoiceLimit * 0.9)
        )
            <div style="margin-top:8px;color:#b54708;font-weight:700;">
                You are close to your monthly invoice limit.
                {{ $monthlyInvoiceRemaining }}
                invoice(s) remaining.
            </div>
        @endif
    </div>

    <p>
        @if(!$monthlyInvoiceLimitReached)
            <a
                href="{{ route('invoices.create') }}"
                class="btn btn-primary"
            >
                + Create Invoice
            </a>
        @else
            <button
                type="button"
                class="btn btn-primary"
                disabled
            >
                + Create Invoice
            </button>
        @endif
    </p>

    <div class="card">

        <table>

            <thead>
            <tr>
                <th>Invoice</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Status</th>
                <th>FBR Sandbox</th>
                <th>FBR No.</th>
                <th>Action</th>
            </tr>
            </thead>

            <tbody>

            @forelse($invoices as $invoice)

                <tr>

                    <td>
                        {{ $invoice->invoice_number }}
                    </td>

                    <td>
                        {{ $invoice->invoice_date->format('d-M-Y') }}
                    </td>

                    <td>
                        {{ $invoice->buyer_business_name }}
                    </td>

                    <td>
                        {{ number_format(
                            (float)$invoice->grand_total,
                            2
                        ) }}
                    </td>

                    <td>
                        {{ strtoupper($invoice->status) }}
                    </td>

                    <td>

                        @if($invoice->latestFbrSubmission)

                            {{ $invoice->latestFbrSubmission->fbr_status ?: '-' }}

                        @else

                            Not Submitted

                        @endif

                    </td>

                    <td>

                        {{ $invoice->latestFbrSubmission?->fbr_invoice_number ?: '-' }}

                    </td>

                    <td>

                        @if(
                            $invoice->status === 'draft'
                            && !$invoice->isFbrLocked()
                        )
                            <a
                                href="{{ route('invoices.edit', $invoice) }}"
                                class="btn"
                            >
                                Edit
                            </a>
                        @else
                            <span style="font-size:12px;font-weight:700;color:#667085;">
                                Locked
                            </span>
                        @endif
                        <a
                            href="{{ route('invoices.preview', $invoice) }}"
                            class="btn"
                        >
                            Preview
                        </a>
                        <a
                            href="{{ route('invoices.pdf', $invoice) }}"
                            class="btn"
                        >
                            PDF
                        </a>

                    </td>
                </tr>

            @empty

                <tr>
                    <td colspan="8">
                        No invoices found.
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
                <strong>{{ $invoices->count() ? $invoices->firstItem() : 0 }}</strong>
                to
                <strong>{{ $invoices->count() ? $invoices->lastItem() : 0 }}</strong>
                of
                <strong>{{ $invoices->total() }}</strong>
                invoices
            </div>

            <div
                style="
                    display:flex;
                    align-items:center;
                    gap:8px;
                    flex-wrap:wrap;
                "
            >
                @if($invoices->onFirstPage())
                    <span
                        class="btn"
                        style="opacity:.45;cursor:not-allowed;"
                    >
                        Previous
                    </span>
                @else
                    <a
                        href="{{ $invoices->previousPageUrl() }}"
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
                    Page {{ $invoices->currentPage() }}
                    of {{ max(1, $invoices->lastPage()) }}
                </span>

                @if($invoices->hasMorePages())
                    <a
                        href="{{ $invoices->nextPageUrl() }}"
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
