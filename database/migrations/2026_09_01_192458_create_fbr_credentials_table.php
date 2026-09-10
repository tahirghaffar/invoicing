<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fbr_credentials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete()
                ->unique();

            $table->string('environment', 20)
                ->default('sandbox');

            $table->text('sandbox_token')->nullable();
            $table->text('production_token')->nullable();

            $table->string('sandbox_api_url')
                ->nullable();

            $table->string('production_api_url')
                ->default('https://gw.fbr.gov.pk/di_data/v1/di/postinvoicedata');

            $table->boolean('production_enabled')
                ->default(false);

            $table->timestamp('last_connection_test_at')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fbr_credentials');
    }
};
