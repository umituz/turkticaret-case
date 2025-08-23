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
            $table->uuid()->primary()->comment('Primary key UUID');
            $table->string('order_number')->unique()->comment('Unique order number');
            $table->foreignUuid('user_uuid')->constrained('users', 'uuid')->onDelete('cascade')->comment('Reference to users table');
            $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending')->comment('Order status');
            $table->unsignedBigInteger('total_amount')->comment('Total order amount in minor units (cents)');
            $table->text('shipping_address')->comment('Shipping address details');
            $table->text('notes')->nullable()->comment('Order notes');
            $table->timestamp('shipped_at')->nullable()->comment('Order shipping timestamp');
            $table->timestamp('delivered_at')->nullable()->comment('Order delivery timestamp');
            $table->softDeletes();
            $table->timestamps();
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
