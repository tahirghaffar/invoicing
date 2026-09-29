<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->timestamp('fbr_last_synced_at')->nullable();
            $table->string('fbr_sync_status', 40)->nullable();
            $table->string('fbr_remote_status', 100)->nullable();
            $table->text('fbr_sync_message')->nullable();
        });

        Schema::create('fbr_invoice_syncs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('invoice_id');
            $table->string('fbr_invoice_number', 100);

            $table->string('method', 10)->nullable();
            $table->text('endpoint')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();

            $table->unsignedSmallInteger('http_status')->nullable();
            $table->string('sync_status', 40)->default('pending');
            $table->string('remote_status', 100)->nullable();
            $table->text('error_message')->nullable();

            $table->json('old_snapshot')->nullable();
            $table->json('new_snapshot')->nullable();
            $table->json('differences')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'invoice_id']);
            $table->index('fbr_invoice_number');
            $table->index('sync_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fbr_invoice_syncs');

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'fbr_last_synced_at',
                'fbr_sync_status',
                'fbr_remote_status',
                'fbr_sync_message',
            ]);
        });
    }
};
