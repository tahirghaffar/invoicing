@extends('layouts.app')

@section('title', 'FBR Submission History')

@section('content')

    <h1>FBR Submission History</h1>

    <p>
        Invoice:
        <strong>{{ $invoice->invoice_number }}</strong>
    </p>

    <p>
        <a
            href="{{ route('invoices.preview', $invoice) }}"
            class="btn"
        >
            Back to Invoice
        </a>
    </p>


    <div class="card">

        <table>

            <thead>

            <tr>
                <th>Date</th>
                <th>Environment</th>
                <th>Scenario</th>
                <th>HTTP</th>
                <th>FBR Status</th>
                <th>FBR Invoice Number</th>
                <th>Error</th>
            </tr>

            </thead>


            <tbody>

            @forelse($submissions as $submission)

                <tr>

                    <td>
                        {{ $submission->submitted_at
                            ? $submission->submitted_at->format('d-M-Y H:i:s')
                            : $submission->created_at->format('d-M-Y H:i:s')
                        }}
                    </td>

                    <td>
                        {{ strtoupper($submission->environment) }}
                    </td>

                    <td>
                        {{ $submission->scenario_id ?: '-' }}
                    </td>

                    <td>
                        {{ $submission->http_status ?: '-' }}
                    </td>

                    <td>
                        {{ $submission->fbr_status ?: '-' }}

                        @if($submission->fbr_status_code)

                            ({{ $submission->fbr_status_code }})

                        @endif
                    </td>

                    <td>
                        {{ $submission->fbr_invoice_number ?: '-' }}
                    </td>

                    <td>
                        {{ $submission->error_message ?: '-' }}
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="7">
                        No FBR submissions found.
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

@endsection
