@extends('layouts.app')

@section('title', 'Add Customer')

@section('content')

    <h1>Add Customer</h1>

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

    @include('customers._form')

@endsection


@push('scripts')

    <script>

        $(function () {

            toggleRegistrationFields();

            $('#registration_type').on(
                'change',
                toggleRegistrationFields
            );


            function toggleRegistrationFields()
            {
                let type =
                    $('#registration_type').val();

                if (type === 'registered') {

                    $('#strn-container').show();

                } else {

                    $('#strn-container').hide();

                    $('input[name="strn"]').val('');

                }
            }


            $('#customer-form').on(
                'submit',
                function (e) {

                    e.preventDefault();

                    let button =
                        $('#save-customer');

                    $('#form-errors')
                        .hide()
                        .html('');

                    $('#success-message')
                        .hide()
                        .html('');

                    button
                        .prop('disabled', true)
                        .text('Saving...');


                    $.ajax({

                        url:
                            "{{ route('customers.store') }}",

                        type: "POST",

                        data: $(this).serialize(),

                        success: function (response) {

                            $('#success-message')
                                .html(response.message)
                                .show();

                            $('#customer-form')[0].reset();

                            $('#registration_type')
                                .val('unregistered');

                            toggleRegistrationFields();

                        },

                        error: function (xhr) {

                            showErrors(xhr);

                        },

                        complete: function () {

                            button
                                .prop('disabled', false)
                                .text('Save Customer');

                        }

                    });

                }
            );


            function showErrors(xhr)
            {
                let html = '';

                if (
                    xhr.status === 422 &&
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

        });

    </script>

@endpush
