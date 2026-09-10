
@extends('layouts.app')

@section('title', 'Business Profile')

@section('content')

    <h1>Business Profile</h1>

    <p>
        Configure the seller information that will later be used
        for FBR Digital Invoices.
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

        <form id="business-profile-form">

            @csrf
            @method('PUT')


            <h3>Business Information</h3>


            <label>Business Name</label>

            <input
                type="text"
                name="name"
                value="{{ $business->name }}"
                required
            >


            <label>Legal / Registered Business Name</label>

            <input
                type="text"
                name="legal_name"
                value="{{ $business->legal_name }}"
                required
            >


            <hr>


            <h3>FBR Registration</h3>


            <label>NTN</label>

            <input
                type="text"
                name="ntn"
                value="{{ $business->ntn }}"
                required
            >


            <label>STRN</label>

            <input
                type="text"
                name="strn"
                value="{{ $business->strn }}"
            >


            <label>Registration Type</label>

            <select name="registration_type">

                <option value="registered"
                    {{ $business->registration_type === 'registered' ? 'selected' : '' }}>

                    Registered

                </option>

            </select>


            <hr>


            <h3>Seller Address</h3>


            <label>Province</label>

            <select
                name="province_code"
                id="province_code"
                required
            >

                <option value="">
                    Select Province
                </option>

                @foreach($provinces as $province)

                    <option
                        value="{{ $province->code }}"

                        {{ (string)$business->province_code === (string)$province->code
                            ? 'selected'
                            : ''
                        }}
                    >

                        {{ $province->description }}

                    </option>

                @endforeach

            </select>


            <label>City</label>

            <input
                type="text"
                name="city"
                value="{{ $business->city }}"
                required
            >


            <label>Address</label>

            <textarea
                name="address"
                rows="4"
                style="width:100%;padding:10px;margin-top:5px;margin-bottom:15px;"
                required
            >{{ $business->address }}</textarea>


            <hr>


            <h3>Contact Information</h3>


            <label>Email</label>

            <input
                type="email"
                name="email"
                value="{{ $business->email }}"
            >


            <label>Phone</label>

            <input
                type="text"
                name="phone"
                value="{{ $business->phone }}"
            >


            <hr>


            <h3>Business Activity</h3>


            <label>Principal Activity Code</label>

            <input
                type="text"
                name="principal_activity_code"
                value="{{ $business->principal_activity_code }}"
            >


            <label>Principal Activity Description</label>

            <input
                type="text"
                name="principal_activity_description"
                value="{{ $business->principal_activity_description }}"
            >


            <button
                type="submit"
                class="btn btn-primary"
                id="save-profile"
            >

                Save Business Profile

            </button>

        </form>

    </div>

@endsection


@push('scripts')

    <script>

        $(document).ready(function () {

            $('#business-profile-form').on('submit', function (e) {

                e.preventDefault();

                let form = $(this);

                let button = $('#save-profile');

                $('#success-message').hide().html('');
                $('#form-errors').hide().html('');

                button.prop('disabled', true)
                    .text('Saving...');


                $.ajax({

                    url: "{{ route('business.profile.update') }}",

                    type: "POST",

                    data: form.serialize(),

                    success: function (response) {

                        $('#success-message')
                            .html(response.message)
                            .show();

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

                                    $.each(messages, function (index, message) {

                                        html += '<div>' + message + '</div>';

                                    });

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

                        button.prop('disabled', false)
                            .text('Save Business Profile');

                    }

                });

            });

        });

    </script>

@endpush
