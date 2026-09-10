<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fbr_submissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->cascadeOnDelete();

            $table->uuid('request_uuid')->unique();

            $table->string('environment', 20)->default('sandbox');
            $table->string('operation', 30)->default('post');

            $table->string('scenario_id', 20)->nullable();

            $table->string('endpoint', 500);

            $table->json('request_payload');
            $table->json('response_payload')->nullable();

            $table->unsignedInteger('http_status')->nullable();

            $table->string('fbr_status_code', 20)->nullable();
            $table->string('fbr_status', 50)->nullable();

            $table->string('fbr_invoice_number', 200)->nullable();

            $table->text('error_message')->nullable();

            $table->timestamp('submitted_at')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fbr_submissions');
    }
};
