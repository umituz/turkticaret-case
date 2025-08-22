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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid()->primary()->comment('Primary key UUID');
            $table->string('name')->comment('Product name');
            $table->string('slug')->unique()->comment('URL-friendly product identifier');
            $table->text('description')->nullable()->comment('Product description');
            $table->string('sku')->unique()->comment('Stock keeping unit');
            $table->unsignedBigInteger('price')->comment('Price in minor units (cents)');
            $table->integer('stock_quantity')->default(0)->comment('Current stock quantity');
            $table->boolean('is_active')->default(true)->comment('Product status');
            $table->foreignUuid('category_uuid')->constrained('categories', 'uuid')->onDelete('cascade')->comment('Reference to categories table');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
