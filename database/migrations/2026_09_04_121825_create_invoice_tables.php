<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_sequences', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();

            $table->string('prefix', 20)->default('INV');
            $table->unsignedInteger('year');
            $table->unsignedBigInteger('current_number')->default(0);
            $table->unsignedInteger('padding')->default(6);

            $table->timestamps();

            $table->unique([
                'business_id',
                'prefix',
                'year'
            ]);
        });


        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();

            $table->string('invoice_number', 100);
            $table->string('invoice_type', 50)
                ->default('Sale Invoice');

            $table->date('invoice_date');

            // Seller snapshot
            $table->string('seller_ntn', 30);
            $table->string('seller_strn', 30)->nullable();
            $table->string('seller_business_name');
            $table->string('seller_province', 100);
            $table->text('seller_address');

            // Buyer snapshot
            $table->string('buyer_ntn_cnic', 30)->nullable();
            $table->string('buyer_strn', 30)->nullable();
            $table->string('buyer_business_name');
            $table->string('buyer_registration_type', 30);
            $table->string('buyer_province', 100);
            $table->text('buyer_address');

            // Totals
            $table->decimal('subtotal', 18, 4)->default(0);
            $table->decimal('sales_tax', 18, 4)->default(0);
            $table->decimal('further_tax', 18, 4)->default(0);
            $table->decimal('extra_tax', 18, 4)->default(0);
            $table->decimal('fed', 18, 4)->default(0);
            $table->decimal('discount', 18, 4)->default(0);
            $table->decimal('grand_total', 18, 4)->default(0);

            $table->string('status', 30)
                ->default('draft')
                ->index();

            // Reserved for later FBR submission stage
            $table->string('fbr_status', 50)->nullable();
            $table->string('fbr_invoice_number', 150)->nullable();
            $table->timestamp('fbr_submitted_at')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique([
                'business_id',
                'invoice_number'
            ]);
        });


        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            $table->string('hs_code', 50);
            $table->text('product_description');

            $table->unsignedInteger('rate_id')->nullable();
            $table->string('rate_description', 100)->nullable();
            $table->decimal('tax_rate', 8, 4)->default(0);

            $table->unsignedInteger('uom_id')->nullable();
            $table->string('uom', 100);

            $table->decimal('quantity', 18, 4)->default(1);
            $table->decimal('unit_price', 18, 4)->default(0);

            $table->decimal('total_value', 18, 4)->default(0);

            $table->decimal(
                'value_sales_excluding_st',
                18,
                4
            )->default(0);

            $table->decimal(
                'fixed_notified_value_or_retail_price',
                18,
                4
            )->default(0);

            $table->decimal(
                'sales_tax_applicable',
                18,
                4
            )->default(0);

            $table->decimal(
                'sales_tax_withheld_at_source',
                18,
                4
            )->default(0);

            $table->decimal('extra_tax', 18, 4)->default(0);
            $table->decimal('further_tax', 18, 4)->default(0);
            $table->decimal('fed_payable', 18, 4)->default(0);
            $table->decimal('discount', 18, 4)->default(0);

            $table->unsignedInteger('transaction_type_id');
            $table->string('sale_type', 255);

            $table->string('sro_schedule_no', 100)->nullable();
            $table->string('sro_item_serial_no', 100)->nullable();

            $table->decimal('line_total', 18, 4)->default(0);

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index([
                'business_id',
                'invoice_id'
            ]);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('invoice_sequences');
    }
};
