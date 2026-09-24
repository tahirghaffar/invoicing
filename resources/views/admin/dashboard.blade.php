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

    <div class="card">

        <h3>Monthly Invoice Usage Alerts</h3>

        @if($monthlyInvoiceAlerts->isEmpty())

            <p style="margin-bottom:0;">
                No businesses are currently at or above
                90% of their monthly invoice limit.
            </p>

        @else

            <div style="overflow-x:auto;">

                <table style="width:100%;">
                    <thead>
                    <tr>
                        <th>Business</th>
                        <th>Usage</th>
                        <th>Utilization</th>
                        <th>Status</th>
                    </tr>
                    </thead>

                    <tbody>

                    @foreach($monthlyInvoiceAlerts as $alert)

                        <tr>
                            <td>{{ $alert['business_name'] }}</td>

                            <td>
                                <strong>
                                    {{ $alert['count'] }}
                                    /
                                    {{ $alert['limit'] }}
                                </strong>
                            </td>

                            <td>
                                {{ number_format(
                                    $alert['percentage'],
                                    1
                                ) }}%
                            </td>

                            <td>
                                @if($alert['reached'])
                                    <span style="color:#b42318;font-weight:700;">
                                        LIMIT REACHED
                                    </span>
                                @else
                                    <span style="color:#b54708;font-weight:700;">
                                        NEAR LIMIT
                                    </span>
                                @endif
                            </td>
                        </tr>

                    @endforeach

                    </tbody>
                </table>

            </div>

        @endif

    </div>

    <a href="{{ route('admin.businesses.index') }}"
       class="btn btn-primary">
        Manage Businesses
    </a>

@endsection
