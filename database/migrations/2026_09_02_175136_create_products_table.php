<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();

            $table->string('type', 20)
                ->default('product');

            $table->string('sku', 100)->nullable();

            $table->string('name');

            $table->text('description')->nullable();

            $table->string('hs_code', 50)->nullable();

            $table->string('uom', 100)->nullable();

            $table->string('sale_type', 150)->nullable();

            $table->decimal('tax_rate', 8, 4)
                ->nullable();

            $table->decimal('unit_price', 18, 4)
                ->default(0);

            $table->string('sro_schedule_no', 100)
                ->nullable();

            $table->string('sro_item_serial_no', 100)
                ->nullable();

            $table->decimal(
                'fixed_notified_value_or_retail_price',
                18,
                4
            )->default(0);

            $table->string('status', 30)
                ->default('active')
                ->index();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'business_id',
                'name'
            ]);

            $table->index([
                'business_id',
                'hs_code'
            ]);

            $table->unique([
                'business_id',
                'sku'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
