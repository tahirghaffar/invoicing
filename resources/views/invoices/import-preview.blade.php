@extends('layouts.app')

@section('title', 'Invoice Import Preview')

@section('content')

    <div
        style="
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            gap:16px;
            flex-wrap:wrap;
            margin-bottom:18px;
        "
    >
        <div>
            <h1 style="margin-bottom:6px;">
                Invoice Import Preview
            </h1>

            <div style="color:#667085;font-size:13px;">
                Confirm that the uploaded invoice is readable before we process its contents.
            </div>
        </div>

        <a
            href="{{ route('invoices.index') }}"
            class="btn btn-gray"
        >
            <i class="bi bi-arrow-left"></i>
            Back to Invoices
        </a>
    </div>


    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif


    <div class="row g-4">

        <div class="col-12 col-xl-8">

            <div
                class="card"
                style="
                    padding:18px;
                    min-height:520px;
                "
            >
                @if($invoiceImport->isPdf())

                    <iframe
                        src="{{ route('invoices.import.file', $invoiceImport) }}"
                        title="Uploaded invoice PDF"
                        style="
                            width:100%;
                            min-height:720px;
                            border:0;
                            border-radius:10px;
                            background:#f9fafb;
                        "
                    ></iframe>

                @elseif($invoiceImport->isImage())

                    <div
                        style="
                            display:flex;
                            align-items:center;
                            justify-content:center;
                            min-height:520px;
                            padding:12px;
                            border-radius:10px;
                            background:#f9fafb;
                        "
                    >
                        <img
                            src="{{ route('invoices.import.file', $invoiceImport) }}"
                            alt="Uploaded invoice"
                            style="
                                display:block;
                                max-width:100%;
                                max-height:760px;
                                object-fit:contain;
                                border-radius:8px;
                            "
                        >
                    </div>

                @else

                    <div class="alert alert-warning">
                        Preview is not available for this file type.
                    </div>

                @endif
            </div>

        </div>


        <div class="col-12 col-xl-4">

            <div
                class="card"
                style="
                    padding:22px;
                    margin-bottom:18px;
                "
            >
                <h3
                    style="
                        margin-bottom:18px;
                        font-size:16px;
                    "
                >
                    Uploaded File
                </h3>

                <div
                    style="
                        display:grid;
                        gap:16px;
                    "
                >
                    <div>
                        <div
                            style="
                                color:#98a2b3;
                                font-size:11px;
                                font-weight:700;
                                letter-spacing:.05em;
                                text-transform:uppercase;
                            "
                        >
                            File name
                        </div>

                        <div
                            style="
                                margin-top:5px;
                                color:#344054;
                                font-size:13px;
                                font-weight:600;
                                word-break:break-word;
                            "
                        >
                            {{ $invoiceImport->original_filename }}
                        </div>
                    </div>

                    <div>
                        <div
                            style="
                                color:#98a2b3;
                                font-size:11px;
                                font-weight:700;
                                letter-spacing:.05em;
                                text-transform:uppercase;
                            "
                        >
                            Detected type
                        </div>

                        <div
                            style="
                                margin-top:5px;
                                color:#344054;
                                font-size:13px;
                                font-weight:600;
                            "
                        >
                            {{ strtoupper($invoiceImport->file_kind) }}
                            ·
                            {{ strtoupper($invoiceImport->file_extension) }}
                        </div>
                    </div>

                    <div>
                        <div
                            style="
                                color:#98a2b3;
                                font-size:11px;
                                font-weight:700;
                                letter-spacing:.05em;
                                text-transform:uppercase;
                            "
                        >
                            Source
                        </div>

                        <div
                            style="
                                margin-top:5px;
                                color:#344054;
                                font-size:13px;
                                font-weight:600;
                            "
                        >
                            {{ $invoiceImport->source === 'camera'
                                ? 'Mobile / Device Camera'
                                : 'File Upload' }}
                        </div>
                    </div>

                    <div>
                        <div
                            style="
                                color:#98a2b3;
                                font-size:11px;
                                font-weight:700;
                                letter-spacing:.05em;
                                text-transform:uppercase;
                            "
                        >
                            Size
                        </div>

                        <div
                            style="
                                margin-top:5px;
                                color:#344054;
                                font-size:13px;
                                font-weight:600;
                            "
                        >
                            {{ number_format(
                                $invoiceImport->size_bytes / 1024 / 1024,
                                2
                            ) }}
                            MB
                        </div>
                    </div>

                    <div>
                        <div
                            style="
                                color:#98a2b3;
                                font-size:11px;
                                font-weight:700;
                                letter-spacing:.05em;
                                text-transform:uppercase;
                            "
                        >
                            Status
                        </div>

                        <div style="margin-top:5px;">
                            <span
                                style="
                                    display:inline-flex;
                                    align-items:center;
                                    gap:6px;
                                    padding:5px 9px;
                                    border-radius:999px;
                                    background:#ecfdf3;
                                    color:#027a48;
                                    font-size:12px;
                                    font-weight:700;
                                "
                            >
                                <i class="bi bi-check-circle"></i>
                                {{ ucfirst($invoiceImport->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>


            <div
                class="card"
                style="
                    padding:22px;
                    margin-bottom:18px;
                    border:1px solid #dbe8ff;
                    background:#f8fbff;
                "
            >
                <div
                    style="
                        display:flex;
                        gap:11px;
                        align-items:flex-start;
                    "
                >
                    <i
                        class="bi bi-stars"
                        style="
                            margin-top:1px;
                            color:#1570ef;
                            font-size:18px;
                        "
                    ></i>

                    <div>
                        <div
                            style="
                                color:#344054;
                                font-size:13px;
                                font-weight:700;
                            "
                        >
                            Next phase
                        </div>

                        <div
                            style="
                                margin-top:5px;
                                color:#667085;
                                font-size:12px;
                                line-height:1.6;
                            "
                        >
                            After this upload/preview foundation is tested,
                            we will read PDF/image contents and prepare the
                            customer, products and invoice for review.
                        </div>
                    </div>
                </div>
            </div>


            <div
                style="
                    display:grid;
                    gap:10px;
                "
            >
                <a
                    href="{{ route('invoices.import.create') }}"
                    class="btn btn-primary"
                >
                    <i class="bi bi-arrow-repeat"></i>
                    Upload Another Invoice
                </a>

                <form
                    action="{{ route('invoices.import.destroy', $invoiceImport) }}"
                    method="POST"
                    onsubmit="return confirm('Remove this uploaded invoice?');"
                >
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="btn btn-outline-danger w-100"
                    >
                        <i class="bi bi-trash3"></i>
                        Remove Upload
                    </button>
                </form>
            </div>

        </div>

    </div>

@endsection
