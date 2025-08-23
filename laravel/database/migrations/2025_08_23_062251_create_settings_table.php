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
        Schema::create('settings', function (Blueprint $table) {
            $table->uuid()->primary()->comment('Primary key UUID');
            $table->string('key')->unique()->comment('Unique setting key');
            $table->json('value')->comment('Setting value as JSON');
            $table->string('type')->default('string')->comment('Value type: string, json, boolean, integer');
            $table->string('group')->default('system')->comment('Setting group: system, commerce, ui, notification');
            $table->text('description')->nullable()->comment('Setting description');
            $table->boolean('is_active')->default(true)->comment('Is setting active');
            $table->boolean('is_editable')->default(true)->comment('Can be edited by admin');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['group', 'is_active']);
            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
