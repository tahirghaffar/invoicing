<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\FbrProvince;
use Illuminate\Http\Request;
use App\Services\AuditService;

class CustomerController extends Controller
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
        $business = app('currentBusiness');

        if (! $this->membership($request)->hasPermission('customers.view')) {
            abort(403);
        }

        $query = Customer::where(
            'business_id',
            $business->id
        );

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                $q->where(
                    'business_name',
                    'like',
                    "%{$search}%"
                )
                    ->orWhere(
                        'ntn_cnic',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'strn',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'phone',
                        'like',
                        "%{$search}%"
                    );

            });
        }

        $customers = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view(
            'customers.index',
            compact('customers')
        );
    }


    public function create(Request $request)
    {
        if (! $this->membership($request)->hasPermission('customers.create')) {
            abort(403);
        }

        $provinces = FbrProvince::where('active', true)
            ->orderBy('description')
            ->get();

        return view('customers.create', compact('provinces'));
    }


    public function store(Request $request)
    {
        if (! $this->membership($request)->hasPermission('customers.create')) {
            abort(403);
        }

        $business = app('currentBusiness');

        $validated = $this->validateCustomer($request);

        $validated['business_id'] = $business->id;

        /*
        |--------------------------------------------------------------------------
        | Unregistered buyer
        |--------------------------------------------------------------------------
        */

        if ($validated['registration_type'] === 'unregistered') {
            $validated['strn'] = null;
        }



        if (!empty($validated['province_code'])) {

            $province = \App\Models\FbrProvince::where(
                'code',
                $validated['province_code']
            )->first();

            $validated['province'] =
                $province?->description;

        }


        $customer = Customer::create($validated);

        app(AuditService::class)->log(
            'customer.created',
            $customer,
            [],
            [
                'business_name' => $customer->business_name,
                'contact_person' => $customer->contact_person,
                'registration_type' => $customer->registration_type,
                'ntn_cnic' => $customer->ntn_cnic,
                'strn' => $customer->strn,
                'province' => $customer->province,
                'city' => $customer->city,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'status' => $customer->status,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully.',
            'customer_id' => $customer->id,
        ]);
    }


    public function edit(Request $request, Customer $customer)
    {
        if (! $this->membership($request)->hasPermission('customers.update')) {
            abort(403);
        }

        $this->ensureCustomerBelongsToBusiness($customer);

        $provinces = FbrProvince::where('active', true)
            ->orderBy('description')
            ->get();

        return view(
            'customers.edit',
            compact('customer', 'provinces')
        );
    }


    public function update(Request $request, Customer $customer)
    {
        if (! $this->membership($request)->hasPermission('customers.update')) {
            abort(403);
        }

        $this->ensureCustomerBelongsToBusiness($customer);

        $validated = $this->validateCustomer($request);

        if ($validated['registration_type'] === 'unregistered') {
            $validated['strn'] = null;
        }

        if (!empty($validated['province_code'])) {

            $province = FbrProvince::where(
                'code',
                $validated['province_code']
            )->firstOrFail();

            $validated['province'] = $province->description;
        }
        $oldValues = [
            'business_name' => $customer->business_name,
            'contact_person' => $customer->contact_person,
            'registration_type' => $customer->registration_type,
            'ntn_cnic' => $customer->ntn_cnic,
            'strn' => $customer->strn,
            'province' => $customer->province,
            'city' => $customer->city,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'status' => $customer->status,
        ];
        $customer->update($validated);

        app(AuditService::class)->log(
            'customer.updated',
            $customer,
            $oldValues,
            [
                'business_name' => $customer->business_name,
                'contact_person' => $customer->contact_person,
                'registration_type' => $customer->registration_type,
                'ntn_cnic' => $customer->ntn_cnic,
                'strn' => $customer->strn,
                'province' => $customer->province,
                'city' => $customer->city,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'status' => $customer->status,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Customer updated successfully.',
        ]);
    }


    public function destroy(Request $request, Customer $customer)
    {
        if (! $this->membership($request)->hasPermission('customers.delete')) {
            abort(403);
        }

        $this->ensureCustomerBelongsToBusiness($customer);
        $deletedValues = [
            'business_name' => $customer->business_name,
            'registration_type' => $customer->registration_type,
            'ntn_cnic' => $customer->ntn_cnic,
            'strn' => $customer->strn,
            'province' => $customer->province,
            'city' => $customer->city,
            'phone' => $customer->phone,
            'email' => $customer->email,
        ];
        $customer->delete();
        app(AuditService::class)->log(
            'customer.deleted',
            $customer,
            $deletedValues
        );
        return response()->json([
            'success' => true,
            'message' => 'Customer deleted successfully.',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | AJAX search
    |--------------------------------------------------------------------------
    */

    public function search(Request $request)
    {
        if (! $this->membership($request)->hasPermission('customers.view')) {
            abort(403);
        }

        $business = app('currentBusiness');

        $search = trim($request->get('q', ''));

        $customers = Customer::where(
            'business_id',
            $business->id
        )
            ->where('status', 'active')
            ->when($search, function ($query) use ($search) {

                $query->where(function ($q) use ($search) {

                    $q->where(
                        'business_name',
                        'like',
                        "%{$search}%"
                    )
                        ->orWhere(
                            'ntn_cnic',
                            'like',
                            "%{$search}%"
                        );

                });

            })
            ->orderBy('business_name')
            ->limit(20)
            ->get([
                'id',
                'business_name',
                'registration_type',
                'ntn_cnic',
                'strn',
                'province',
                'city',
                'address',
            ]);

        return response()->json($customers);
    }


    private function validateCustomer(Request $request): array
    {
        return $request->validate([

            'business_name' => [
                'required',
                'string',
                'max:255',
            ],

            'contact_person' => [
                'nullable',
                'string',
                'max:255',
            ],

            'registration_type' => [
                'required',
                'in:registered,unregistered',
            ],

            'ntn_cnic' => [
                'nullable',
                'string',
                'max:30',
            ],

            'strn' => [
                'nullable',
                'string',
                'max:30',
            ],

            'province_code' => [
                'nullable',
                'integer',
                'exists:fbr_provinces,code',
            ],

            'city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],

        ]);
    }


    private function ensureCustomerBelongsToBusiness(
        Customer $customer
    ): void {

        $business = app('currentBusiness');

        if ($customer->business_id !== $business->id) {
            abort(404);
        }
    }
}
