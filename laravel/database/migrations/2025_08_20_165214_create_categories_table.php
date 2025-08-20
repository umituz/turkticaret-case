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
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid()->primary()->comment('Primary key UUID');
            $table->string('name')->comment('Category name');
            $table->text('description')->nullable()->comment('Category description');
            $table->string('slug')->unique()->comment('URL-friendly category identifier');
            $table->boolean('is_active')->default(true)->comment('Category status');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
