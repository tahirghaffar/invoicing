@extends('layouts.app')

@section('title', 'Sandbox Scenarios')

@section('content')

    <h1>Sandbox Scenarios</h1>

    <p>
        Business:
        <strong>{{ $business->name }}</strong>
    </p>


    <div class="card">

        <form
            method="POST"
            action="{{ route(
        'admin.businesses.sandbox-scenarios.update',
        $business
    ) }}"
        >

            @csrf
            @method('PUT')


            @foreach($scenarios as $scenario)

                <label
                    style="
            display:block;
            margin-bottom:12px;
            font-weight:normal;
        "
                >

                    <input
                        type="checkbox"
                        name="scenario_ids[]"
                        value="{{ $scenario->id }}"
                        style="width:auto;"
                        {{ in_array(
                            $scenario->id,
                            $assignedIds
                        ) ? 'checked' : '' }}
                    >

                    <strong>
                        {{ $scenario->scenario_code }}
                    </strong>

                    @if($scenario->description)

                        — {{ $scenario->description }}

                    @endif

                </label>

            @endforeach


            <br>

            <button
                type="submit"
                class="btn btn-primary"
            >
                Save Scenario Assignment
            </button>

        </form>

    </div>

@endsection
