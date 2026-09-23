<?php

namespace App\Http\Middleware;

use App\Models\Business;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Defensive auth check
        |--------------------------------------------------------------------------
        |
        | Routes using this middleware are already inside Laravel's "auth"
        | middleware, so this is only a safety net.
        |
        */

        if (! $user) {
            return redirect()->route('login');
        }

        $businessId =
            session('current_business_id');

        /*
        |--------------------------------------------------------------------------
        | Restore business context after a remembered login
        |--------------------------------------------------------------------------
        |
        | Laravel's Remember Me cookie can restore the authenticated user after
        | the normal session expires, but current_business_id is session-only.
        | Therefore we restore the last business from a separate encrypted
        | cookie, after confirming that the user still has an active membership.
        |
        */

        if (! $businessId) {

            if ($user->hasSystemRole('super-admin')) {
                return redirect()
                    ->route('admin.dashboard');
            }

            $rememberedBusinessId =
                $request->cookie(
                    'last_business_id'
                );

            if ($rememberedBusinessId) {

                $rememberedMembership =
                    $user->businessMemberships()
                        ->where(
                            'business_id',
                            $rememberedBusinessId
                        )
                        ->where(
                            'status',
                            'active'
                        )
                        ->whereHas(
                            'business',
                            function ($query) {
                                $query->where(
                                    'status',
                                    'active'
                                );
                            }
                        )
                        ->first();

                if ($rememberedMembership) {

                    $businessId =
                        (int) $rememberedBusinessId;

                    session([
                        'current_business_id' =>
                            $businessId,
                    ]);

                } else {

                    /*
                    | The cookie is stale or belongs to a business the user can
                    | no longer access.
                    */

                    Cookie::queue(
                        Cookie::forget(
                            'last_business_id'
                        )
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | If no remembered business exists, auto-select the only active one
            |--------------------------------------------------------------------------
            */

            if (! $businessId) {

                $activeMemberships =
                    $user->businessMemberships()
                        ->where(
                            'status',
                            'active'
                        )
                        ->whereHas(
                            'business',
                            function ($query) {
                                $query->where(
                                    'status',
                                    'active'
                                );
                            }
                        )
                        ->get();

                if (
                    $activeMemberships->count()
                    === 1
                ) {

                    $businessId =
                        $activeMemberships
                            ->first()
                            ->business_id;

                    session([
                        'current_business_id' =>
                            $businessId,
                    ]);

                } else {

                    return redirect()
                        ->route(
                            'business.select'
                        );
                }
            }
        }

        $membership = $user->businessMemberships()
            ->where('business_id', $businessId)
            ->where('status', 'active')
            ->first();

        if (!$membership) {
            session()->forget('current_business_id');

            return redirect()->route('business.select');
        }

        $business = Business::where('id', $businessId)
            ->where('status', 'active')
            ->first();

        if (!$business) {
            session()->forget('current_business_id');

            return redirect()->route('business.select');
        }

        app()->instance('currentBusiness', $business);

        View::share('currentBusiness', $business);

        return $next($request);
    }
}
