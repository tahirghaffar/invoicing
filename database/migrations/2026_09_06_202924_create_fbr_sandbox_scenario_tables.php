<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fbr_sandbox_scenarios', function (Blueprint $table) {
            $table->id();

            $table->string('scenario_code', 20)->unique();
            $table->string('description')->nullable();

            $table->boolean('active')->default(true);

            $table->timestamps();
        });


        Schema::create('business_sandbox_scenarios', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();

            $table->foreignId('sandbox_scenario_id')
                ->constrained('fbr_sandbox_scenarios')
                ->cascadeOnDelete();

            $table->string('status', 30)
                ->default('pending');

            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['business_id', 'sandbox_scenario_id'],
                'business_scenario_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_sandbox_scenarios');
        Schema::dropIfExists('fbr_sandbox_scenarios');
    }
};
