<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AuditService;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'status' => 'active',
        ], $remember)) {

            return back()
                ->withErrors([
                    'email' => 'Invalid email or password.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        app(AuditService::class)->log(
            'auth.login',
            $request->user(),
            [],
            [],
            [
                'email' => $request->user()->email,
            ]
        );


        $user = $request->user();

        $user->update([
            'last_login_at' => now(),
        ]);

        // Platform Super Admin
        if ($user->hasSystemRole('super-admin')) {
            return redirect()->route('admin.dashboard');
        }

        $memberships = $user->businessMemberships()
            ->where('status', 'active')
            ->whereHas('business', function ($query) {
                $query->where('status', 'active');
            })
            ->get();

        if ($memberships->count() === 1) {
            session([
                'current_business_id' => $memberships->first()->business_id,
            ]);
            app(AuditService::class)->log(
                'auth.login',
                $request->user(),
                [],
                [],
                [
                    'email' => $request->user()->email,
                ],
                $memberships->first()->business_id
            );
            return redirect()->route('dashboard');
        }

        if ($memberships->count() > 1) {
            return redirect()->route('business.select');
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->withErrors([
                'email' => 'No active business is assigned to this account.',
            ]);
    }

    public function logout(Request $request)
    {
        app(AuditService::class)->log(
            'auth.logout',
            $request->user()
        );

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
