@extends('layouts.app')

@section('title', 'Select Business')

@section('content')

    <h1>Select Business</h1>

    <div class="card">

        <form method="POST"
              action="{{ route('business.select.store') }}">

            @csrf

            <label>Business</label>

            <select name="business_id"
                    required>

                @foreach($businesses as $business)

                    <option value="{{ $business->id }}">
                        {{ $business->name }}
                    </option>

                @endforeach

            </select>

            <button type="submit"
                    class="btn btn-primary">

                Continue

            </button>

        </form>

    </div>

@endsection
