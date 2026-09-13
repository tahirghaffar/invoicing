<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Product;
use App\Models\FbrSubmission;
use App\Models\AuditLog;

class DashboardController extends Controller
{
    public function index()
    {
        $businessId = session('current_business_id');

        if (!$businessId) {
            abort(403, 'No business selected.');
        }

        $totalInvoices = Invoice::where(
            'business_id',
            $businessId
        )->count();

        $productionInvoices = Invoice::where(
            'business_id',
            $businessId
        )
            ->whereNotNull('fbr_invoice_number')
            ->count();

        $draftInvoices = Invoice::where(
            'business_id',
            $businessId
        )
            ->where('status', 'draft')
            ->whereNull('fbr_invoice_number')
            ->count();

        $draftAmount = Invoice::where(
            'business_id',
            $businessId
        )
            ->where('status', 'draft')
            ->whereNull('fbr_invoice_number')
            ->sum('grand_total');


        $productionAmount = Invoice::where(
            'business_id',
            $businessId
        )
            ->whereNotNull('fbr_invoice_number')
            ->sum('grand_total');


        $productionSalesTax = Invoice::where(
            'business_id',
            $businessId
        )
            ->whereNotNull('fbr_invoice_number')
            ->sum('sales_tax');

        $thisMonthProductionAmount = Invoice::where(
            'business_id',
            $businessId
        )
            ->whereNotNull('fbr_invoice_number')
            ->whereYear(
                'fbr_submitted_at',
                now()->year
            )
            ->whereMonth(
                'fbr_submitted_at',
                now()->month
            )
            ->sum('grand_total');

        $thisMonthProductionInvoices = Invoice::where(
            'business_id',
            $businessId
        )
            ->whereNotNull('fbr_invoice_number')
            ->whereYear(
                'fbr_submitted_at',
                now()->year
            )
            ->whereMonth(
                'fbr_submitted_at',
                now()->month
            )
            ->count();

        $todayInvoices = Invoice::where(
            'business_id',
            $businessId
        )
            ->whereDate('created_at', today())
            ->count();

        $totalCustomers = Customer::where(
            'business_id',
            $businessId
        )
            ->where('status', 'active')
            ->count();


        $totalProducts = Product::where(
            'business_id',
            $businessId
        )
            ->where('status', 'active')
            ->count();

        $failedProductionSubmissions =
            FbrSubmission::where(
                'business_id',
                $businessId
            )
                ->where('environment', 'production')
                ->where(function ($query) {

                    $query
                        ->whereNull('fbr_status_code')
                        ->orWhere(
                            'fbr_status_code',
                            '!=',
                            '00'
                        );

                })
                ->count();
        $sandboxSubmissions =
            FbrSubmission::where(
                'business_id',
                $businessId
            )
                ->where('environment', 'sandbox')
                ->where('fbr_status_code', '00')
                ->count();
        $recentActivities = AuditLog::where(
            'business_id',
            $businessId
        )
            ->with('user')
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'totalInvoices',
            'productionInvoices',
            'draftInvoices',
            'draftAmount',
            'productionAmount',
            'productionSalesTax',
            'thisMonthProductionAmount',
            'thisMonthProductionInvoices',
            'todayInvoices',
            'totalCustomers',
            'totalProducts',
            'failedProductionSubmissions',
            'sandboxSubmissions',
            'recentActivities'
        ));

        //return view('dashboard');
    }
}
