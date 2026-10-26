<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('sku', 60)->nullable()->unique();
            $table->string('name_bn');
            $table->string('name_en');
            $table->string('short_description_bn', 300)->nullable();
            $table->string('short_description_en', 300)->nullable();
            $table->text('description_bn')->nullable();
            $table->text('description_en')->nullable();
            // draft | published | out_of_stock
            $table->string('status', 20)->default('draft')->index();
            // kg | g | piece | pack | bottle | jar
            $table->string('unit', 20)->default('piece');
            $table->boolean('is_seasonal')->default(false);
            $table->unsignedTinyInteger('season_start_month')->nullable();
            $table->unsignedTinyInteger('season_end_month')->nullable();
            $table->boolean('is_featured_home')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('seo_title_bn')->nullable();
            $table->string('seo_title_en')->nullable();
            $table->string('seo_description_bn', 300)->nullable();
            $table->string('seo_description_en', 300)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
