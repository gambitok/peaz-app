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
        Schema::table('post_thumbnails', function (Blueprint $table) {
            $table->string('title', 255)->after('type');
            $table->string('description', 255)->after('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_thumbnails', function (Blueprint $table) {
            $table->dropColumn(['title', 'description']);
        });
    }
};
