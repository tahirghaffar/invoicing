<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {

            $table->decimal('quantity', 18, 2)->change();

            $table->decimal('unit_price', 18, 2)->change();

            $table->decimal('total_value', 18, 2)->change();

            $table->decimal('value_sales_excluding_st',18,2)->change();

            $table->decimal('fixed_notified_value_or_retail_price',18,2)->change();

            $table->decimal('sales_tax_applicable',18,2)->change();

            $table->decimal('sales_tax_withheld_at_source',18,2)->change();

            $table->decimal('extra_tax', 18, 2)->change();

            $table->decimal('further_tax', 18, 2)->change();

            $table->decimal('fed_payable', 18, 2)->change();

            $table->decimal('discount', 18, 2)->change();

            $table->decimal('line_total', 18, 2)->change();

            $table->decimal('tax_rate', 18, 2)->change();
        });
    }


    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {

            $table->decimal('quantity', 18, 4)->change();

            $table->decimal('unit_price', 18, 4)->change();

            $table->decimal('total_value', 18, 4)->change();

            $table->decimal('value_sales_excluding_st',18,4)->change();

            $table->decimal('fixed_notified_value_or_retail_price',18,4)->change();

            $table->decimal('sales_tax_applicable',18,4)->change();

            $table->decimal('sales_tax_withheld_at_source',18,4)->change();

            $table->decimal('extra_tax', 18, 4)->change();

            $table->decimal('further_tax', 18, 4)->change();

            $table->decimal('fed_payable', 18, 4)->change();

            $table->decimal('discount', 18, 4)->change();

            $table->decimal('line_total', 18, 4)->change();

            $table->decimal('tax_rate', 18, 4)->change();
        });
    }
};
