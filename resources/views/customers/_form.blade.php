<div class="card">

    <form id="customer-form">

        @csrf

        @if(isset($customer))
            @method('PUT')
        @endif


        <h3>Buyer Information</h3>


        <label>Business / Customer Name *</label>

        <input
            type="text"
            name="business_name"
            value="{{ old('business_name', $customer->business_name ?? '') }}"
            required
        >


        <label>Contact Person</label>

        <input
            type="text"
            name="contact_person"
            value="{{ old('contact_person', $customer->contact_person ?? '') }}"
        >


        <label>Registration Type *</label>

        <select
            name="registration_type"
            id="registration_type"
            required
        >

            <option value="registered"
                {{ old('registration_type', $customer->registration_type ?? 'unregistered') === 'registered' ? 'selected' : '' }}>

                Registered

            </option>

            <option value="unregistered"
                {{ old('registration_type', $customer->registration_type ?? 'unregistered') === 'unregistered' ? 'selected' : '' }}>

                Unregistered

            </option>

        </select>


        <div id="ntn-container">

            <label>NTN / CNIC</label>

            <input
                type="text"
                name="ntn_cnic"
                value="{{ old('ntn_cnic', $customer->ntn_cnic ?? '') }}"
            >

        </div>


        <div id="strn-container">

            <label>STRN</label>

            <input
                type="text"
                name="strn"
                value="{{ old('strn', $customer->strn ?? '') }}"
            >

        </div>


        <hr>


        <h3>Address</h3>


        <label>Province</label>

        <select name="province_code">

            <option value="">
                Select Province
            </option>

            @foreach($provinces as $province)

                <option
                    value="{{ $province->code }}"
                    {{ (string) old(
                        'province_code',
                        $customer->province_code ?? ''
                    ) === (string) $province->code ? 'selected' : '' }}
                >
                    {{ $province->description }}
                </option>

            @endforeach

        </select>


        <label>City</label>

        <input
            type="text"
            name="city"
            value="{{ old('city', $customer->city ?? '') }}"
        >


        <label>Address</label>

        <textarea
            name="address"
            rows="4"
        >{{ old('address', $customer->address ?? '') }}</textarea>


        <hr>


        <h3>Contact</h3>


        <label>Email</label>

        <input
            type="email"
            name="email"
            value="{{ old('email', $customer->email ?? '') }}"
        >


        <label>Phone</label>

        <input
            type="text"
            name="phone"
            value="{{ old('phone', $customer->phone ?? '') }}"
        >


        <label>Status</label>

        <select name="status">

            <option value="active"
                {{ old('status', $customer->status ?? 'active') === 'active' ? 'selected' : '' }}>
                Active
            </option>

            <option value="inactive"
                {{ old('status', $customer->status ?? 'active') === 'inactive' ? 'selected' : '' }}>
                Inactive
            </option>

        </select>


        <button
            type="submit"
            id="save-customer"
            class="btn btn-primary"
        >

            {{ isset($customer) ? 'Update Customer' : 'Save Customer' }}

        </button>

    </form>

</div>
