<?php

namespace App\Http\Controllers;

use App\Models\InvoiceImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvoiceImportController extends Controller
{
    private function membership(Request $request)
    {
        $business = app('currentBusiness');

        return $request->user()
            ->businessMemberships()
            ->where('business_id', $business->id)
            ->where('status', 'active')
            ->firstOrFail();
    }

    public function create(Request $request)
    {
        if (!$this->membership($request)
            ->hasPermission('invoices.create')) {
            abort(403);
        }

        return view('invoices.import');
    }

    public function store(Request $request)
    {
        if (!$this->membership($request)
            ->hasPermission('invoices.create')) {
            abort(403);
        }

        $request->validate([
            'document_file' => [
                'nullable',
                'file',
                'max:15360',
                'mimes:pdf,jpg,jpeg,png',
            ],
            'camera_image' => [
                'nullable',
                'file',
                'max:15360',
                'mimes:jpg,jpeg,png',
            ],
        ], [
            'document_file.max' =>
                'The uploaded invoice may not be larger than 15 MB.',
            'camera_image.max' =>
                'The captured invoice image may not be larger than 15 MB.',
        ]);

        $hasDocument =
            $request->hasFile('document_file');

        $hasCameraImage =
            $request->hasFile('camera_image');

        if (!$hasDocument && !$hasCameraImage) {
            throw ValidationException::withMessages([
                'document_file' =>
                    'Please upload a PDF/image or capture an invoice photo.',
            ]);
        }

        if ($hasDocument && $hasCameraImage) {
            throw ValidationException::withMessages([
                'document_file' =>
                    'Please select only one invoice file at a time.',
            ]);
        }

        $file = $hasCameraImage
            ? $request->file('camera_image')
            : $request->file('document_file');

        $source = $hasCameraImage
            ? 'camera'
            : 'upload';

        /*
        |--------------------------------------------------------------------------
        | Detect the real file type
        |--------------------------------------------------------------------------
        |
        | Do not trust only the browser-provided extension. Laravel's "mimes"
        | validation above already checks the MIME type; we also whitelist the
        | detected MIME here before deciding whether this is a PDF or image.
        |
        */

        $mimeType = strtolower(
            (string) $file->getMimeType()
        );

        $allowedMimeTypes = [
            'application/pdf' => [
                'kind' => 'pdf',
                'extension' => 'pdf',
            ],
            'image/jpeg' => [
                'kind' => 'image',
                'extension' => 'jpg',
            ],
            'image/png' => [
                'kind' => 'image',
                'extension' => 'png',
            ],
        ];

        if (!isset($allowedMimeTypes[$mimeType])) {
            throw ValidationException::withMessages([
                $hasCameraImage
                    ? 'camera_image'
                    : 'document_file' =>
                    'Unsupported invoice file type. Please use PDF, JPG/JPEG, or PNG.',
            ]);
        }

        $business = app('currentBusiness');

        $fileType =
            $allowedMimeTypes[$mimeType];

        $storedFilename =
            (string) Str::uuid()
            . '.'
            . $fileType['extension'];

        $folder =
            'invoice-imports/'
            . $business->id
            . '/'
            . now()->format('Y/m');

        $path = $file->storeAs(
            $folder,
            $storedFilename,
            'local'
        );

        if (!$path) {
            throw ValidationException::withMessages([
                'document_file' =>
                    'The invoice file could not be stored. Please try again.',
            ]);
        }

        $invoiceImport = InvoiceImport::create([
            'business_id' =>
                $business->id,

            'uploaded_by' =>
                $request->user()->id,

            'original_filename' =>
                Str::limit(
                    basename(
                        (string) $file->getClientOriginalName()
                    ),
                    255,
                    ''
                ),

            'stored_filename' =>
                $storedFilename,

            'storage_disk' =>
                'local',

            'storage_path' =>
                $path,

            'mime_type' =>
                $mimeType,

            'file_extension' =>
                $fileType['extension'],

            'file_kind' =>
                $fileType['kind'],

            'source' =>
                $source,

            'size_bytes' =>
                (int) $file->getSize(),

            'status' =>
                'uploaded',
        ]);

        return redirect()
            ->route(
                'invoices.import.preview',
                $invoiceImport
            )
            ->with(
                'success',
                'Invoice file uploaded successfully.'
            );
    }

    public function preview(
        Request $request,
        InvoiceImport $invoiceImport
    ) {
        if (!$this->membership($request)
            ->hasPermission('invoices.create')) {
            abort(403);
        }

        $this->ensureBelongsToBusiness(
            $invoiceImport
        );

        return view(
            'invoices.import-preview',
            [
                'invoiceImport' =>
                    $invoiceImport,
            ]
        );
    }

    public function file(
        Request $request,
        InvoiceImport $invoiceImport
    ) {
        if (!$this->membership($request)
            ->hasPermission('invoices.create')) {
            abort(403);
        }

        $this->ensureBelongsToBusiness(
            $invoiceImport
        );

        $disk = Storage::disk(
            $invoiceImport->storage_disk
        );

        if (!$disk->exists(
            $invoiceImport->storage_path
        )) {
            abort(404);
        }

        return response()->file(
            $disk->path(
                $invoiceImport->storage_path
            ),
            [
                'Content-Type' =>
                    $invoiceImport->mime_type,

                'Content-Disposition' =>
                    'inline',

                'X-Content-Type-Options' =>
                    'nosniff',
            ]
        );
    }

    public function destroy(
        Request $request,
        InvoiceImport $invoiceImport
    ) {
        if (!$this->membership($request)
            ->hasPermission('invoices.create')) {
            abort(403);
        }

        $this->ensureBelongsToBusiness(
            $invoiceImport
        );

        $disk = Storage::disk(
            $invoiceImport->storage_disk
        );

        if ($disk->exists(
            $invoiceImport->storage_path
        )) {
            $disk->delete(
                $invoiceImport->storage_path
            );
        }

        $invoiceImport->delete();

        return redirect()
            ->route('invoices.import.create')
            ->with(
                'success',
                'Uploaded invoice was removed.'
            );
    }

    private function ensureBelongsToBusiness(
        InvoiceImport $invoiceImport
    ): void {
        $business = app('currentBusiness');

        if (
            (int) $invoiceImport->business_id
            !== (int) $business->id
        ) {
            abort(404);
        }
    }
}
