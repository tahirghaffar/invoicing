@extends('layouts.app')

@section('title', 'Add Product / Service')

@section('content')

    <h1>Add Product / Service</h1>

    <div
        id="success-message"
        class="success"
        style="display:none;">
    </div>

    <div
        id="form-errors"
        class="error"
        style="display:none;">
    </div>

    @include('products._form')

@endsection


@push('scripts')

    <script>

        $(function () {

            $('#product-form').on('submit', function (e) {

                e.preventDefault();

                let button = $('#save-product');

                $('#success-message')
                    .hide()
                    .html('');

                $('#form-errors')
                    .hide()
                    .html('');

                button
                    .prop('disabled', true)
                    .text('Saving...');


                $.ajax({

                    url: "{{ route('products.store') }}",

                    type: "POST",

                    data: $(this).serialize(),

                    success: function (response) {

                        $('#success-message')
                            .html(response.message)
                            .show();

                        $('#product-form')[0].reset();

                    },

                    error: function (xhr) {

                        showErrors(xhr);

                    },

                    complete: function () {

                        button
                            .prop('disabled', false)
                            .text('Save Product / Service');

                    }

                });

            });


            function showErrors(xhr)
            {
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
                                function (i, message) {

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
            }

            $('#transaction_type_id').on(
                'change',
                loadRates
            );


            function loadRates()
            {
                let transactionTypeId =
                    $('#transaction_type_id').val();

                let rateSelect =
                    $('#rate_id');

                rateSelect.html(
                    '<option value="">Loading...</option>'
                );


                if (!transactionTypeId) {

                    rateSelect.html(
                        '<option value="">Select Sale Type First</option>'
                    );

                    return;
                }


                $.ajax({

                    url:
                        "{{ route('fbr.references.rates') }}",

                    type:
                        "GET",

                    data: {

                        transaction_type_id:
                        transactionTypeId,

                        date:
                            "{{ now()->format('Y-m-d') }}"

                    },

                    success: function (rates) {

                        let html =
                            '<option value="">Select Rate</option>';

                        $.each(
                            rates,
                            function (index, rate) {

                                html +=
                                    '<option value="' +
                                    rate.ratE_ID +
                                    '" ' +

                                    'data-value="' +
                                    rate.ratE_VALUE +
                                    '" ' +

                                    'data-description="' +
                                    $('<div>')
                                        .text(rate.ratE_DESC)
                                        .html() +
                                    '">' +

                                    rate.ratE_DESC +

                                    '</option>';

                            }
                        );

                        rateSelect.html(html);

                    },

                    error: function (xhr) {

                        let message =
                            xhr.responseJSON?.message ??
                            'Unable to retrieve tax rates.';

                        rateSelect.html(
                            '<option value="">' +
                            message +
                            '</option>'
                        );

                    }

                });
            }

            let hsTimer = null;


            $('#hs_code').on('keyup', function () {

                clearTimeout(hsTimer);

                let search =
                    $(this).val().trim();

                if (search.length < 2) {

                    $('#hs-results')
                        .hide()
                        .html('');

                    return;
                }


                hsTimer = setTimeout(
                    function () {

                        $.ajax({

                            url:
                                "{{ route('fbr.references.hs-codes') }}",

                            type:
                                "GET",

                            data: {
                                q: search
                            },

                            success: function (results) {

                                let html = '';

                                $.each(
                                    results,
                                    function (index, item) {

                                        html +=
                                            '<div ' +
                                            'class="hs-option" ' +
                                            'data-code="' +
                                            item.hs_code +
                                            '" ' +
                                            'style="padding:10px;cursor:pointer;border-bottom:1px solid #eee;">' +

                                            '<strong>' +
                                            item.hs_code +
                                            '</strong><br>' +

                                            item.description +

                                            '</div>';

                                    }
                                );


                                $('#hs-results')
                                    .html(html)
                                    .show();

                            }

                        });

                    },
                    300
                );

            });


            $(document).on(
                'click',
                '.hs-option',
                function () {

                    $('#hs_code').val(
                        $(this).data('code')
                    );

                    $('#hs-results')
                        .hide()
                        .html('');

                }
            );

        });

    </script>

@endpush
