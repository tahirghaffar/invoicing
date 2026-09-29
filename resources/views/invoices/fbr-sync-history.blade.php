@extends('layouts.app')

@section('title', 'FBR Sync History')

@section('content')

    <div
        style="
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:12px;
            flex-wrap:wrap;
            margin-bottom:18px;
        "
    >
        <div>
            <h1 style="margin-bottom:4px;">FBR Sync History</h1>
            <div style="color:#667085;font-size:13px;">
                Local Invoice: <strong>{{ $invoice->invoice_number }}</strong>
                &nbsp;|&nbsp;
                FBR No: <strong>{{ $invoice->fbr_invoice_number }}</strong>
            </div>
        </div>

        <a
            href="{{ route('invoices.preview', $invoice) }}"
            class="btn"
        >
            Back to Invoice
        </a>
    </div>


    <div class="card">

        <table>
            <thead>
            <tr>
                <th>Synced At</th>
                <th>Status</th>
                <th>Remote Status</th>
                <th>Changes</th>
                <th>User</th>
                <th>Details</th>
            </tr>
            </thead>

            <tbody>
            @forelse($syncs as $sync)
                <tr>
                    <td>
                        {{ $sync->synced_at?->format('d-M-Y h:i A')
                            ?: $sync->created_at?->format('d-M-Y h:i A') }}
                    </td>

                    <td>
                        <span
                            style="
                                font-weight:800;
                                color:{{ $sync->sync_status === 'success' ? '#027a48' : '#b42318' }};
                            "
                        >
                            {{ strtoupper($sync->sync_status) }}
                        </span>
                    </td>

                    <td>
                        {{ $sync->remote_status ?: '-' }}
                    </td>

                    <td>
                        {{ is_array($sync->differences)
                            ? count($sync->differences)
                            : 0 }}
                    </td>

                    <td>
                        {{ $sync->user?->name ?: '-' }}
                    </td>

                    <td style="min-width:250px;">
                        @if($sync->error_message)
                            <div style="color:#b42318;margin-bottom:8px;">
                                {{ $sync->error_message }}
                            </div>
                        @endif

                        @if(!empty($sync->differences))
                            <details style="margin-bottom:8px;">
                                <summary style="cursor:pointer;font-weight:700;">
                                    View Changes
                                </summary>

                                <div style="margin-top:10px;overflow:auto;">
                                    <table style="font-size:12px;">
                                        <thead>
                                        <tr>
                                            <th>Field</th>
                                            <th>Before</th>
                                            <th>After</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($sync->differences as $difference)
                                            <tr>
                                                <td>
                                                    {{ $difference['field'] ?? '-' }}
                                                </td>
                                                <td>
                                                    {{ is_scalar($difference['old'] ?? null)
                                                        ? ($difference['old'] ?? '-')
                                                        : json_encode($difference['old'] ?? null) }}
                                                </td>
                                                <td>
                                                    {{ is_scalar($difference['new'] ?? null)
                                                        ? ($difference['new'] ?? '-')
                                                        : json_encode($difference['new'] ?? null) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </details>
                        @endif

                        @if($sync->response_payload)
                            <details>
                                <summary style="cursor:pointer;font-weight:700;">
                                    Raw FBR Response
                                </summary>
                                <pre
                                    style="
                                        margin-top:10px;
                                        white-space:pre-wrap;
                                        word-break:break-word;
                                        max-height:360px;
                                        overflow:auto;
                                        font-size:11px;
                                        background:#f9fafb;
                                        padding:10px;
                                        border:1px solid #eaecf0;
                                        border-radius:8px;
                                    "
                                >{{ json_encode($sync->response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            </details>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        No FBR synchronization has been performed yet.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

    </div>


    <div class="card" style="margin-top:15px;">
        <div
            style="
                display:flex;
                justify-content:space-between;
                align-items:center;
                gap:12px;
                flex-wrap:wrap;
            "
        >
            <div style="color:#667085;font-size:13px;">
                Showing
                <strong>{{ $syncs->count() ? $syncs->firstItem() : 0 }}</strong>
                to
                <strong>{{ $syncs->count() ? $syncs->lastItem() : 0 }}</strong>
                of
                <strong>{{ $syncs->total() }}</strong>
                sync record(s)
            </div>

            <div>
                {{ $syncs->links() }}
            </div>
        </div>
    </div>

@endsection
