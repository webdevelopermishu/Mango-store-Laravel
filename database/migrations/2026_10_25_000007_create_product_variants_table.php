<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku', 60)->nullable()->unique();
            $table->string('label_bn', 80);
            $table->string('label_en', 80);
            $table->unsignedInteger('weight_grams')->nullable();
            // Money is stored as integer paisa (1 BDT = 100 paisa) to avoid float rounding.
            $table->unsignedBigInteger('regular_price_paisa');
            $table->unsignedBigInteger('sale_price_paisa')->nullable();
            // null = stock not tracked for this variant
            $table->unsignedInteger('stock_quantity')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
