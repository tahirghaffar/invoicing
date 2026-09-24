<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Invoice;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $monthlyCounts = Invoice::withTrashed()
            ->selectRaw('business_id, COUNT(*) as invoice_count')
            ->whereBetween('created_at', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ])
            ->groupBy('business_id')
            ->pluck('invoice_count', 'business_id');

        $businesses = Business::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'monthly_invoice_limit',
            ]);

        $monthlyInvoiceAlerts = $businesses
            ->map(function ($business) use ($monthlyCounts) {
                $limit =
                    (int) ($business->monthly_invoice_limit ?: 100);

                $count =
                    (int) ($monthlyCounts[$business->id] ?? 0);

                $percentage =
                    $limit > 0
                        ? ($count / $limit) * 100
                        : 0;

                return [
                    'business_id' => $business->id,
                    'business_name' => $business->name,
                    'count' => $count,
                    'limit' => $limit,
                    'percentage' => round($percentage, 1),
                    'reached' => $count >= $limit,
                ];
            })
            ->filter(
                fn ($row) =>
                    $row['percentage'] >= 90
            )
            ->values();

        return view('admin.dashboard', [
            'businessCount' => Business::count(),
            'userCount' => User::count(),
            'monthlyInvoiceAlerts' => $monthlyInvoiceAlerts,
        ]);
    }
}
