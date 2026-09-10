<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {

            $table->string('ntn', 30)
                ->nullable()
                ->after('legal_name')
                ->index();

            $table->string('strn', 30)
                ->nullable()
                ->after('ntn')
                ->index();

            $table->string('registration_type', 30)
                ->default('registered')
                ->after('strn');

            $table->string('province', 100)
                ->nullable()
                ->after('registration_type');

            $table->string('city', 100)
                ->nullable()
                ->after('province');

            $table->text('address')
                ->nullable()
                ->after('city');

            $table->string('principal_activity_code', 50)
                ->nullable()
                ->after('address');

            $table->string('principal_activity_description')
                ->nullable()
                ->after('principal_activity_code');

            $table->timestamp('profile_completed_at')
                ->nullable()
                ->after('principal_activity_description');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {

            $table->dropIndex(['ntn']);
            $table->dropIndex(['strn']);

            $table->dropColumn([
                'ntn',
                'strn',
                'registration_type',
                'province',
                'city',
                'address',
                'principal_activity_code',
                'principal_activity_description',
                'profile_completed_at',
            ]);
        });
    }
};
