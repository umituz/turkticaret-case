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
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->foreignUuid('user_uuid')->constrained('users','uuid');
            $table->foreignUuid('country_uuid')->constrained('countries','uuid');
            $table->string('type', 50);
            $table->boolean('is_default')->default(false);
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('company', 100)->nullable();
            $table->string('address_line_1', 255);
            $table->string('address_line_2', 255)->nullable();
            $table->string('city', 100);
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20);
            $table->string('phone', 20)->nullable();
            $table->softDeletes();
            $table->timestamps();


            $table->index(['user_uuid']);
            $table->index(['user_uuid', 'is_default']);
            $table->index(['user_uuid', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
