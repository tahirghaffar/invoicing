@extends('layouts.app')

@section('title', 'Create Business')

@section('content')

    <h1>Create Business</h1>

    <div class="card">

        <form method="POST"
              action="{{ route('admin.businesses.store') }}">

            @csrf


            <h3>Business Information</h3>


            <label>Business Name</label>

            <input
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
            >


            <label>Legal Name</label>

            <input
                type="text"
                name="legal_name"
                value="{{ old('legal_name') }}"
            >


            <hr>


            <h3>Business Administrator</h3>


            <label>Administrator Name</label>

            <input
                type="text"
                name="admin_name"
                value="{{ old('admin_name') }}"
                required
            >


            <label>Email</label>

            <input
                type="email"
                name="admin_email"
                value="{{ old('admin_email') }}"
                required
            >


            <label>Password</label>

            <input
                type="password"
                name="admin_password"
                required
            >


            <button type="submit"
                    class="btn btn-primary">

                Create Business

            </button>

        </form>

    </div>

@endsection
