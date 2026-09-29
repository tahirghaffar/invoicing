@extends('layouts.app')

@section('title', 'Import Invoice')

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
                Import Invoice
            </h1>

            <div style="color:#667085;font-size:13px;">
                Upload an existing PDF/image invoice or capture a photo from your mobile camera.
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


    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Unable to upload invoice.</strong>

            <ul style="margin:8px 0 0;">
                @foreach($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif


    <form
        id="invoice-import-form"
        action="{{ route('invoices.import.store') }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @csrf

        <div class="row g-4">

            <div class="col-12 col-lg-6">

                <div
                    class="card h-100"
                    style="
                        padding:24px;
                        border:1px solid #e6e8f0;
                    "
                >
                    <div
                        style="
                            width:52px;
                            height:52px;
                            display:flex;
                            align-items:center;
                            justify-content:center;
                            margin-bottom:18px;
                            border-radius:15px;
                            background:#f0edff;
                            color:#6c5ce7;
                            font-size:23px;
                        "
                    >
                        <i class="bi bi-camera"></i>
                    </div>

                    <h3 style="font-size:17px;margin-bottom:8px;">
                        Take Photo
                    </h3>

                    <p
                        style="
                            min-height:42px;
                            margin-bottom:18px;
                            color:#667085;
                            font-size:13px;
                            line-height:1.6;
                        "
                    >
                        On a phone or tablet, open the rear camera and capture a clear photo of the invoice.
                    </p>

                    <label for="camera_image">
                        Capture invoice image
                    </label>

                    <input
                        type="file"
                        name="camera_image"
                        id="camera_image"
                        class="form-control"
                        accept="image/jpeg,image/png"
                        capture="environment"
                    >

                    <div
                        style="
                            color:#98a2b3;
                            font-size:12px;
                            line-height:1.5;
                        "
                    >
                        JPG/JPEG or PNG, maximum 15 MB.
                    </div>
                </div>

            </div>


            <div class="col-12 col-lg-6">

                <div
                    class="card h-100"
                    style="
                        padding:24px;
                        border:1px solid #e6e8f0;
                    "
                >
                    <div
                        style="
                            width:52px;
                            height:52px;
                            display:flex;
                            align-items:center;
                            justify-content:center;
                            margin-bottom:18px;
                            border-radius:15px;
                            background:#eef7ff;
                            color:#1570ef;
                            font-size:23px;
                        "
                    >
                        <i class="bi bi-file-earmark-arrow-up"></i>
                    </div>

                    <h3 style="font-size:17px;margin-bottom:8px;">
                        Upload PDF or Image
                    </h3>

                    <p
                        style="
                            min-height:42px;
                            margin-bottom:18px;
                            color:#667085;
                            font-size:13px;
                            line-height:1.6;
                        "
                    >
                        Choose an existing PDF invoice or invoice image from your device.
                    </p>

                    <label for="document_file">
                        Select invoice file
                    </label>

                    <input
                        type="file"
                        name="document_file"
                        id="document_file"
                        class="form-control"
                        accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                    >

                    <div
                        style="
                            color:#98a2b3;
                            font-size:12px;
                            line-height:1.5;
                        "
                    >
                        PDF, JPG/JPEG or PNG, maximum 15 MB.
                    </div>
                </div>

            </div>

        </div>


        <div
            id="selected-file-card"
            class="card"
            style="
                display:none;
                margin-top:24px;
                padding:20px;
            "
        >
            <div
                style="
                    display:flex;
                    align-items:center;
                    gap:14px;
                "
            >
                <div
                    id="selected-file-icon"
                    style="
                        width:46px;
                        height:46px;
                        flex:0 0 46px;
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        border-radius:12px;
                        background:#f4f3ff;
                        color:#6c5ce7;
                        font-size:20px;
                    "
                >
                    <i class="bi bi-file-earmark-check"></i>
                </div>

                <div style="min-width:0;flex:1;">
                    <div
                        id="selected-file-name"
                        style="
                            overflow:hidden;
                            color:#344054;
                            font-size:14px;
                            font-weight:700;
                            text-overflow:ellipsis;
                            white-space:nowrap;
                        "
                    ></div>

                    <div
                        id="selected-file-meta"
                        style="
                            margin-top:4px;
                            color:#98a2b3;
                            font-size:12px;
                        "
                    ></div>
                </div>
            </div>

            <div
                id="image-preview-wrap"
                style="
                    display:none;
                    margin-top:18px;
                    padding:12px;
                    border:1px solid #eaecf0;
                    border-radius:12px;
                    background:#f9fafb;
                    text-align:center;
                "
            >
                <img
                    id="image-preview"
                    src=""
                    alt="Selected invoice preview"
                    style="
                        max-width:100%;
                        max-height:420px;
                        border-radius:8px;
                    "
                >
            </div>
        </div>


        <div
            class="card"
            style="
                margin-top:24px;
                padding:18px 20px;
                background:#fcfcfd;
            "
        >
            <div
                style="
                    display:flex;
                    align-items:center;
                    gap:10px;
                    color:#475467;
                    font-size:13px;
                    line-height:1.6;
                "
            >
                <i
                    class="bi bi-shield-check"
                    style="
                        color:#18a875;
                        font-size:18px;
                    "
                ></i>

                <span>
                    The uploaded file is stored privately under the current business.
                    It is not submitted to FBR at this stage.
                </span>
            </div>
        </div>


        <div
            style="
                display:flex;
                justify-content:flex-end;
                gap:10px;
                margin-top:22px;
            "
        >
            <a
                href="{{ route('invoices.index') }}"
                class="btn btn-gray"
            >
                Cancel
            </a>

            <button
                type="submit"
                id="upload-button"
                class="btn btn-primary"
                disabled
            >
                <i class="bi bi-cloud-arrow-up"></i>
                Upload & Preview
            </button>
        </div>

    </form>


    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const cameraInput =
                document.getElementById('camera_image');

            const documentInput =
                document.getElementById('document_file');

            const selectedCard =
                document.getElementById('selected-file-card');

            const selectedName =
                document.getElementById('selected-file-name');

            const selectedMeta =
                document.getElementById('selected-file-meta');

            const selectedIcon =
                document.getElementById('selected-file-icon');

            const imagePreviewWrap =
                document.getElementById('image-preview-wrap');

            const imagePreview =
                document.getElementById('image-preview');

            const uploadButton =
                document.getElementById('upload-button');


            function humanFileSize(bytes) {

                if (!bytes) {
                    return '0 KB';
                }

                const units =
                    ['B', 'KB', 'MB', 'GB'];

                const index =
                    Math.min(
                        Math.floor(
                            Math.log(bytes)
                            / Math.log(1024)
                        ),
                        units.length - 1
                    );

                return (
                    (bytes / Math.pow(1024, index))
                        .toFixed(index === 0 ? 0 : 1)
                    + ' '
                    + units[index]
                );
            }


            function resetPreview() {

                selectedCard.style.display =
                    'none';

                selectedName.textContent =
                    '';

                selectedMeta.textContent =
                    '';

                imagePreviewWrap.style.display =
                    'none';

                imagePreview.src =
                    '';

                uploadButton.disabled =
                    true;
            }


            function showSelectedFile(file) {

                if (!file) {
                    resetPreview();
                    return;
                }

                selectedCard.style.display =
                    'block';

                selectedName.textContent =
                    file.name;

                selectedMeta.textContent =
                    (file.type || 'File')
                    + ' • '
                    + humanFileSize(file.size);

                uploadButton.disabled =
                    false;

                const isImage =
                    file.type.startsWith('image/');

                const isPdf =
                    file.type === 'application/pdf'
                    || file.name
                        .toLowerCase()
                        .endsWith('.pdf');

                selectedIcon.innerHTML =
                    isPdf
                        ? '<i class="bi bi-file-earmark-pdf"></i>'
                        : '<i class="bi bi-file-earmark-image"></i>';

                if (isImage) {

                    const reader =
                        new FileReader();

                    reader.onload =
                        function (event) {

                            imagePreview.src =
                                event.target.result;

                            imagePreviewWrap
                                .style
                                .display =
                                'block';
                        };

                    reader.readAsDataURL(file);

                } else {

                    imagePreviewWrap.style.display =
                        'none';

                    imagePreview.src =
                        '';
                }
            }


            cameraInput.addEventListener(
                'change',
                function () {

                    if (
                        cameraInput.files
                        && cameraInput.files.length
                    ) {

                        documentInput.value =
                            '';

                        showSelectedFile(
                            cameraInput.files[0]
                        );

                    } else if (
                        !documentInput.files.length
                    ) {

                        resetPreview();
                    }
                }
            );


            documentInput.addEventListener(
                'change',
                function () {

                    if (
                        documentInput.files
                        && documentInput.files.length
                    ) {

                        cameraInput.value =
                            '';

                        showSelectedFile(
                            documentInput.files[0]
                        );

                    } else if (
                        !cameraInput.files.length
                    ) {

                        resetPreview();
                    }
                }
            );

        });
    </script>

@endsection
