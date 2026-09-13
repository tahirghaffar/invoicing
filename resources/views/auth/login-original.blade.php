@extends('layouts.app')

@section('title', 'Login')

@section('content')

    <div class="card"
         style="max-width:450px;margin:80px auto;">

        <h2>Digital Invoicing Login..............</h2>

        <form method="POST"
              action="{{ route('login.submit') }}">

            @csrf

            <label>Email</label>

            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
            >


            <label>Password</label>

            <input
                type="password"
                name="password"
                required
            >


            <label style="font-weight:normal;">

                <input
                    type="checkbox"
                    name="remember"
                    value="1"
                    style="width:auto;"
                >

                Remember me

            </label>

            <br><br>

            <button class="btn btn-primary"
                    type="submit">

                Login

            </button>

        </form>

    </div>

@endsection
