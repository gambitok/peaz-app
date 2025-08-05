<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('post_thumbnails', function (Blueprint $table) {
            $table->string('title')->nullable()->default(null)->change();
            $table->string('description')->nullable()->default(null)->change();
        });
    }

    public function down()
    {
        Schema::table('post_thumbnails', function (Blueprint $table) {
            $table->string('title')->nullable(false)->default('')->change();
            $table->string('description')->nullable(false)->default('')->change();
        });
    }
};
