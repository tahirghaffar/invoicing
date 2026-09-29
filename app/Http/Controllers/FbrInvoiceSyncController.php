<?php

namespace App\Http\Controllers;

use App\Models\FbrInvoiceSync;
use App\Models\Invoice;
use App\Services\AuditService;
use App\Services\FBR\FbrInvoiceSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class FbrInvoiceSyncController extends Controller
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


    private function ensureBelongsToBusiness(
        Invoice $invoice
    ): void {
        $business = app('currentBusiness');

        if ($invoice->business_id !== $business->id) {
            abort(404);
        }
    }


    public function sync(
        Request $request,
        Invoice $invoice,
        FbrInvoiceSyncService $syncService,
        AuditService $auditService
    ) {
        if (!$this->membership($request)
            ->hasPermission('invoices.submit_fbr')) {
            abort(403);
        }

        $this->ensureBelongsToBusiness($invoice);

        if (!$invoice->fbr_invoice_number) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Only production invoices with an FBR invoice number can be synchronized.',
            ], 422);
        }

        $invoice->load('items');

        $sync = FbrInvoiceSync::create([
            'business_id' => $invoice->business_id,
            'invoice_id' => $invoice->id,
            'fbr_invoice_number' =>
                $invoice->fbr_invoice_number,
            'sync_status' => 'pending',
            'old_snapshot' =>
                $syncService->snapshot($invoice),
            'created_by' => $request->user()->id,
        ]);

        try {
            $result = $syncService->fetch($invoice);

            $sync->update([
                'method' => $result['method'],
                'endpoint' => $result['endpoint'],
                'request_payload' =>
                    $result['request_payload'],
                'response_payload' =>
                    $result['response'],
                'http_status' =>
                    $result['http_status'],
            ]);

            if (!$result['successful']) {
                $message =
                    'FBR invoice-details request failed with HTTP status '
                    . $result['http_status']
                    . '.';

                $this->recordFailure(
                    $invoice,
                    $sync,
                    $message
                );

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'fbr_response' =>
                        $result['response'],
                ], 502);
            }

            $remote = $syncService->normalize(
                $result['response'],
                $invoice->fbr_invoice_number
            );

            $applied = DB::transaction(function () use (
                $invoice,
                $remote,
                $syncService,
                $sync,
                $auditService
            ) {
                $applied = $syncService->apply(
                    $invoice,
                    $remote
                );

                $differenceCount = count(
                    $applied['differences']
                );

                $updatedInvoice = $applied['invoice'];

                $updatedInvoice->update([
                    'fbr_last_synced_at' => now(),
                    'fbr_sync_status' => 'success',
                    'fbr_remote_status' =>
                        $remote['remote_status']
                        ?: $updatedInvoice->fbr_remote_status,
                    'fbr_sync_message' =>
                        $differenceCount > 0
                            ? $differenceCount
                                . ' field change(s) synchronized from FBR.'
                            : 'FBR invoice is already synchronized.',
                ]);

                $sync->update([
                    'sync_status' => 'success',
                    'remote_status' =>
                        $remote['remote_status'],
                    'old_snapshot' =>
                        $applied['old_snapshot'],
                    'new_snapshot' =>
                        $applied['new_snapshot'],
                    'differences' =>
                        $applied['differences'],
                    'synced_at' => now(),
                ]);

                $auditService->log(
                    'invoice.fbr_sync',
                    $updatedInvoice,
                    $applied['old_snapshot'],
                    $applied['new_snapshot'],
                    [
                        'fbr_invoice_number' =>
                            $updatedInvoice->fbr_invoice_number,
                        'difference_count' =>
                            $differenceCount,
                        'remote_status' =>
                            $remote['remote_status'],
                        'sync_id' => $sync->id,
                    ],
                    $updatedInvoice->business_id
                );

                return $applied;
            });

            $differenceCount = count(
                $applied['differences']
            );

            return response()->json([
                'success' => true,
                'changed' => $differenceCount > 0,
                'difference_count' =>
                    $differenceCount,
                'remote_status' =>
                    $remote['remote_status'],
                'message' => $differenceCount > 0
                    ? 'Invoice synchronized from FBR. '
                        . $differenceCount
                        . ' field change(s) were applied locally.'
                    : 'Invoice checked successfully. Local values already match FBR.',
            ]);

        } catch (Throwable $e) {
            report($e);

            $this->recordFailure(
                $invoice,
                $sync,
                $e->getMessage()
            );

            return response()->json([
                'success' => false,
                'message' =>
                    'FBR synchronization failed: '
                    . $e->getMessage(),
            ], 500);
        }
    }


    public function history(
        Request $request,
        Invoice $invoice
    ) {
        if (!$this->membership($request)
            ->hasPermission('invoices.view')) {
            abort(403);
        }

        $this->ensureBelongsToBusiness($invoice);

        $syncs = $invoice
            ->fbrInvoiceSyncs()
            ->with('user')
            ->latest('id')
            ->paginate(20);

        return view(
            'invoices.fbr-sync-history',
            compact(
                'invoice',
                'syncs'
            )
        );
    }


    private function recordFailure(
        Invoice $invoice,
        FbrInvoiceSync $sync,
        string $message
    ): void {
        $sync->update([
            'sync_status' => 'failed',
            'error_message' => $message,
            'synced_at' => now(),
        ]);

        $invoice->update([
            'fbr_sync_status' => 'failed',
            'fbr_sync_message' => $message,
        ]);
    }
}
