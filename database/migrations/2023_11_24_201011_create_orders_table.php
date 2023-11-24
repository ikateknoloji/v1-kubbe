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
            $table->unsignedBigInteger('customer_id');
            $table->string('order_code');
            $table->enum('status', ['OC','DP','DA', 'P' ,'PA', 'MS', 'MA' , 'PP', 'PR', 'PD', 'PIT']); 
            $table->unsignedBigInteger('manufacturer_id')->nullable();
            $table->decimal('offer_price', 8, 2);
            $table->enum('invoice_type', ['I', 'C']);
            $table->enum('is_rejected',['A','R','C','CR','MR'])->default('A');
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
