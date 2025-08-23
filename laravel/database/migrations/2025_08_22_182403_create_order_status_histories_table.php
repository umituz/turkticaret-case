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
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->uuid()->primary()->comment('Primary key UUID');
            $table->foreignUuid('order_uuid')->constrained('orders', 'uuid')->onDelete('cascade')->comment('Reference to orders table');
            $table->string('old_status')->nullable()->comment('Previous order status');
            $table->string('new_status')->comment('New order status');
            $table->foreignUuid('changed_by_uuid')->nullable()->constrained('users', 'uuid')->onDelete('set null')->comment('User who changed the status');
            $table->text('notes')->nullable()->comment('Optional notes about the status change');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['order_uuid', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
    }
};
