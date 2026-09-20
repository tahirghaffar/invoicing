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

        });

    </script>

@endpush
