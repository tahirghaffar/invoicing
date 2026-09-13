<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class SystemMaintenanceController extends Controller
{
    public function migrate(Request $request)
    {
        // Protect this endpoint
        if ($request->query('key') !== config('app.maintenance_key')) {
            abort(403, 'Unauthorized.');
        }

        try {
            $exitCode = Artisan::call('migrate', [
                '--force' => true,
            ]);

            $output = Artisan::output();

            return response()->json([
                'success' => $exitCode === 0,
                'exit_code' => $exitCode,
                'message' => 'Migration command executed.',
                'output' => $output,
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Migration failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
