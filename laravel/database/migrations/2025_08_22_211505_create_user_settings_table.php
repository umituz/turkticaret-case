<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->uuid('user_uuid');
            $table->boolean('email_notifications')->default(true);
            $table->boolean('push_notifications')->default(true);
            $table->boolean('sms_notifications')->default(false);
            $table->boolean('marketing_notifications')->default(false);
            $table->boolean('order_update_notifications')->default(true);
            $table->boolean('newsletter_notifications')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_uuid')->references('uuid')->on('users')->onDelete('cascade');
            $table->unique('user_uuid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
