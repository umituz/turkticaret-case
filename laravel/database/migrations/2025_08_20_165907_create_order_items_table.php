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
        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid()->primary()->comment('Primary key UUID');
            $table->foreignUuid('order_uuid')->constrained('orders', 'uuid')->onDelete('cascade')->comment('Reference to orders table');
            $table->foreignUuid('product_uuid')->constrained('products', 'uuid')->onDelete('cascade')->comment('Reference to products table');
            $table->string('product_name')->comment('Product name at time of order');
            $table->integer('quantity')->comment('Ordered quantity');
            $table->unsignedBigInteger('unit_price')->comment('Unit price in minor units (cents) at time of order');
            $table->unsignedBigInteger('total_price')->comment('Total line price in minor units (cents)');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
