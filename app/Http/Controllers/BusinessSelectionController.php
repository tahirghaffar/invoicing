<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BusinessSelectionController extends Controller
{
    public function index(Request $request)
    {
        $businesses = $request->user()
            ->businesses()
            ->where('businesses.status', 'active')
            ->wherePivot('status', 'active')
            ->get();

        return view('business.select', compact('businesses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'business_id' => ['required', 'integer'],
        ]);

        $business = $request->user()
            ->businesses()
            ->where('businesses.id', $request->business_id)
            ->where('businesses.status', 'active')
            ->wherePivot('status', 'active')
            ->first();

        if (!$business) {
            abort(403);
        }

        session([
            'current_business_id' => $business->id,
        ]);

        return redirect()->route('dashboard');
    }
}
