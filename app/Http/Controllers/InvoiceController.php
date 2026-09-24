<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\FbrSubmission;
use App\Models\Business;

use App\Services\FBR\FbrProductionService;
use App\Services\FBR\FbrQrCodeService;
use App\Services\Invoice\InvoiceCalculator;
use App\Services\Invoice\InvoiceNumberService;
use App\Services\FBR\FbrPayloadBuilder;
use App\Services\FBR\FbrSandboxValidationService;
use App\Services\FBR\FbrSandboxPostService;
use App\Services\AuditService;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;



class InvoiceController extends Controller
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


    private function monthlyInvoiceUsage(Business $business): array
    {
        $limit = (int) ($business->monthly_invoice_limit ?: 100);

        $count = Invoice::withTrashed()
            ->where('business_id', $business->id)
            ->whereBetween('created_at', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ])
            ->count();

        return [
            'count' => $count,
            'limit' => $limit,
            'remaining' => max(0, $limit - $count),
            'reached' => $count >= $limit,
        ];
    }


    private function ensureInvoiceEditable(Invoice $invoice): void
    {
        if ($invoice->isFbrLocked()) {
            throw ValidationException::withMessages([
                'invoice' =>
                    'This invoice already has an FBR sandbox or production invoice number and can no longer be edited.',
            ]);
        }
    }


    public function index(Request $request)
    {
        if (!$this->membership($request)
            ->hasPermission('invoices.view')) {
            abort(403);
        }

        $business = app('currentBusiness');

        $invoices = Invoice::where(
            'business_id',
            $business->id
        )
            ->with([
                'latestFbrSubmission',
                'successfulSandboxSubmission',
            ])
            ->latest()
            ->paginate(20);

        $monthlyUsage =
            $this->monthlyInvoiceUsage($business);

        return view(
            'invoices.index',
            [
                'invoices' => $invoices,
                'monthlyInvoiceCount' => $monthlyUsage['count'],
                'monthlyInvoiceLimit' => $monthlyUsage['limit'],
                'monthlyInvoiceRemaining' => $monthlyUsage['remaining'],
                'monthlyInvoiceLimitReached' => $monthlyUsage['reached'],
            ]
        );
    }


    public function create(Request $request)
    {
        if (!$this->membership($request)
            ->hasPermission('invoices.create')) {
            abort(403);
        }

        $business = app('currentBusiness');

        if (
            !$business->ntn ||
            !$business->province ||
            !$business->address
        ) {
            return redirect()
                ->route('business.profile.edit')
                ->withErrors([
                    'business' =>
                        'Complete the Business Profile before creating invoices.'
                ]);
        }

        $scenarios = $business
            ->sandboxScenarios()
            ->where(
                'fbr_sandbox_scenarios.active',
                true
            )
            ->orderBy('scenario_code')
            ->get();

        $monthlyUsage =
            $this->monthlyInvoiceUsage($business);

        if ($monthlyUsage['reached']) {
            return redirect()
                ->route('invoices.index')
                ->withErrors([
                    'invoice_limit' =>
                        'Your monthly invoice limit of '
                        . $monthlyUsage['limit']
                        . ' invoices has been reached. '
                        . 'You cannot create additional invoices this month.',
                ]);
        }

        $invoice = null;

        return view(
            'invoices.form',
            [
                'invoice' => $invoice,
                'scenarios' => $scenarios,
                'monthlyInvoiceCount' => $monthlyUsage['count'],
                'monthlyInvoiceLimit' => $monthlyUsage['limit'],
                'monthlyInvoiceRemaining' => $monthlyUsage['remaining'],
            ]
        );
    }


    public function edit(
        Request $request,
        Invoice $invoice
    ) {
        if (!$this->membership($request)
            ->hasPermission('invoices.update')) {
            abort(403);
        }

        $this->ensureBelongsToBusiness($invoice);

        if ($invoice->status !== 'draft') {
            abort(403);
        }

        $this->ensureInvoiceEditable($invoice);

        $invoice->load(
            'items',
            'customer'
        );

        $business = app('currentBusiness');

        $scenarios = $business
            ->sandboxScenarios()
            ->where(
                'fbr_sandbox_scenarios.active',
                true
            )
            ->orderBy('scenario_code')
            ->get();

        $monthlyUsage =
            $this->monthlyInvoiceUsage($business);

        return view(
            'invoices.form',
            [
                'invoice' => $invoice,
                'scenarios' => $scenarios,
                'monthlyInvoiceCount' => $monthlyUsage['count'],
                'monthlyInvoiceLimit' => $monthlyUsage['limit'],
                'monthlyInvoiceRemaining' => $monthlyUsage['remaining'],
            ]
        );
    }


    public function save(
        Request $request,
        InvoiceCalculator $calculator,
        InvoiceNumberService $numberService
    ) {
        $business = app('currentBusiness');

        $validated = $request->validate([

            'invoice_id' => [
                'nullable',
                'integer',
            ],

            'invoice_date' => [
                'required',
                'date',
            ],

            'customer_id' => [
                'required',
                'integer',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_id' => [
                'nullable',
                'integer',
            ],

            'items.*.hs_code' => [
                'required',
                'string',
                'max:50',
                'exists:fbr_hs_codes,hs_code',
            ],

            'items.*.product_description' => [
                'required',
                'string',
                'max:2000',
            ],

            'items.*.transaction_type_id' => [
                'required',
                'integer',
                'exists:fbr_transaction_types,fbr_id',
            ],

            'items.*.sale_type' => [
                'required',
                'string',
                'max:255',
            ],

            'items.*.rate_id' => [
                'required',
                'integer',
            ],

            'items.*.rate_description' => [
                'required',
                'string',
                'max:100',
            ],

            'items.*.tax_rate' => [
                'required',
                'numeric',
                'min:0',
            ],

            'items.*.uom_id' => [
                'nullable',
                'integer',
            ],

            'items.*.uom' => [
                'required',
                'string',
                'max:100',
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'items.*.unit_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'items.*.discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'items.*.extra_tax' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'items.*.further_tax' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'items.*.fed_payable' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'items.*.fixed_notified_value_or_retail_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'items.*.sro_schedule_no' => [
                'nullable',
                'string',
                'max:100',
            ],

            'items.*.sro_item_serial_no' => [
                'nullable',
                'string',
                'max:100',
            ],

            'sandbox_scenario_id' => [
                'nullable',
                'integer',
            ],
        ]);

        $scenario = null;

        if (!empty(
        $validated['sandbox_scenario_id']
        )) {

            $scenario = $business
                ->sandboxScenarios()
                ->where('fbr_sandbox_scenarios.id',$validated['sandbox_scenario_id'])
                ->where('fbr_sandbox_scenarios.active',true)
                ->first();

            if (!$scenario) {

                return response()->json([
                    'success' => false,
                    'message' =>'The selected FBR scenario is not assigned to this business.',
                ], 422);
            }
        }

        $customer = Customer::where(
            'business_id',
            $business->id
        )
            ->where('id', $validated['customer_id'])
            ->where('status', 'active')
            ->firstOrFail();


        if (
            $customer->registration_type === 'registered'
            && !$customer->ntn_cnic
        ) {
            return response()->json([
                'message' =>
                    'NTN/CNIC is required for a registered buyer.'
            ], 422);
        }


        if (
            !$customer->province ||
            !$customer->address
        ) {
            return response()->json([
                'message' =>
                    'Customer province and address are required.'
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Validate product tenant ownership
        |--------------------------------------------------------------------------
        */

        foreach ($validated['items'] as $item) {

            if (!empty($item['product_id'])) {

                $validProduct = Product::where(
                    'business_id',
                    $business->id
                )
                    ->where(
                        'id',
                        $item['product_id']
                    )
                    ->exists();

                if (!$validProduct) {
                    abort(404);
                }
            }
        }


        $calculated = $calculator->calculate(
            $validated['items']
        );


        $invoice = DB::transaction(
            function () use (
                $scenario,
                $request,
                $validated,
                $calculated,
                $customer,
                $business,
                $numberService
            ) {

                if (!empty($validated['invoice_id'])) {

                    $invoice = Invoice::where(
                        'business_id',
                        $business->id
                    )
                        ->where(
                            'id',
                            $validated['invoice_id']
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($invoice->status !== 'draft') {
                        abort(403);
                    }

                    $this->ensureInvoiceEditable($invoice);

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Monthly invoice limit
                    |--------------------------------------------------------------------------
                    */

                    Business::whereKey($business->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $monthlyLimit =
                        (int) ($business->monthly_invoice_limit ?: 100);

                    $monthlyCount =
                        Invoice::withTrashed()
                            ->where('business_id', $business->id)
                            ->whereBetween('created_at', [
                                now()->startOfMonth(),
                                now()->endOfMonth(),
                            ])
                            ->count();

                    if ($monthlyCount >= $monthlyLimit) {
                        throw ValidationException::withMessages([
                            'invoice_limit' =>
                                'Your monthly invoice limit of '
                                . $monthlyLimit
                                . ' invoices has been reached. '
                                . 'You cannot create additional invoices this month.',
                        ]);
                    }

                    $number =
                        $numberService->next(
                            $business->id,
                            (int)date(
                                'Y',
                                strtotime(
                                    $validated['invoice_date']
                                )
                            )
                        );

                    $invoice = new Invoice();

                    $invoice->business_id =
                        $business->id;

                    $invoice->invoice_number =
                        $number;

                    $invoice->created_by =
                        $request->user()->id;
                }


                $invoice->customer_id =
                    $customer->id;

                $invoice->invoice_type =
                    'Sale Invoice';

                $invoice->invoice_date =
                    $validated['invoice_date'];


                /*
                |--------------------------------------------------------------------------
                | Seller Snapshot
                |--------------------------------------------------------------------------
                */

                $invoice->seller_ntn =
                    $business->ntn;

                $invoice->seller_strn =
                    $business->strn;

                $invoice->seller_business_name =
                    $business->legal_name
                        ?: $business->name;

                $invoice->seller_province =
                    $business->province;

                $invoice->seller_address =
                    $business->address;


                /*
                |--------------------------------------------------------------------------
                | Buyer Snapshot
                |--------------------------------------------------------------------------
                */

                $invoice->buyer_ntn_cnic =
                    $customer->ntn_cnic;

                $invoice->buyer_strn =
                    $customer->strn;

                $invoice->buyer_business_name =
                    $customer->business_name;

                $invoice->buyer_registration_type =
                    $customer->registration_type;

                $invoice->buyer_province =
                    $customer->province;

                $invoice->buyer_address =
                    $customer->address;


                /*
                |--------------------------------------------------------------------------
                | Totals
                |--------------------------------------------------------------------------
                */

                $invoice->subtotal =
                    $calculated['subtotal'];

                $invoice->sales_tax =
                    $calculated['sales_tax'];

                $invoice->further_tax =
                    $calculated['further_tax'];

                $invoice->extra_tax =
                    $calculated['extra_tax'];

                $invoice->fed =
                    $calculated['fed'];

                $invoice->discount =
                    $calculated['discount'];

                $invoice->grand_total =
                    $calculated['grand_total'];

                $invoice->status = 'draft';

                $invoice->updated_by =
                    $request->user()->id;


                /*
                |--------------------------------------------------------------------------
                | Selected FBR Sandbox Scenario
                |--------------------------------------------------------------------------
                */

                $invoice->sandbox_scenario_id =
                    $scenario?->id;


                /*
                |--------------------------------------------------------------------------
                | Any invoice edit invalidates previous sandbox validation
                |--------------------------------------------------------------------------
                */

                $invoice->sandbox_validation_status = null;

                $invoice->sandbox_validation_code = null;

                $invoice->sandbox_validated_at = null;


                $invoice->save();


                /*
                |--------------------------------------------------------------------------
                | Replace draft items
                |--------------------------------------------------------------------------
                */

                $invoice->items()->delete();


                foreach (
                    $calculated['items']
                    as $index => $item
                ) {

                    $invoice->items()->create([

                        'business_id' =>
                            $business->id,

                        'product_id' =>
                            $item['product_id'] ?? null,

                        'hs_code' =>
                            $item['hs_code'],

                        'product_description' =>
                            $item['product_description'],

                        'rate_id' =>
                            $item['rate_id'],

                        'rate_description' =>
                            $item['rate_description'],

                        'tax_rate' =>
                            $item['tax_rate'],

                        'uom_id' =>
                            $item['uom_id'] ?? null,

                        'uom' =>
                            $item['uom'],

                        'quantity' =>
                            $item['quantity'],

                        'unit_price' =>
                            $item['unit_price'],

                        'total_value' =>
                            $item['total_value'],

                        'value_sales_excluding_st' =>
                            $item['value_sales_excluding_st'],

                        'fixed_notified_value_or_retail_price' =>
                            $item['fixed_notified_value_or_retail_price']
                            ?? 0,

                        'sales_tax_applicable' =>
                            $item['sales_tax_applicable'],

                        'sales_tax_withheld_at_source' =>
                            0,

                        'extra_tax' =>
                            $item['extra_tax'] ?? 0,

                        'further_tax' =>
                            $item['further_tax'] ?? 0,

                        'fed_payable' =>
                            $item['fed_payable'] ?? 0,

                        'discount' =>
                            $item['discount'] ?? 0,

                        'transaction_type_id' =>
                            $item['transaction_type_id'],

                        'sale_type' =>
                            $item['sale_type'],

                        'sro_schedule_no' =>
                            $item['sro_schedule_no'] ?? null,

                        'sro_item_serial_no' =>
                            $item['sro_item_serial_no'] ?? null,

                        'line_total' =>
                            $item['line_total'],

                        'sort_order' =>
                            $index,

                    ]);
                }

                return $invoice;
            }
        );


        return response()->json([

            'success' => true,

            'message' =>
                'Invoice draft saved successfully.',

            'invoice_id' =>
                $invoice->id,

            'invoice_number' =>
                $invoice->invoice_number,

            'totals' => [
                'subtotal' =>
                    $invoice->subtotal,

                'sales_tax' =>
                    $invoice->sales_tax,

                'grand_total' =>
                    $invoice->grand_total,
            ],

            'edit_url' =>
                route(
                    'invoices.edit',
                    $invoice
                ),
        ]);
    }


    private function ensureBelongsToBusiness(
        Invoice $invoice
    ): void {

        $business = app('currentBusiness');

        if ($invoice->business_id !== $business->id) {
            abort(404);
        }
    }


    /**
     * Resolve the FBR invoice number that should be displayed.
     *
     * Production number always has priority. If there is no production
     * number, the latest successful sandbox number is used for testing.
     */
    private function resolveFbrDisplayData(
        Invoice $invoice,
        FbrQrCodeService $qrService
    ): array {
        $displayFbrInvoiceNumber =
            $invoice->fbr_invoice_number;

        $isSandbox = false;

        if (!$displayFbrInvoiceNumber) {

            $sandboxSubmission = $invoice
                ->fbrSubmissions()
                ->where('environment', 'sandbox')
                ->where('fbr_status_code', '00')
                ->whereNotNull('fbr_invoice_number')
                ->latest('submitted_at')
                ->first();

            if ($sandboxSubmission) {

                $displayFbrInvoiceNumber =
                    $sandboxSubmission->fbr_invoice_number;

                $isSandbox = true;
            }
        }

        $qrCode = null;

        if ($displayFbrInvoiceNumber) {

            $qrCode = $qrService->generate(
                $displayFbrInvoiceNumber
            );
        }

        return [
            'displayFbrInvoiceNumber' =>
                $displayFbrInvoiceNumber,

            'isSandbox' =>
                $isSandbox,

            'qrCode' =>
                $qrCode,
        ];
    }

//    public function preview(
//        Request $request,
//        Invoice $invoice
//    ) {
//        if (! $this->membership($request)
//            ->hasPermission('invoices.view')) {
//            abort(403);
//        }
//
//        $this->ensureBelongsToBusiness($invoice);
//
//        $invoice->load([
//            'items',
//            'business',
//        ]);
//
//        return view(
//            'invoices.preview',
//            compact('invoice')
//        );
//    }
//

    public function preview(
        Request $request,
        Invoice $invoice,
        FbrQrCodeService $qrService
    ) {
        if (! $this->membership($request)
            ->hasPermission('invoices.view')) {
            abort(403);
        }

        $this->ensureBelongsToBusiness($invoice);

        $invoice->load([
            'items',
            'business',
            'sandboxScenario',
        ]);

        $fbrDisplay =
            $this->resolveFbrDisplayData(
                $invoice,
                $qrService
            );

        $displayFbrInvoiceNumber =
            $fbrDisplay['displayFbrInvoiceNumber'];

        $isSandbox =
            $fbrDisplay['isSandbox'];

        $qrCode =
            $fbrDisplay['qrCode'];

        return view(
            'invoices.preview',
            compact(
                'invoice',
                'qrCode',
                'displayFbrInvoiceNumber',
                'isSandbox'
            )
        );
    }


    public function printView(
        Request $request,
        Invoice $invoice,
        FbrQrCodeService $qrService
    ) {
        if (! $this->membership($request)
            ->hasPermission('invoices.view')) {
            abort(403);
        }

        $this->ensureBelongsToBusiness($invoice);

        $invoice->load([
            'items',
            'business',
            'sandboxScenario',
        ]);

        $fbrDisplay =
            $this->resolveFbrDisplayData(
                $invoice,
                $qrService
            );

        $displayFbrInvoiceNumber =
            $fbrDisplay['displayFbrInvoiceNumber'];

        $isSandbox =
            $fbrDisplay['isSandbox'];

        $qrCode =
            $fbrDisplay['qrCode'];

        return view(
            'invoices.print',
            compact(
                'invoice',
                'qrCode',
                'displayFbrInvoiceNumber',
                'isSandbox'
            )
        );
    }


    public function pdf(
        Request $request,
        Invoice $invoice,
        FbrQrCodeService $qrService
    ) {
        if (! $this->membership($request)
            ->hasPermission('invoices.view')) {
            abort(403);
        }

        $this->ensureBelongsToBusiness($invoice);

        $invoice->load([
            'items',
            'business',
            'sandboxScenario',
        ]);

        $fbrDisplay =
            $this->resolveFbrDisplayData(
                $invoice,
                $qrService
            );

        $displayFbrInvoiceNumber =
            $fbrDisplay['displayFbrInvoiceNumber'];

        $isSandbox =
            $fbrDisplay['isSandbox'];

        $qrCode =
            $fbrDisplay['qrCode'];

        $pdf = Pdf::loadView(
            'invoices.pdf',
            compact(
                'invoice',
                'qrCode',
                'displayFbrInvoiceNumber',
                'isSandbox'
            )
        );

        $pdf->setPaper(
            'a4',
            'portrait'
        );

        return $pdf->download(
            $invoice->invoice_number . '.pdf'
        );
    }


    public function fbrJson(
        Request $request,
        Invoice $invoice,
        FbrPayloadBuilder $builder
    ) {
        if (!$this->membership($request)
            ->hasPermission('invoices.view')) {
            abort(403);
        }

        $this->ensureBelongsToBusiness($invoice);

        $business = app('currentBusiness');

        $allowedScenarios = $business
            ->sandboxScenarios()
            ->where('fbr_sandbox_scenarios.active', true)
            ->orderBy('scenario_code')
            ->get();

        $allowedScenarioCodes = $allowedScenarios
            ->pluck('scenario_code')
            ->toArray();

        $scenarioId =
            $request->get('scenario_id');

        if (
            $scenarioId &&
            !in_array(
                $scenarioId,
                $allowedScenarioCodes,
                true
            )
        ) {
            abort(422);
        }

        $payload = $builder->build(
            $invoice,
            $scenarioId
        );

        return view(
            'invoices.fbr-json',
            compact(
                'invoice',
                'payload',
                'scenarioId',
                'allowedScenarios'
            )
        );
    }

    public function validateSandbox(
        Request $request,
        Invoice $invoice,
        FbrPayloadBuilder $builder,
        FbrSandboxValidationService $validator
    ) {
        if (!$this->membership($request)
            ->hasPermission('invoices.submit_fbr')) {
            abort(403);
        }

        $this->ensureBelongsToBusiness($invoice);

        $business = app('currentBusiness');

        $allowedCodes = $business
            ->sandboxScenarios()
            ->where('fbr_sandbox_scenarios.active', true)
            ->pluck('scenario_code')
            ->toArray();

        $validated = $request->validate([

            'scenario_id' => [
                'required',
                Rule::in($allowedCodes),
            ],

        ]);

        try {

            $payload = $builder->build(
                $invoice,
                $validated['scenario_id']
            );

            $result = $validator->validate(
                $payload
            );

            return response()->json([
                'success' => true,
                'payload' => $payload,
                'http_status' =>
                    $result['http_status'],
                'fbr_response' =>
                    $result['response'],
            ]);

        } catch (\Throwable $e) {

            report($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function postSandbox(
        Request $request,
        Invoice $invoice,
        FbrPayloadBuilder $builder,
        FbrSandboxValidationService $validator,
        FbrSandboxPostService $poster
    ) {
        if (!$this->membership($request)
            ->hasPermission('invoices.submit_fbr')) {
            abort(403);
        }

        $this->ensureBelongsToBusiness($invoice);

        $business = app('currentBusiness');

        /*
        |--------------------------------------------------------------------------
        | Use the scenario already saved on the invoice
        |--------------------------------------------------------------------------
        */

        $invoice->load('sandboxScenario');

        $scenario = $invoice->sandboxScenario;

        if (!$scenario) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Please select and save an FBR Sandbox Scenario first.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Ensure this scenario is assigned to the current business
        |--------------------------------------------------------------------------
        */

        $allowed = $business
            ->sandboxScenarios()
            ->where(
                'fbr_sandbox_scenarios.id',
                $scenario->id
            )
            ->where(
                'fbr_sandbox_scenarios.active',
                true
            )
            ->exists();

        if (!$allowed) {

            return response()->json([
                'success' => false,
                'message' =>
                    'This FBR scenario is not assigned to this business.',
            ], 422);
        }


        $scenarioCode =
            $scenario->scenario_code;

        $payload = $builder->build(
            $invoice,
            $scenarioCode
        );


        /*
        |--------------------------------------------------------------------------
        | Validate again immediately before posting
        |--------------------------------------------------------------------------
        */

        $validation =
            $validator->validate($payload);

        $validationResponse =
            $validation['response']['validationResponse']
            ?? [];

        if (
            ($validationResponse['statusCode'] ?? null)
            !== '00'
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    $validationResponse['error']
                    ?? 'Invoice failed sandbox validation.',
                'fbr_response' =>
                    $validation['response'],
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Create submission log BEFORE calling FBR
        |--------------------------------------------------------------------------
        */

        $submission = FbrSubmission::create([
            'business_id' =>
                $invoice->business_id,

            'invoice_id' =>
                $invoice->id,

            'request_uuid' =>
                (string) Str::uuid(),

            'environment' =>
                'sandbox',

            'operation' =>
                'post',

            'scenario_id' =>
                $scenarioCode,

            'endpoint' =>
                config('fbr.sandbox_post_url'),

            'request_payload' =>
                $payload,

            'created_by' =>
                $request->user()->id,
        ]);


        try {

            $result =
                $poster->post($payload);

            $response =
                $result['response'];

            $fbrValidation =
                $response['validationResponse']
                ?? [];

            $submission->update([
                'response_payload' =>
                    $response,

                'http_status' =>
                    $result['http_status'],

                'fbr_status_code' =>
                    $fbrValidation['statusCode']
                    ?? null,

                'fbr_status' =>
                    $fbrValidation['status']
                    ?? null,

                'fbr_invoice_number' =>
                    $response['invoiceNumber']
                    ?? null,

                'error_message' =>
                    $fbrValidation['error']
                    ?? null,

                'submitted_at' =>
                    now(),
            ]);


            $success =
                ($fbrValidation['statusCode'] ?? null)
                    === '00'
                &&
                !empty(
                    $response['invoiceNumber']
                );


            if ($success) {

                $business
                    ->sandboxScenarios()
                    ->updateExistingPivot(
                        $scenario->id,
                        [
                            'status' =>
                                'completed',

                            'completed_at' =>
                                now(),
                        ]
                    );
            }


            return response()->json([
                'success' =>
                    $success,

                'message' =>
                    $success
                        ? 'Invoice submitted successfully to FBR Sandbox.'
                        : 'FBR Sandbox rejected the invoice.',

                'fbr_invoice_number' =>
                    $response['invoiceNumber']
                    ?? null,

                'scenario' =>
                    $scenarioCode,

                'fbr_response' =>
                    $response,

                'submission_id' =>
                    $submission->id,
            ]);

        } catch (\Throwable $e) {

            $submission->update([
                'error_message' =>
                    $e->getMessage(),

                'submitted_at' =>
                    now(),
            ]);

            report($e);

            return response()->json([
                'success' => false,
                'message' =>
                    'Sandbox submission failed: '
                    . $e->getMessage(),
            ], 500);
        }
    }


    public function submissions(
        Request $request,
        Invoice $invoice
    ) {
        if (!$this->membership($request)
            ->hasPermission('invoices.view')) {
            abort(403);
        }

        $this->ensureBelongsToBusiness($invoice);

        $submissions = $invoice
            ->fbrSubmissions()
            ->latest()
            ->get();

        return view(
            'invoices.submissions',
            compact('invoice', 'submissions')
        );
    }

    public function submitProduction(
        Request $request,
        Invoice $invoice,
        FbrPayloadBuilder $builder,
        FbrProductionService $production
    ) {
        if (!$this->membership($request)
            ->hasPermission('invoices.submit_fbr')) {
            abort(403);
        }

        $this->ensureBelongsToBusiness($invoice);

        /*
        |--------------------------------------------------------------------------
        | Never submit same invoice twice
        |--------------------------------------------------------------------------
        */

        if ($invoice->fbr_invoice_number) {

            return response()->json([
                'success' => false,
                'message' =>
                    'This invoice has already been submitted to FBR.',
                'fbr_invoice_number' =>
                    $invoice->fbr_invoice_number,
            ], 409);
        }


        /*
        |--------------------------------------------------------------------------
        | Production payload
        |--------------------------------------------------------------------------
        */

        $payload = $builder->build($invoice, null);

        // Extra protection:
        unset($payload['scenarioId']);


        /*
        |--------------------------------------------------------------------------
        | Production validation FIRST
        |--------------------------------------------------------------------------
        */

        $validation = $production->validate($payload);

        $validationResponse =
            $validation['response']['validationResponse']
            ?? [];

        if (
            ($validationResponse['statusCode'] ?? null) !== '00'
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'FBR Production validation failed.',
                'fbr_response' =>
                    $validation['response'],
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Create audit record BEFORE POST
        |--------------------------------------------------------------------------
        */

        $submission = FbrSubmission::create([
            'business_id' => $invoice->business_id,
            'invoice_id' => $invoice->id,
            'request_uuid' => (string) Str::uuid(),

            'environment' => 'production',
            'operation' => 'post',

            'scenario_id' => null,

            'endpoint' =>
                config('fbr.production_url'),

            'request_payload' => $payload,

            'created_by' =>
                $request->user()->id,
        ]);


        try {

            $result = $production->post($payload);

            $response = $result['response'];

            $fbrValidation =
                $response['validationResponse']
                ?? [];

            $success =
                ($fbrValidation['statusCode'] ?? null) === '00'
                &&
                !empty($response['invoiceNumber']);


            $submission->update([
                'response_payload' =>
                    $response,

                'http_status' =>
                    $result['http_status'],

                'fbr_status_code' =>
                    $fbrValidation['statusCode'] ?? null,

                'fbr_status' =>
                    $fbrValidation['status'] ?? null,

                'fbr_invoice_number' =>
                    $response['invoiceNumber'] ?? null,

                'error_message' =>
                    $fbrValidation['error'] ?? null,

                'submitted_at' => now(),
            ]);


            /*
            |--------------------------------------------------------------------------
            | Save official FBR number on invoice
            |--------------------------------------------------------------------------
            */

            if ($success) {

                $invoice->update([
                    'fbr_status' =>
                        $fbrValidation['status']
                        ?? 'Valid',

                    'fbr_invoice_number' =>
                        $response['invoiceNumber'],

                    'fbr_submitted_at' => now(),
                ]);
            }


            return response()->json([
                'success' => $success,

                'message' => $success
                    ? 'REAL FBR invoice created successfully.'
                    : 'FBR rejected the production invoice.',

                'fbr_invoice_number' =>
                    $response['invoiceNumber'] ?? null,

                'fbr_response' => $response,
            ]);

        } catch (\Throwable $e) {

            $submission->update([
                'error_message' =>
                    $e->getMessage(),

                'submitted_at' =>
                    now(),
            ]);

            report($e);

            return response()->json([
                'success' => false,
                'message' =>
                    'Production submission failed: '
                    . $e->getMessage(),
            ], 500);
        }
    }

    public function validateFbr(
        Request $request,
        Invoice $invoice,
        FbrPayloadBuilder $builder,
        FbrSandboxValidationService $validator
    ) {
        if (! $this->membership($request)
            ->hasPermission(
                'invoices.submit_fbr'
            )) {

            abort(403);
        }


        $this->ensureBelongsToBusiness(
            $invoice
        );


        /*
        |--------------------------------------------------------------------------
        | Scenario
        |--------------------------------------------------------------------------
        */

        $scenario = $invoice
            ->sandboxScenario;

        if (!$scenario) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Please select an FBR Sandbox Scenario first.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Ensure scenario belongs to this business
        |--------------------------------------------------------------------------
        */

        $business = app('currentBusiness');

        $allowed = $business
            ->sandboxScenarios()
            ->where(
                'fbr_sandbox_scenarios.id',
                $scenario->id
            )
            ->where(
                'fbr_sandbox_scenarios.active',
                true
            )
            ->exists();

        if (!$allowed) {

            return response()->json([
                'success' => false,
                'message' =>
                    'This FBR scenario is not assigned to this business.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Build JSON internally
        |--------------------------------------------------------------------------
        */

        $payload = $builder->build(
            $invoice,
            $scenario->scenario_code
        );


        /*
        |--------------------------------------------------------------------------
        | Validate with FBR
        |--------------------------------------------------------------------------
        */

        $result = $validator->validate(
            $payload
        );


        $validationResponse =
            $result['response']
            ['validationResponse']
            ?? [];


        $statusCode =
            $validationResponse[
            'statusCode'
            ]
            ?? null;

        $status =
            $validationResponse[
            'status'
            ]
            ?? null;

        $error =
            $validationResponse['error']
            ?? null;

        $itemErrors = [];

        foreach (
            $validationResponse['invoiceStatuses'] ?? []
            as $itemStatus
        ) {
            if (!empty($itemStatus['error'])) {
                $itemErrors[] =
                    $itemStatus['error'];
            }
        }

        $errorMessage = $error;

        if (!empty($itemErrors)) {
            $errorMessage =
                implode('<br>', array_unique($itemErrors));
        }


        /*
        |--------------------------------------------------------------------------
        | Save latest validation result
        |--------------------------------------------------------------------------
        */

        $invoice->update([

            'sandbox_validation_code' =>
                $statusCode,

            'sandbox_validation_status' =>
                $status,

            'sandbox_validated_at' =>
                now(),

        ]);


        /*
        |--------------------------------------------------------------------------
        | Audit
        |--------------------------------------------------------------------------
        */

        app(
            \App\Services\AuditService::class
        )->log(

            'invoice.fbr_validated',

            $invoice,

            [],

            [],

            [
                'environment' => 'sandbox',

                'scenario_id' =>
                    $scenario->scenario_code,

                'http_status' =>
                    $result[
                    'http_status'
                    ]
                    ?? null,

                'fbr_status_code' =>
                    $statusCode,

                'fbr_status' =>
                    $status,
            ]
        );


        $success =
            $statusCode === '00';


        return response()->json([

            'success' =>
                $success,

            'message' =>
                $success
                    ? 'Invoice validated successfully with FBR.'
                    : (
                $errorMessage
                    ?: 'FBR validation failed.'
                ),

            'status' =>
                $status,

            'status_code' =>
                $statusCode,

            'scenario' =>
                $scenario
                    ->scenario_code,

        ], $success ? 200 : 422);
    }

}
