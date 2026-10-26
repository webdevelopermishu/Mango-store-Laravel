<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // Set right after insert from the ID (see Order::numberFor), so it starts null.
            $table->string('order_number', 20)->nullable()->unique();
            // Secret part of the customer's order link; without it the order page returns 404.
            $table->string('access_token', 64);
            $table->string('locale', 5);
            $table->string('customer_name', 120);
            $table->string('phone', 20)->index();
            $table->string('email', 150)->nullable();
            $table->text('address');
            $table->string('district', 120);
            $table->string('delivery_zone', 30);
            $table->text('note')->nullable();
            $table->unsignedBigInteger('subtotal_paisa');
            $table->unsignedBigInteger('delivery_fee_paisa');
            $table->unsignedBigInteger('total_paisa');
            // cod | bkash | nagad
            $table->string('payment_method', 20);
            // unpaid | pending_verification | paid | refunded
            $table->string('payment_status', 30)->index();
            $table->string('payer_number', 20)->nullable();
            $table->string('payment_reference', 40)->nullable();
            // pending | confirmed | processing | shipped | delivered | cancelled
            $table->string('status', 20)->default('pending')->index();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
