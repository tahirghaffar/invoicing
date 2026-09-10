@extends('layouts.app')

@section('title', 'Businesses')

@section('content')

    <h1>Businesses</h1>

    <p>

        <a href="{{ route('admin.businesses.create') }}"
           class="btn btn-primary">

            + Add Business

        </a>



    </p>


    <div class="card">

        <table>

            <thead>

            <tr>
                <th>ID</th>
                <th>Business</th>
                <th>Login User</th>
                <th>Legal Name</th>
                <th>Status</th>
                <th>Created</th>
                <th>Sandbox Scenarios</th>
            </tr>

            </thead>


            <tbody>
            @php
//                echo '<pre>';
//                print_r($businesses);
//                echo '</pre>';
            @endphp
            @foreach($businesses as $business)
                @php
                    $businessUser = $business->users->first();
                @endphp
                <tr>

                    <td>{{ $business->id }}</td>

                    <td>{{ $business->name }}</td>

                    <td>{{ $businessUser?->email ?? '-' }}</td>
                    <td>{{ $business->legal_name }}</td>

                    <td>{{ ucfirst($business->status) }}</td>

                    <td>
                        {{ $business->created_at->format('d-M-Y') }}
                    </td>
                    <td>
                        <a
                            class="btn btn-success"
                            href="{{ route(
                        'admin.businesses.sandbox-scenarios.edit',
                        $business
                    ) }}"
                            class="btn"
                        >
                            Sandbox Scenarios
                        </a>
                    </td>
                </tr>

            @endforeach

            </tbody>

        </table>

    </div>

    {{ $businesses->links() }}

@endsection
