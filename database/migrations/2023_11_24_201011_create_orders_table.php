<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_name');
            $table->unsignedBigInteger('customer_id');
            $table->string('order_code');
            $table->enum('status', ['OC','DP','DA', 'P' ,'PA', 'MS','PP', 'PR', 'PIT', 'PD']); 
            $table->unsignedBigInteger('manufacturer_id')->nullable();
            $table->decimal('offer_price', 8, 2);
            $table->enum('invoice_type', ['I', 'C']);
            $table->enum('is_rejected',['A','R','C'])->default('A');
            $table->text('note')->nullable();
            $table->timestamp('production_start_date')->nullable(); // Üretime başlama tarihi
            $table->timestamp('production_date')->nullable(); // Üretim tarihi
            $table->timestamp('delivery_date')->nullable(); // Teslim tarihi
            $table->timestamps();

            $table->foreign('customer_id')->references('user_id')->on('customers')->onDelete('cascade');
            $table->foreign('manufacturer_id')->references('user_id')->on('manufacturers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
