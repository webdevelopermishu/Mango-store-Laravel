<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            // Links survive only while the product exists; names and prices below are a snapshot.
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name_bn');
            $table->string('product_name_en');
            $table->string('variant_label_bn', 80);
            $table->string('variant_label_en', 80);
            $table->string('sku', 60)->nullable();
            $table->unsignedBigInteger('unit_price_paisa');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('line_total_paisa');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
