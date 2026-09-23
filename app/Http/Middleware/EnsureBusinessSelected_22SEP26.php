<?php

namespace App\Http\Middleware;

use App\Models\Business;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $businessId = session('current_business_id');

        if (!$businessId) {
            return redirect()->route('business.select');
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
