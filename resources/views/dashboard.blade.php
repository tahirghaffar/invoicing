@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    @php

        $currentBusiness = app()->bound('currentBusiness')
            ? app('currentBusiness')
            : null;

        $productionRate = $totalInvoices > 0
            ? round(($productionInvoices / $totalInvoices) * 100)
            : 0;

        $draftRate = $totalInvoices > 0
            ? round(($draftInvoices / $totalInvoices) * 100)
            : 0;

    @endphp


    <style>

        /* ============================================================
           Dashboard
        ============================================================ */

        .dashboard-page {
            padding-bottom: 30px;
        }

        .dashboard-heading {
            font-size: 28px;
            font-weight: 800;
            color: #191c24;
            letter-spacing: -0.7px;
            margin-bottom: 4px;
        }

        .dashboard-subtitle {
            color: #8a92a6;
            font-size: 14px;
            margin-bottom: 0;
        }


        /* ============================================================
           Main KPI Cards
        ============================================================ */

        .stat-card {
            position: relative;
            background: #fff;
            border: 1px solid #edf0f5;
            border-radius: 18px;
            padding: 22px;
            height: 100%;
            overflow: hidden;
            transition:
                transform .2s ease,
                box-shadow .2s ease,
                border-color .2s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            border-color: transparent;
            box-shadow: 0 14px 35px rgba(33, 43, 54, .08);
        }

        .stat-card::after {
            content: '';
            position: absolute;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            right: -28px;
            top: -30px;
            background: rgba(72, 94, 255, .05);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .stat-icon svg {
            width: 23px;
            height: 23px;
        }

        .icon-purple {
            background: #f0efff;
            color: #6c5ce7;
        }

        .icon-green {
            background: #eafaf3;
            color: #18a66a;
        }

        .icon-blue {
            background: #edf5ff;
            color: #3c7df0;
        }

        .icon-orange {
            background: #fff4e6;
            color: #f39c3d;
        }

        .stat-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .7px;
            font-weight: 700;
            color: #9ba3b5;
            margin-bottom: 7px;
        }

        .stat-value {
            font-size: 26px;
            font-weight: 800;
            color: #20232d;
            line-height: 1.2;
        }

        .stat-description {
            margin-top: 9px;
            font-size: 12px;
            color: #9ba3b5;
        }


        /* ============================================================
           Panels
        ============================================================ */

        .dashboard-panel {
            background: #fff;
            border: 1px solid #edf0f5;
            border-radius: 18px;
            padding: 22px;
            height: 100%;
        }

        .panel-title {
            font-size: 17px;
            font-weight: 750;
            color: #252836;
            margin-bottom: 3px;
        }

        .panel-subtitle {
            font-size: 12px;
            color: #9ba3b5;
            margin-bottom: 20px;
        }


        /* ============================================================
           Small stats
        ============================================================ */

        .mini-stat {
            padding: 15px 0;
            border-bottom: 1px solid #f0f2f5;
        }

        .mini-stat:last-child {
            border-bottom: none;
        }

        .mini-label {
            color: #8992a5;
            font-size: 13px;
        }

        .mini-value {
            font-size: 17px;
            font-weight: 750;
            color: #292c35;
        }


        /* ============================================================
           Production progress
        ============================================================ */

        .custom-progress {
            height: 8px;
            border-radius: 20px;
            background: #edf0f5;
            overflow: hidden;
        }

        .custom-progress-bar {
            height: 100%;
            border-radius: 20px;
            background: linear-gradient(
                90deg,
                #6c5ce7,
                #7f73f1
            );
        }


        /* ============================================================
           Status cards
        ============================================================ */

        .status-box {
            border-radius: 15px;
            padding: 16px;
            height: 100%;
        }

        .status-box.success {
            background: #edfbf5;
        }

        .status-box.warning {
            background: #fff8eb;
        }

        .status-box.danger {
            background: #fff0f1;
        }

        .status-box.info {
            background: #eef6ff;
        }

        .status-number {
            font-size: 23px;
            font-weight: 800;
            color: #252836;
        }

        .status-label {
            font-size: 12px;
            font-weight: 600;
            color: #7c8598;
            margin-top: 3px;
        }


        /* ============================================================
           Recent activity
        ============================================================ */

        .activity-item {
            display: flex;
            gap: 13px;
            padding: 13px 0;
            border-bottom: 1px solid #f0f2f5;
        }

        .activity-item:last-child {
            border-bottom: 0;
        }

        .activity-avatar {
            width: 39px;
            height: 39px;
            min-width: 39px;
            border-radius: 12px;
            background: #f0efff;
            color: #6c5ce7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .activity-text {
            font-size: 13px;
            color: #404453;
            line-height: 1.5;
        }

        .activity-time {
            color: #a1a8b8;
            font-size: 11px;
            margin-top: 3px;
        }

        .activity-badge {
            display: inline-block;
            margin-top: 4px;
            padding: 3px 8px;
            font-size: 10px;
            font-weight: 700;
            border-radius: 20px;
            background: #f3f2ff;
            color: #6c5ce7;
        }


        /* ============================================================
           Amount formatting
        ============================================================ */

        .currency-prefix {
            font-size: 12px;
            font-weight: 600;
            color: #858da0;
            margin-right: 3px;
        }


        @media (max-width: 767px) {

            .dashboard-heading {
                font-size: 23px;
            }

            .stat-value {
                font-size: 22px;
            }

        }

    </style>



    <div class="dashboard-page">

        {{-- ============================================================
             HEADER
        ============================================================ --}}

        <div class="d-flex flex-column flex-lg-row
                justify-content-between align-items-lg-center
                mb-4">

            <div>

                <div class="dashboard-heading">
                    Dashboard
                </div>

                <p class="dashboard-subtitle">

                    @if($currentBusiness)

                        {{ $currentBusiness->name }} ·

                    @endif

                    Financial & FBR Digital Invoicing Overview

                </p>

            </div>


            <div class="mt-3 mt-lg-0">

                <a
                    href="{{ route('invoices.create') }}"
                    class="btn btn-primary px-4 py-2 rounded-3"
                >
                    + Create Invoice
                </a>

            </div>

        </div>



        {{-- ============================================================
             MAIN KPI CARDS
        ============================================================ --}}

        <div class="row g-4 mb-4">


            {{-- Total invoices --}}

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon icon-purple">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M9 12h6m-6 4h6M9 8h6M5 3h10l4 4v14H5z"
                            />
                        </svg>

                    </div>

                    <div class="stat-label">
                        Total Invoices
                    </div>

                    <div class="stat-value">
                        {{ number_format($totalInvoices) }}
                    </div>

                    <div class="stat-description">
                        All invoices created by this business
                    </div>

                </div>

            </div>



            {{-- Production invoices --}}

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon icon-green">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M5 12l4 4L19 6"
                            />
                        </svg>

                    </div>

                    <div class="stat-label">
                        Production Invoices
                    </div>

                    <div class="stat-value">
                        {{ number_format($productionInvoices) }}
                    </div>

                    <div class="stat-description">
                        Successfully issued through FBR
                    </div>

                </div>

            </div>



            {{-- Production amount --}}

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon icon-blue">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M12 6v12m4-9.5C16 7.1 14.2 6 12 6S8 7.1 8 8.5 9.8 11 12 11s4 1.1 4 2.5S14.2 16 12 16s-4-1.1-4-2.5"
                            />
                        </svg>

                    </div>

                    <div class="stat-label">
                        Production Amount
                    </div>

                    <div class="stat-value">

                    <span class="currency-prefix">
                        Rs.
                    </span>

                        {{ number_format($productionAmount, 2) }}

                    </div>

                    <div class="stat-description">
                        Total value submitted to FBR
                    </div>

                </div>

            </div>



            {{-- Draft amount --}}

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon icon-orange">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M4 20h4l10-10-4-4L4 16v4zm8-12 4 4"
                            />
                        </svg>

                    </div>

                    <div class="stat-label">
                        Draft Amount
                    </div>

                    <div class="stat-value">

                    <span class="currency-prefix">
                        Rs.
                    </span>

                        {{ number_format($draftAmount, 2) }}

                    </div>

                    <div class="stat-description">
                        Value still pending production submission
                    </div>

                </div>

            </div>

        </div>



        {{-- ============================================================
             SECOND ROW
        ============================================================ --}}

        <div class="row g-4 mb-4">


            {{-- Invoice status --}}

            <div class="col-xl-7">

                <div class="dashboard-panel">

                    <div class="panel-title">
                        Invoice Status Overview
                    </div>

                    <div class="panel-subtitle">
                        Production versus draft invoice activity
                    </div>


                    <div class="row g-3 mb-4">

                        <div class="col-md-6">

                            <div class="status-box success">

                                <div class="status-number">
                                    {{ number_format($productionInvoices) }}
                                </div>

                                <div class="status-label">
                                    Production Invoices
                                </div>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="status-box warning">

                                <div class="status-number">
                                    {{ number_format($draftInvoices) }}
                                </div>

                                <div class="status-label">
                                    Draft Invoices
                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="d-flex justify-content-between mb-2">

                    <span class="small text-muted">
                        Production completion
                    </span>

                        <strong class="small">
                            {{ $productionRate }}%
                        </strong>

                    </div>


                    <div class="custom-progress mb-4">

                        <div
                            class="custom-progress-bar"
                            style="width: {{ min($productionRate, 100) }}%;"
                        ></div>

                    </div>


                    <div class="row">

                        <div class="col-md-6">

                            <div class="mini-stat">

                                <div class="mini-label">
                                    Production Sales Tax
                                </div>

                                <div class="mini-value">

                                    Rs.
                                    {{ number_format(
                                        $productionSalesTax,
                                        2
                                    ) }}

                                </div>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="mini-stat">

                                <div class="mini-label">
                                    This Month Production
                                </div>

                                <div class="mini-value">

                                    Rs.
                                    {{ number_format(
                                        $thisMonthProductionAmount,
                                        2
                                    ) }}

                                </div>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="mini-stat">

                                <div class="mini-label">
                                    Production Invoices This Month
                                </div>

                                <div class="mini-value">
                                    {{ number_format(
                                        $thisMonthProductionInvoices
                                    ) }}
                                </div>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="mini-stat">

                                <div class="mini-label">
                                    Invoices Created Today
                                </div>

                                <div class="mini-value">
                                    {{ number_format($todayInvoices) }}
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            {{-- FBR status --}}

            <div class="col-xl-5">

                <div class="dashboard-panel">

                    <div class="panel-title">
                        FBR Integration
                    </div>

                    <div class="panel-subtitle">
                        Submission and testing status
                    </div>


                    <div class="row g-3">

                        <div class="col-6">

                            <div class="status-box success">

                                <div class="status-number">
                                    {{ number_format(
                                        $productionInvoices
                                    ) }}
                                </div>

                                <div class="status-label">
                                    Production Valid
                                </div>

                            </div>

                        </div>


                        <div class="col-6">

                            <div class="status-box info">

                                <div class="status-number">
                                    {{ number_format(
                                        $sandboxSubmissions
                                    ) }}
                                </div>

                                <div class="status-label">
                                    Sandbox Valid
                                </div>

                            </div>

                        </div>


                        <div class="col-6">

                            <div class="status-box danger">

                                <div class="status-number">
                                    {{ number_format(
                                        $failedProductionSubmissions
                                    ) }}
                                </div>

                                <div class="status-label">
                                    Failed Production Attempts
                                </div>

                            </div>

                        </div>


                        <div class="col-6">

                            <div class="status-box warning">

                                <div class="status-number">
                                    {{ number_format(
                                        $draftInvoices
                                    ) }}
                                </div>

                                <div class="status-label">
                                    Pending Drafts
                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="mt-4 pt-2">

                        <div class="d-flex justify-content-between mb-2">

                        <span class="mini-label">
                            Draft ratio
                        </span>

                            <strong>
                                {{ $draftRate }}%
                            </strong>

                        </div>

                        <div class="custom-progress">

                            <div
                                class="custom-progress-bar"
                                style="
                                width:
                                {{ min($draftRate, 100) }}%;
                            "
                            ></div>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        {{-- ============================================================
             CUSTOMERS / PRODUCTS + RECENT ACTIVITY
        ============================================================ --}}

        <div class="row g-4">


            <div class="col-xl-4">

                <div class="dashboard-panel">

                    <div class="panel-title">
                        Business Overview
                    </div>

                    <div class="panel-subtitle">
                        Master data and daily activity
                    </div>


                    <div class="mini-stat
                            d-flex justify-content-between
                            align-items-center">

                        <div class="mini-label">
                            Active Customers
                        </div>

                        <div class="mini-value">
                            {{ number_format($totalCustomers) }}
                        </div>

                    </div>


                    <div class="mini-stat
                            d-flex justify-content-between
                            align-items-center">

                        <div class="mini-label">
                            Products / Services
                        </div>

                        <div class="mini-value">
                            {{ number_format($totalProducts) }}
                        </div>

                    </div>


                    <div class="mini-stat
                            d-flex justify-content-between
                            align-items-center">

                        <div class="mini-label">
                            Draft Invoices
                        </div>

                        <div class="mini-value">
                            {{ number_format($draftInvoices) }}
                        </div>

                    </div>


                    <div class="mini-stat
                            d-flex justify-content-between
                            align-items-center">

                        <div class="mini-label">
                            Today's Invoices
                        </div>

                        <div class="mini-value">
                            {{ number_format($todayInvoices) }}
                        </div>

                    </div>


                    <div class="mt-4">

                        <a
                            href="{{ route('customers.index') }}"
                            class="btn btn-light
                               rounded-3 w-100 mb-2"
                        >
                            View Customers
                        </a>

                        <a
                            href="{{ route('products.index') }}"
                            class="btn btn-light
                               rounded-3 w-100"
                        >
                            View Products
                        </a>

                    </div>

                </div>

            </div>



            {{-- ========================================================
                 RECENT ACTIVITY
            ======================================================== --}}

            <div class="col-xl-8">

                <div class="dashboard-panel">

                    <div class="d-flex
                            justify-content-between
                            align-items-start">

                        <div>

                            <div class="panel-title">
                                Recent Activity
                            </div>

                            <div class="panel-subtitle">
                                Latest actions performed by business users
                            </div>

                        </div>

                    </div>


                    @forelse($recentActivities as $activity)

                        @php

                            $actor =
                                $activity->user?->name
                                ?? 'System';

                            $initials = collect(
                                explode(' ', $actor)
                            )
                            ->filter()
                            ->take(2)
                            ->map(
                                fn($word) =>
                                    strtoupper(
                                        mb_substr($word, 0, 1)
                                    )
                            )
                            ->implode('');


                            $actionLabels = [

                                'auth.login'
                                    => 'logged into the system',

                                'auth.logout'
                                    => 'logged out',

                                'business.selected'
                                    => 'accessed this business',

                                'invoice.created'
                                    => 'created an invoice',

                                'invoice.updated'
                                    => 'updated an invoice',

                                'invoice.autosaved'
                                    => 'autosaved an invoice',

                                'invoice.deleted'
                                    => 'deleted an invoice',

                                'product.created'
                                    => 'created a product',

                                'product.updated'
                                    => 'updated a product',

                                'product.deleted'
                                    => 'deleted a product',

                                'customer.created'
                                    => 'created a customer',

                                'customer.updated'
                                    => 'updated a customer',

                                'customer.deleted'
                                    => 'deleted a customer',

                                'invoice.fbr_validated'
                                    => 'validated an invoice with FBR',

                                'invoice.fbr_sandbox_submitted'
                                    => 'submitted an invoice to FBR Sandbox',

                                'invoice.fbr_production_submitted'
                                    => 'submitted an invoice to FBR Production',

                            ];


                            $activityLabel =
                                $actionLabels[
                                    $activity->action
                                ]
                                ??
                                str_replace(
                                    '.',
                                    ' ',
                                    $activity->action
                                );


                            $reference =
                                data_get(
                                    $activity->new_values,
                                    'invoice_number'
                                )
                                ??
                                data_get(
                                    $activity->new_values,
                                    'name'
                                )
                                ??
                                data_get(
                                    $activity->new_values,
                                    'business_name'
                                )
                                ??
                                data_get(
                                    $activity->metadata,
                                    'fbr_invoice_number'
                                );

                        @endphp


                        <div class="activity-item">

                            <div class="activity-avatar">
                                {{ $initials ?: 'S' }}
                            </div>


                            <div class="flex-grow-1">

                                <div class="activity-text">

                                    <strong>
                                        {{ $actor }}
                                    </strong>

                                    {{ $activityLabel }}

                                    @if($reference)

                                        <strong>
                                            {{ $reference }}
                                        </strong>

                                    @endif

                                </div>


                                <div class="activity-time">

                                    {{ $activity
                                        ->created_at
                                        ->diffForHumans()
                                    }}

                                    ·

                                    {{ $activity
                                        ->created_at
                                        ->format('d M Y, h:i A')
                                    }}

                                </div>


                                <span class="activity-badge">

                                {{ strtoupper(
                                    str_replace(
                                        '.',
                                        ' / ',
                                        $activity->action
                                    )
                                ) }}

                            </span>

                            </div>

                        </div>


                    @empty

                        <div
                            class="text-center py-5 text-muted"
                        >

                            No recent activity available.

                        </div>

                    @endforelse

                </div>

            </div>

        </div>

    </div>

@endsection
