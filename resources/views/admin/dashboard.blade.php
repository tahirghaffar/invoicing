@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')

    <h1>System Administration</h1>

    <div class="card">

        <h3>Businesses</h3>

        <h1>{{ $businessCount }}</h1>

    </div>

    <div class="card">

        <h3>Users</h3>

        <h1>{{ $userCount }}</h1>

    </div>

    <a href="{{ route('admin.businesses.index') }}"
       class="btn btn-primary">

        Manage Businesses

    </a>

@endsection
