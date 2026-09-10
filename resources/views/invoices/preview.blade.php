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

        <a
            href="{{ route('invoices.fbr-json',$invoice) }}"
            class="btn btn-primary"
        >
            FBR JSON
        </a>

        <a
            href="{{ route('invoices.submissions', $invoice) }}"
            class="btn"
        >
            FBR History
        </a>

        @if(!$invoice->fbr_invoice_number)

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
        ['invoice' => $invoice]
    )

@endsection
@push('scripts')

    <script>
        $(document).ready(function () {

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
                        _token: "{{ csrf_token() }}"
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
