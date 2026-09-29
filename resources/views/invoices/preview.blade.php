@extends('layouts.app')

@section('title', 'Invoice Preview')

@section('content')

    <div style="margin-bottom:20px;">

        <a
            href="{{ route('invoices.edit', $invoice) }}"
            class="btn"
        >
            Back to Invoice
        </a>


        <a
            href="{{ route('invoices.print', $invoice) }}"
            target="_blank"
            class="btn"
        >
            Print
        </a>


        <a
            href="{{ route('invoices.pdf', $invoice) }}"
            class="btn btn-primary"
        >
            Download PDF
        </a>


        @if(auth()->user()?->hasSystemRole('super-admin'))

            <a
                href="{{ route('invoices.fbr-json',$invoice) }}"
                class="btn"
            >
                FBR JSON
            </a>

        @endif


        <a
            href="{{ route('invoices.submissions', $invoice) }}"
            class="btn"
        >
            FBR History
        </a>


        @if(
            empty($displayFbrInvoiceNumber)
            && $invoice->sandbox_scenario_id
        )

            <button
                type="button"
                id="submit-sandbox-preview"
                class="btn btn-primary"
            >
                Submit to FBR Sandbox
            </button>

        @endif


        @if(
            config('fbr.production_enabled')
            && !$invoice->fbr_invoice_number
        )

            <button
                type="button"
                id="submit-production"
                class="btn"
            >
                Submit to FBR Production
            </button>

        @endif

    </div>


    @if($invoice->fbr_invoice_number)

        @php
            $correctionDeadline = $invoice->fbrCorrectionDeadline();
            $correctionOpen = $invoice->isWithinFbrCorrectionWindow();
        @endphp

        <div
            class="card"
            style="margin-bottom:20px;border-left:4px solid {{ $correctionOpen ? '#12b76a' : '#98a2b3' }};"
        >
            <div
                style="
                    display:flex;
                    justify-content:space-between;
                    align-items:flex-start;
                    gap:18px;
                    flex-wrap:wrap;
                "
            >
                <div style="min-width:260px;flex:1;">
                    <div style="font-size:16px;font-weight:800;margin-bottom:8px;">
                        FBR Production Invoice
                    </div>

                    <div style="font-size:13px;color:#475467;line-height:1.7;">
                        <div>
                            <strong>FBR No:</strong>
                            {{ $invoice->fbr_invoice_number }}
                        </div>

                        <div>
                            <strong>Submitted:</strong>
                            {{ $invoice->fbr_submitted_at?->format('d-M-Y h:i A') ?: '-' }}
                        </div>

                        <div>
                            <strong>72-hour correction window:</strong>
                            @if($correctionOpen)
                                <span style="color:#027a48;font-weight:800;">
                                    OPEN
                                </span>
                                until
                                {{ $correctionDeadline?->format('d-M-Y h:i A') }}
                                ({{ $correctionDeadline?->diffForHumans() }})
                            @else
                                <span style="color:#667085;font-weight:800;">
                                    EXPIRED
                                </span>
                                @if($correctionDeadline)
                                    on {{ $correctionDeadline->format('d-M-Y h:i A') }}
                                @endif
                            @endif
                        </div>

                        <div>
                            <strong>FBR remote status:</strong>
                            {{ $invoice->fbr_remote_status ?: 'Not synced yet' }}
                        </div>

                        <div>
                            <strong>Last synced:</strong>
                            {{ $invoice->fbr_last_synced_at?->format('d-M-Y h:i A') ?: 'Never' }}

                            @if($invoice->fbr_sync_status)
                                <span
                                    style="
                                        margin-left:6px;
                                        font-weight:700;
                                        color:{{ $invoice->fbr_sync_status === 'success' ? '#027a48' : '#b42318' }};
                                    "
                                >
                                    {{ strtoupper($invoice->fbr_sync_status) }}
                                </span>
                            @endif
                        </div>

                        @if($invoice->fbr_sync_message)
                            <div style="margin-top:4px;">
                                {{ $invoice->fbr_sync_message }}
                            </div>
                        @endif
                    </div>
                </div>

                <div
                    style="
                        display:flex;
                        gap:8px;
                        flex-wrap:wrap;
                        align-items:center;
                    "
                >
                    <a
                        href="{{ config('fbr_invoice_sync.iris_url') }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn"
                    >
                        <i class="bi bi-box-arrow-up-right"></i>
                        Manage on FBR IRIS
                    </a>

                    <button
                        type="button"
                        id="sync-fbr-invoice"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-arrow-repeat"></i>
                        Sync from FBR
                    </button>

                    <a
                        href="{{ route('invoices.fbr-sync-history', $invoice) }}"
                        class="btn"
                    >
                        <i class="bi bi-clock-history"></i>
                        Sync History
                    </a>
                </div>
            </div>

            <div
                style="
                    margin-top:14px;
                    padding:10px 12px;
                    background:#f9fafb;
                    border:1px solid #eaecf0;
                    border-radius:8px;
                    color:#475467;
                    font-size:13px;
                "
            >
                Corrections/deletions are performed on FBR IRIS. This local invoice remains locked.
                After making a correction on IRIS, use <strong>Sync from FBR</strong> to refresh the
                local invoice and tax values.
            </div>
        </div>

    @endif


    @include(
        'invoices._document',
        [
            'invoice' => $invoice,
            'qrCode' => $qrCode,
            'displayFbrInvoiceNumber' => $displayFbrInvoiceNumber,
            'isSandbox' => $isSandbox,
        ]
    )

