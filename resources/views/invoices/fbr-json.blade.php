@extends('layouts.app')

@section('title', 'FBR JSON Preview')

@section('content')

    <h1>FBR JSON Preview</h1>

    <p>
        Invoice:
        <strong>
            {{ $invoice->invoice_number }}
        </strong>
    </p>


    <div class="card">

        <form method="GET">

            <label>
                Sandbox Scenario
            </label>

            <select
                name="scenario_id"
                id="scenario_id"
            >

                <option value="">
                    Production-shaped JSON
                </option>

                @foreach($allowedScenarios as $scenario)

                    <option
                        value="{{ $scenario->scenario_code }}"
                        {{ $scenarioId === $scenario->scenario_code
                            ? 'selected'
                            : ''
                        }}
                    >

                        {{ $scenario->scenario_code }}

                        @if($scenario->description)
                            - {{ $scenario->description }}
                        @endif

                    </option>

                @endforeach

            </select>


            <button
                type="submit"
                class="btn"
            >
                Apply Scenario
            </button>

        </form>

    </div>


    <div class="card">

        <h3>Generated JSON</h3>

        <pre style="
    background:#111;
    color:#eee;
    padding:20px;
    overflow:auto;
    white-space:pre-wrap;
">{{ json_encode(
    $payload,
    JSON_PRETTY_PRINT
    | JSON_UNESCAPED_SLASHES
    | JSON_UNESCAPED_UNICODE
) }}</pre>

    </div>


    @if($scenarioId)

        <div class="card">

            <h3>
                Sandbox Validation
            </h3>

            <p>
                This sends the JSON only to the
                FBR Sandbox Validation API.
            </p>


            <button
                type="button"
                id="validate-sandbox"
                class="btn btn-primary"
            >
                Validate with FBR Sandbox
            </button>

            <button
                type="button"
                id="post-sandbox"
                class="btn"
            >
                Submit to FBR Sandbox
            </button>


            <div
                id="validation-result"
                style="margin-top:20px;">
            </div>

        </div>

    @endif


    <a
        href="{{ route(
        'invoices.preview',
        $invoice
    ) }}"
        class="btn"
    >
        Back to Invoice
    </a>

@endsection


@push('scripts')

    <script>

        $(function () {

            $('#validate-sandbox').on(
                'click',
                function () {

                    let button = $(this);

                    button
                        .prop('disabled', true)
                        .text('Validating...');

                    $('#validation-result')
                        .html('');


                    $.ajax({

                        url:
                            "{{ route(
                        'invoices.validate-sandbox',
                        $invoice
                    ) }}",

                        type:
                            "POST",

                        data: {

                            _token:
                                "{{ csrf_token() }}",

                            scenario_id:
                                "{{ $scenarioId }}"

                        },

                        success:
                            function (response) {

                                let fbr =
                                    response.fbr_response;

                                let validation =
                                    fbr.validationResponse
                                    ?? {};

                                let className =
                                    validation.status === 'Valid'
                                        ? 'success'
                                        : 'error';


                                let html =
                                    '<div class="' +
                                    className +
                                    '">' +

                                    '<strong>Status:</strong> ' +
                                    (validation.status ?? '-') +

                                    '<br>' +

                                    '<strong>Status Code:</strong> ' +
                                    (validation.statusCode ?? '-') +

                                    '<br>' +

                                    '<strong>Error:</strong> ' +
                                    (validation.error ?? '-') +

                                    '</div>';


                                html +=
                                    '<pre style="' +
                                    'background:#111;' +
                                    'color:#eee;' +
                                    'padding:15px;' +
                                    'overflow:auto;">' +
                                    $('<div>')
                                        .text(
                                            JSON.stringify(
                                                fbr,
                                                null,
                                                2
                                            )
                                        )
                                        .html() +
                                    '</pre>';


                                $('#validation-result')
                                    .html(html);

                            },


                        error:
                            function (xhr) {

                                let message =
                                    xhr.responseJSON?.message
                                    ?? 'Sandbox validation failed.';

                                $('#validation-result')
                                    .html(
                                        '<div class="error">' +
                                        message +
                                        '</div>'
                                    );

                            },

                        complete:
                            function () {

                                button
                                    .prop(
                                        'disabled',
                                        false
                                    )
                                    .text(
                                        'Validate with FBR Sandbox'
                                    );

                            }

                    });

                }
            );

            $('#post-sandbox').on('click', function () {

                if (!confirm(
                    'Submit this invoice to FBR Sandbox?'
                )) {
                    return;
                }

                let button = $(this);

                button
                    .prop('disabled', true)
                    .text('Submitting...');

                $.ajax({

                    url: "{{ route('invoices.post-sandbox',$invoice) }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        scenario_id: "{{ $scenarioId }}"
                    },

                    success: function (response) {

                        let html =
                            '<div class="' +
                            (response.success ? 'success' : 'error') +
                            '">' +

                            response.message +

                            '<br><strong>FBR Invoice:</strong> ' +
                            (response.fbr_invoice_number ?? '-') +

                            '</div>';

                        $('#validation-result')
                            .html(html);

                    },

                    error: function (xhr) {

                        $('#validation-result').html(
                            '<div class="error">' +
                            (xhr.responseJSON?.message
                                ?? 'Sandbox submission failed.') +
                            '</div>'
                        );

                    },

                    complete: function () {

                        button
                            .prop('disabled', false)
                            .text('Submit to FBR Sandbox');

                    }

                });

            });

        });

    </script>

@endpush
