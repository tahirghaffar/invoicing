<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('slug')->unique();

            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('logo_path')->nullable();

            $table->string('timezone', 50)->default('Asia/Karachi');
            $table->string('currency', 3)->default('PKR');

            $table->string('status', 30)->default('active')->index();

            // Reserved for non-critical extensible business preferences.
            // Important tax/FBR fields will receive proper columns in later stages.
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
