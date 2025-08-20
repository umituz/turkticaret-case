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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->uuid()->primary()->comment('Primary key UUID');
            $table->foreignUuid('cart_uuid')->constrained('carts', 'uuid')->onDelete('cascade')->comment('Reference to carts table');
            $table->foreignUuid('product_uuid')->constrained('products', 'uuid')->onDelete('cascade')->comment('Reference to products table');
            $table->integer('quantity')->default(1)->comment('Item quantity');
            $table->unsignedBigInteger('unit_price')->comment('Unit price in minor units (cents) at time of adding to cart');
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['cart_uuid', 'product_uuid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
