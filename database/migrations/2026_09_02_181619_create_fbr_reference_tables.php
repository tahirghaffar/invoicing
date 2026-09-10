<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fbr_provinces', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('code')->unique();
            $table->string('description', 100);

            $table->boolean('active')->default(true);

            $table->timestamps();
        });


        Schema::create('fbr_document_types', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('fbr_id')->unique();
            $table->string('description', 150);

            $table->boolean('active')->default(true);

            $table->timestamps();
        });


        Schema::create('fbr_hs_codes', function (Blueprint $table) {
            $table->id();

            $table->string('hs_code', 50)->unique();

            $table->text('description')->nullable();

            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->index('hs_code');
        });


        Schema::create('fbr_transaction_types', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('fbr_id')->unique();

            $table->string('description', 255);

            $table->boolean('active')->default(true);

            $table->timestamps();
        });


        Schema::create('fbr_uoms', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('fbr_id')->unique();

            $table->string('description', 150);

            $table->boolean('active')->default(true);

            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('fbr_uoms');
        Schema::dropIfExists('fbr_transaction_types');
        Schema::dropIfExists('fbr_hs_codes');
        Schema::dropIfExists('fbr_document_types');
        Schema::dropIfExists('fbr_provinces');
    }
};
