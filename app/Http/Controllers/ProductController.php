<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\FbrTransactionType;
use App\Models\FbrUom;
use App\Services\FBR\FbrReferenceService;

class ProductController extends Controller
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


    public function index(Request $request)
    {
        if (! $this->membership($request)->hasPermission('products.view')) {
            abort(403);
        }

        $business = app('currentBusiness');

        $query = Product::where(
            'business_id',
            $business->id
        );

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('hs_code', 'like', "%{$search}%");

            });
        }

        $products = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view(
            'products.index',
            compact('products')
        );
    }


    public function create(Request $request)
    {
        if (
            ! $this->membership($request)
                ->hasPermission('products.create')
        ) {
            abort(403);
        }

        $transactionTypes =
            FbrTransactionType::where('active', true)
                ->orderBy('description')
                ->get();

        $uoms =
            FbrUom::where('active', true)
                ->orderBy('description')
                ->get();

        return view(
            'products.create',
            compact(
                'transactionTypes',
                'uoms'
            )
        );
    }


    public function store(
        Request $request,
        FbrReferenceService $referenceService
    ) {
        if (! $this->membership($request)->hasPermission('products.create')) {
            abort(403);
        }

        $business = app('currentBusiness');

        $validated = $this->validateProduct(
            $request,
            $business->id
        );

        $validated['business_id'] = $business->id;

        if (!empty($validated['uom_id'])) {

            $uom = FbrUom::where(
                'fbr_id',
                $validated['uom_id']
            )->firstOrFail();

            $validated['uom'] =
                $uom->description;

        }

        $transactionType =
            FbrTransactionType::where(
                'fbr_id',
                $validated['transaction_type_id']
            )->firstOrFail();


        $validated['sale_type'] =
            $transactionType->description;

        if (
            !empty($validated['rate_id']) &&
            $business->province_code
        ) {

            $rates = $referenceService->rates(
                $validated['transaction_type_id'],
                $business->province_code,
                now()->format('Y-m-d')
            );


            $selectedRate =
                collect($rates)->firstWhere(
                    'ratE_ID',
                    (int)$validated['rate_id']
                );


            if (!$selectedRate) {

                return response()->json([
                    'message' =>
                        'The selected FBR tax rate is invalid.'
                ], 422);

            }


            $validated['tax_rate'] =
                $selectedRate['ratE_VALUE'];

            $validated['tax_rate_description'] =
                $selectedRate['ratE_DESC'];

        }

        $product = Product::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product / Service created successfully.',
            'product_id' => $product->id,
        ]);
    }


    public function edit(Request $request,Product $product)
    {
        if (! $this->membership($request)->hasPermission('products.update')) {
            abort(403);
        }

        $this->ensureBelongsToBusiness($product);

        $transactionTypes =
            FbrTransactionType::where('active', true)
                ->orderBy('description')
                ->get();

        $uoms =
            FbrUom::where('active', true)
                ->orderBy('description')
                ->get();

        return view(
            'products.edit',
            compact('product', 'transactionTypes', 'uoms')
        );
    }


    public function update(
        Request $request,
        Product $product,
        FbrReferenceService $referenceService
    ) {
        if (! $this->membership($request)->hasPermission('products.update')) {
            abort(403);
        }

        $this->ensureBelongsToBusiness($product);

        $business = app('currentBusiness');

        $validated = $this->validateProduct(
            $request,
            $business->id,
            $product->id
        );

        if (!empty($validated['uom_id'])) {

            $uom = FbrUom::where(
                'fbr_id',
                $validated['uom_id']
            )->firstOrFail();

            $validated['uom'] =
                $uom->description;

        }

        $transactionType =
            FbrTransactionType::where(
                'fbr_id',
                $validated['transaction_type_id']
            )->firstOrFail();


        $validated['sale_type'] =
            $transactionType->description;

        if (
            !empty($validated['rate_id']) &&
            $business->province_code
        ) {

            $rates = $referenceService->rates(
                $validated['transaction_type_id'],
                $business->province_code,
                now()->format('Y-m-d')
            );


            $selectedRate =
                collect($rates)->firstWhere(
                    'ratE_ID',
                    (int)$validated['rate_id']
                );


            if (!$selectedRate) {

                return response()->json([
                    'message' =>
                        'The selected FBR tax rate is invalid.'
                ], 422);

            }


            $validated['tax_rate'] =
                $selectedRate['ratE_VALUE'];

            $validated['tax_rate_description'] =
                $selectedRate['ratE_DESC'];

        }

        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product / Service updated successfully.',
        ]);
    }


    public function destroy(
        Request $request,
        Product $product
    ) {
        if (! $this->membership($request)->hasPermission('products.delete')) {
            abort(403);
        }

        $this->ensureBelongsToBusiness($product);

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product / Service deleted successfully.',
        ]);
    }


    public function search(Request $request)
    {
        if (! $this->membership($request)->hasPermission('products.view')) {
            abort(403);
        }

        $business = app('currentBusiness');

        $search = trim(
            $request->get('q', '')
        );

        $products = Product::where(
            'business_id',
            $business->id
        )
            ->where('status', 'active')
            ->when($search, function ($query) use ($search) {

                $query->where(function ($q) use ($search) {

                    $q->where(
                        'name',
                        'like',
                        "%{$search}%"
                    )
                        ->orWhere(
                            'sku',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'hs_code',
                            'like',
                            "%{$search}%"
                        );

                });

            })
            ->orderBy('name')
            ->limit(20)
            ->get([
                'id',
                'type',
                'sku',
                'name',
                'description',
                'hs_code',
                'uom',
                'transaction_type_id',
                'rate_id',
                'sale_type',
                'tax_rate',
                'unit_price',
                'sro_schedule_no',
                'sro_item_serial_no',
                'fixed_notified_value_or_retail_price',
            ]);

        return response()->json($products);
    }


    private function validateProduct(
        Request $request,
        int $businessId,
        ?int $productId = null
    ): array {

        return $request->validate([

            'type' => [
                'required',
                'in:product,service',
            ],

            'sku' => [
                'nullable',
                'string',
                'max:100',

                Rule::unique('products', 'sku')
                    ->where(function ($query) use ($businessId) {
                        return $query->where(
                            'business_id',
                            $businessId
                        );
                    })
                    ->ignore($productId),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'hs_code' => [
                'nullable',
                'string',
                'max:50',
            ],

            'uom_id' => [
                'nullable',
                'integer',
                'exists:fbr_uoms,fbr_id',
            ],

            'transaction_type_id' => [
                'required',
                'integer',
                'exists:fbr_transaction_types,fbr_id',
            ],

            'rate_id' => [
                'nullable',
                'integer',
            ],

            'unit_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'sro_schedule_no' => [
                'nullable',
                'string',
                'max:100',
            ],

            'sro_item_serial_no' => [
                'nullable',
                'string',
                'max:100',
            ],

            'fixed_notified_value_or_retail_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],

        ]);
    }


    private function ensureBelongsToBusiness(
        Product $product
    ): void {

        $business = app('currentBusiness');

        if ($product->business_id !== $business->id) {
            abort(404);
        }
    }
}
