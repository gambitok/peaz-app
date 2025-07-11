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
            $table->string('file', 255)->default('')->after('post_id');
            $table->string('file_type', 255)->default('')->after('file');

            $table->string('thumbnail', 255)->default('')->change();
            $table->string('type', 255)->default('')->change();
            $table->string('title', 255)->default('')->change();
            $table->string('description', 255)->default('')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_thumbnails', function (Blueprint $table) {
            $table->dropColumn(['file', 'file_type']);

            $table->string('thumbnail', 255)->nullable()->change();
            $table->string('type', 255)->nullable()->change();
            $table->string('title', 255)->nullable()->change();
            $table->string('description', 255)->nullable()->change();
        });
    }
};
