<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBillboardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('billboards', function (Blueprint $table) {
            $table->string('link', 255)->after('file');
            $table->unsignedBigInteger('tag_id')->after('link');
            $table->boolean('verified')->after('tag_id');

            $table->foreign('tag_id')->references('id')->on('tags');
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
            $table->dropColumn('link');
            $table->dropColumn('tag_id');
            $table->dropColumn('verified');
        });
    }
}
