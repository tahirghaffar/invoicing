@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    <h1>
        {{ $currentBusiness->name }}
    </h1>

    <div class="card">

        <h2>Dashboard</h2>

        <p>
            Business ID:
            {{ $currentBusiness->id }}
        </p>

        <p>
            Logged in as:
            {{ auth()->user()->name }}
        </p>

        <p>
            <a href="{{ route('business.profile.edit') }}"
               class="btn btn-primary">
                Business Profile
            </a>
        </p>

        <a href="{{ route('fbr.settings.edit') }}"
           class="btn btn-primary">
            FBR / PRAL Settings
        </a>

        <a
            href="{{ route('customers.index') }}"
            class="btn btn-primary"
        >
            Customers
        </a>

        <a
            href="{{ route('products.index') }}"
            class="btn btn-primary"
        >
            Products / Services
        </a>

        <a
            href="{{ route('invoices.index') }}"
            class="btn btn-primary"
        >
            Invoices
        </a>

    </div>

@endsection
