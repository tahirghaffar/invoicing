<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();

            $table->string('business_name');
            $table->string('contact_person')->nullable();

            $table->string('registration_type', 30)
                ->default('unregistered');

            $table->string('ntn_cnic', 30)->nullable();
            $table->string('strn', 30)->nullable();

            $table->string('province', 100)->nullable();
            $table->string('city', 100)->nullable();

            $table->text('address')->nullable();

            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();

            $table->string('status', 30)
                ->default('active')
                ->index();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'business_id',
                'business_name'
            ]);

            $table->index([
                'business_id',
                'ntn_cnic'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
