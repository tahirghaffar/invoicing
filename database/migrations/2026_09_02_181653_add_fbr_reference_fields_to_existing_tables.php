<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {

            $table->unsignedInteger('province_code')
                ->nullable()
                ->after('province');

        });


        Schema::table('customers', function (Blueprint $table) {

            $table->unsignedInteger('province_code')
                ->nullable()
                ->after('province');

        });


        Schema::table('products', function (Blueprint $table) {

            $table->unsignedInteger('uom_id')
                ->nullable()
                ->after('uom');

            $table->unsignedInteger('transaction_type_id')
                ->nullable()
                ->after('sale_type');

            $table->unsignedInteger('rate_id')
                ->nullable()
                ->after('tax_rate');

            $table->string('tax_rate_description', 255)
                ->nullable()
                ->after('rate_id');

        });
    }


    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('province_code');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('province_code');
        });

        Schema::table('products', function (Blueprint $table) {

            $table->dropColumn([
                'uom_id',
                'transaction_type_id',
                'rate_id',
                'tax_rate_description',
            ]);

        });
    }
};
