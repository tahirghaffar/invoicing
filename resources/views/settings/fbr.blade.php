@extends('layouts.app')

@section('title', 'FBR / PRAL Configuration')

@section('content')

    <h1>FBR / PRAL Configuration</h1>

    <p>
        Configure FBR Digital Invoicing credentials for
        <strong>{{ $business->name }}</strong>.
    </p>


    <div id="success-message"
         class="success"
         style="display:none;">
    </div>

    <div id="form-errors"
         class="error"
         style="display:none;">
    </div>


    <div class="card">

        <form id="fbr-settings-form">

            @csrf
            @method('PUT')


            <h3>Environment</h3>


            <label>Current Environment</label>

            <select name="environment">

                <option value="sandbox"
                    {{ $credential->environment === 'sandbox' ? 'selected' : '' }}>

                    Sandbox

                </option>

                <option value="production"
                    {{ $credential->environment === 'production' ? 'selected' : '' }}>

                    Production

                </option>

            </select>


            <hr>


            <h3>Sandbox Configuration</h3>


            <label>Sandbox API URL</label>

            <input
                type="url"
                name="sandbox_api_url"
                value="{{ $credential->sandbox_api_url }}"
            >


            <label>Sandbox Token</label>

            <input
                type="password"
                name="sandbox_token"
                autocomplete="new-password"
                placeholder="{{ $credential->sandbox_token ? 'Token already saved — leave blank to keep it' : 'Enter Sandbox Token' }}"
            >


            @if($credential->sandbox_token)

                <p>
                    ✅ Sandbox token saved
                </p>

            @else

                <p>
                    ❌ Sandbox token not configured
                </p>

            @endif


            <hr>


            <h3>Production Configuration</h3>


            <label>Production API URL</label>

            <input
                type="url"
                name="production_api_url"
                value="{{ $credential->production_api_url }}"
                required
            >


            <label>Production Token</label>

            <input
                type="password"
                name="production_token"
                autocomplete="new-password"
                placeholder="{{ $credential->production_token ? 'Token already saved — leave blank to keep it' : 'Enter Production Token' }}"
            >


            @if($credential->production_token)

                <p>
                    ✅ Production token saved
                </p>

            @else

                <p>
                    ❌ Production token not configured
                </p>

            @endif


            <hr>


            <h3>Production Safety</h3>


            <label style="font-weight:normal;">

                <input
                    type="checkbox"
                    name="production_enabled"
                    value="1"
                    style="width:auto;"
                    {{ $credential->production_enabled ? 'checked' : '' }}
                >

                Enable production submission for this business

            </label>


            <p>

                Application-wide production status:

                @if(config('fbr.production_enabled'))

                    <strong>
                        ENABLED
                    </strong>

                @else

                    <strong>
                        DISABLED
                    </strong>

                @endif

            </p>


            @if(!config('fbr.production_enabled'))

                <div class="error">

                    Production submission is currently disabled
                    application-wide.

                    No real FBR invoice can be submitted.

                </div>

            @endif


            <br>


            <button
                type="submit"
                id="save-fbr-settings"
                class="btn btn-primary">

                Save FBR Configuration

            </button>

        </form>

    </div>

    <!-- Another card -->

    <div class="card">

        <h3>FBR Reference Data</h3>

        <p>
            Synchronize provinces, HS codes, UOMs,
            transaction types and document types from FBR.
        </p>

        <button
            type="button"
            id="sync-fbr-references-disabled"
            class="btn btn-gray"
            disabled
        >
            Sync FBR Reference Data
        </button>

        <div
            id="sync-result"
            style="margin-top:15px;">
        </div>

    </div>

@endsection


@push('scripts')

    <script>

        $(document).ready(function () {

            $('#fbr-settings-form').on('submit', function (e) {

                e.preventDefault();

                let button = $('#save-fbr-settings');

                $('#success-message').hide().html('');
                $('#form-errors').hide().html('');

                button
                    .prop('disabled', true)
                    .text('Saving...');


                $.ajax({

                    url: "{{ route('fbr.settings.update') }}",

                    type: "POST",

                    data: $(this).serialize(),

                    success: function (response) {

                        $('#success-message')
                            .html(response.message)
                            .show();

                        $('input[name="sandbox_token"]').val('');
                        $('input[name="production_token"]').val('');

                    },

                    error: function (xhr) {

                        let html = '';

                        if (
                            xhr.status === 422 &&
                            xhr.responseJSON &&
                            xhr.responseJSON.errors
                        ) {

                            $.each(
                                xhr.responseJSON.errors,
                                function (field, messages) {

                                    $.each(
                                        messages,
                                        function (index, message) {

                                            html +=
                                                '<div>' +
                                                message +
                                                '</div>';

                                        }
                                    );

                                }
                            );

                        } else {

                            html =
                                '<div>An unexpected error occurred.</div>';

                        }

                        $('#form-errors')
                            .html(html)
                            .show();

                    },

                    complete: function () {

                        button
                            .prop('disabled', false)
                            .text('Save FBR Configuration');

                    }

                });

            });

            $('#sync-fbr-references').on('click', function () {

                let button = $(this);

                button
                    .prop('disabled', true)
                    .text('Synchronizing...');

                $('#sync-result').html('');


                $.ajax({

                    url:
                        "{{ route('fbr.references.sync') }}",

                    type:
                        "POST",

                    data: {
                        _token:
                            "{{ csrf_token() }}"
                    },

                    success: function (response) {

                        let data = response.data;

                        let html =
                            '<div class="success">' +
                            response.message +
                            '<br><br>' +

                            'Provinces: ' +
                            data.provinces +
                            '<br>' +

                            'Document Types: ' +
                            data.document_types +
                            '<br>' +

                            'HS Codes: ' +
                            data.hs_codes +
                            '<br>' +

                            'Transaction Types: ' +
                            data.transaction_types +
                            '<br>' +

                            'UOMs: ' +
                            data.uoms +

                            '</div>';

                        $('#sync-result').html(html);

                    },

                    error: function (xhr) {

                        let message =
                            xhr.responseJSON?.message ??
                            'Reference synchronization failed.';

                        $('#sync-result').html(
                            '<div class="error">' +
                            message +
                            '</div>'
                        );

                    },

                    complete: function () {

                        button
                            .prop('disabled', false)
                            .text(
                                'Sync FBR Reference Data'
                            );

                    }

                });

            });

        });

    </script>

@endpush
