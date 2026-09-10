<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_user_roles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_user_id')
                ->constrained('business_users')
                ->cascadeOnDelete();

            $table->foreignId('role_id')
                ->constrained('roles')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['business_user_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_user_roles');
    }
};
