@extends('layouts.app')

@section('title', 'Invoices')

@section('content')

    <h1>Invoices</h1>

    <p>
        <a
            href="{{ route('invoices.create') }}"
            class="btn btn-primary"
        >
            + Create Invoice
        </a>
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

                        @if($invoice->status === 'draft')
                            <a
                                href="{{ route('invoices.edit', $invoice) }}"
                                class="btn"
                            >
                                Edit
                            </a>
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
                    <td colspan="6">
                        No invoices found.
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

    {{ $invoices->links() }}

@endsection