@endsection


@push('scripts')

    <script>

        $(document).ready(function () {


            /*
            |--------------------------------------------------------------------------
            | Submit to FBR Sandbox
            |--------------------------------------------------------------------------
            */

            $('#submit-sandbox-preview').click(function () {

                if (!confirm(
                    'Submit this invoice to FBR Sandbox and generate a sandbox invoice number?'
                )) {
                    return;
                }

                let button = $(this);

                button
                    .prop('disabled', true)
                    .text('Submitting to Sandbox...');


                $.ajax({

                    url:
                        "{{ route(
                            'invoices.post-sandbox',
                            $invoice
                        ) }}",

                    type:
                        "POST",

                    data: {
                        _token:
                            "{{ csrf_token() }}"
                    },


                    success:
                        function (response) {

                            if (response.success) {

                                alert(
                                    'FBR Sandbox Invoice Created!\n\n' +
                                    response.fbr_invoice_number
                                );

                                /*
                                Reload preview so the FBR logo,
                                sandbox number and QR code appear.
                                */
                                location.reload();

                            } else {

                                alert(
                                    response.message
                                    ?? 'FBR Sandbox rejected the invoice.'
                                );

                                button
                                    .prop('disabled', false)
                                    .text('Submit to FBR Sandbox');
                            }
                        },


                    error:
                        function (xhr) {

                            alert(
                                xhr.responseJSON?.message
                                ?? 'Sandbox submission failed.'
                            );

                            button
                                .prop('disabled', false)
                                .text('Submit to FBR Sandbox');
                        }

                });

            });


            /*
            |--------------------------------------------------------------------------
            | Submit to FBR Production
            |--------------------------------------------------------------------------
            */

            $('#submit-production').click(function () {

                if (!confirm(
                    'IMPORTANT: This will create a REAL FBR tax invoice. Continue?'
                )) {
                    return;
                }

                let button = $(this);

                button
                    .prop('disabled', true)
                    .text('Submitting to FBR...');

                $.ajax({

                    url: "{{ route(
                        'invoices.submit-production',
                        $invoice
                    ) }}",

                    type: "POST",

                    data: {
                        _token:
                            "{{ csrf_token() }}"
                    },

                    success: function(response) {

                        if (response.success) {

                            alert(
                                'FBR Invoice Created!\n\n' +
                                response.fbr_invoice_number
                            );

                            location.reload();

                        } else {

                            alert(response.message);

                            button
                                .prop('disabled', false)
                                .text('Submit to FBR Production');
                        }
                    },

                    error: function(xhr) {

                        alert(
                            xhr.responseJSON?.message
                            ?? 'Production submission failed.'
                        );

                        button
                            .prop('disabled', false)
                            .text('Submit to FBR Production');
                    }

                });

            });


            /*
            |--------------------------------------------------------------------------
            | Synchronize production invoice from FBR
            |--------------------------------------------------------------------------
            */

            $('#sync-fbr-invoice').click(function () {

                if (!confirm(
                    'Fetch the latest production invoice details from FBR and update the local invoice snapshot?\n\n' +
                    'Any changes made on IRIS will be synchronized locally and recorded in Sync History.'
                )) {
                    return;
                }

                let button = $(this);
                let originalHtml = button.html();

                button
                    .prop('disabled', true)
                    .html('<span>Synchronizing...</span>');

                $.ajax({
                    url: "{{ route('invoices.sync-fbr', $invoice) }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        alert(response.message);
                        location.reload();
                    },
                    error: function (xhr) {
                        alert(
                            xhr.responseJSON?.message
                            ?? 'FBR synchronization failed.'
                        );

                        button
                            .prop('disabled', false)
                            .html(originalHtml);
                    }
                });
            });

        });

    </script>

@endpush
