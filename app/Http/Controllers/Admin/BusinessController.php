<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BusinessController extends Controller
{
    public function index()
    {
        $businesses = Business::with('users')->latest()->paginate(20);

        return view('admin.businesses.index', compact('businesses'));
    }

    public function create()
    {
        return view('admin.businesses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],

            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:8'],
        ]);

        DB::transaction(function () use ($validated) {

            $business = Business::create([
                'name' => $validated['name'],
                'legal_name' => $validated['legal_name'] ?? null,

                'slug' => Str::slug($validated['name'])
                    . '-' . Str::lower(Str::random(6)),

                'timezone' => 'Asia/Karachi',
                'currency' => 'PKR',
                'status' => 'active',
            ]);

            $user = User::create([
                'name' => $validated['admin_name'],
                'email' => $validated['admin_email'],
                'password' => $validated['admin_password'],
                'status' => 'active',
            ]);

            $membership = BusinessUser::create([
                'business_id' => $business->id,
                'user_id' => $user->id,
                'status' => 'active',
                'joined_at' => now(),
            ]);

            $role = Role::where('slug', 'business-admin')
                ->where('scope', 'business')
                ->firstOrFail();

            $membership->roles()->attach($role->id);
        });

        return redirect()
            ->route('admin.businesses.index')
            ->with('success', 'Business created successfully.');
    }
}
