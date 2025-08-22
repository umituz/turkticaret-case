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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignUuid('language_uuid')->constrained('languages','uuid')->on('languages')->onDelete('set null');
            $table->index('language_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['language_uuid']);
            $table->dropIndex(['language_uuid']);
            $table->dropColumn('language_uuid');
        });
    }
};
