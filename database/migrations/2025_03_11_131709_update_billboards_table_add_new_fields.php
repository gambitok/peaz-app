<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBillboardsTableAddNewFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('billboards', function (Blueprint $table) {
            $table->string('title')->default('')->change();
            $table->string('link')->default('')->change();
            $table->string('caption')->default('')->change();
            $table->unsignedBigInteger('tag_id')->default(1)->change();
            $table->boolean('verified')->default(0)->change();
            $table->unsignedBigInteger('user_id')->default(0)->change();
            $table->boolean('status')->default(0)->change();
            $table->string('logo_file')->default('')->after('file');
            $table->string('horizontal_file')->default('')->after('logo_file');
            $table->string('video_file')->default('')->after('horizontal_file');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('billboards', function (Blueprint $table) {
            $table->string('title')->default(null)->change();
            $table->string('link')->default(null)->change();
            $table->string('caption')->default(null)->change();
            $table->unsignedBigInteger('tag_id')->default(null)->change();
            $table->boolean('verified')->default(null)->change();
            $table->unsignedBigInteger('user_id')->default(null)->change();
            $table->boolean('status')->default(null)->change();
            $table->dropColumn('logo_file');
            $table->dropColumn('horizontal_file');
            $table->dropColumn('video_file');
        });
    }
}
