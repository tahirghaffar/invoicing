<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {

            $table->foreignId('sandbox_scenario_id')
                ->nullable()
                ->after('invoice_date')
                ->constrained('fbr_sandbox_scenarios')
                ->nullOnDelete();

            $table->string(
                'sandbox_validation_status',
                50
            )
                ->nullable()
                ->after('sandbox_scenario_id');

            $table->string(
                'sandbox_validation_code',
                20
            )
                ->nullable()
                ->after('sandbox_validation_status');

            $table->timestamp(
                'sandbox_validated_at'
            )
                ->nullable()
                ->after('sandbox_validation_code');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {

            $table->dropForeign([
                'sandbox_scenario_id'
            ]);

            $table->dropColumn([
                'sandbox_scenario_id',
                'sandbox_validation_status',
                'sandbox_validation_code',
                'sandbox_validated_at',
            ]);
        });
    }
};
