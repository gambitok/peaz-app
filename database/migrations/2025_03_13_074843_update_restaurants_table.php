<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateRestaurantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('title', 255)->default('')->after('id');
            $table->string('link', 255)->default('')->change();
            $table->unsignedBigInteger('user_id')->default(0)->change();
            $table->boolean('status')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->string('link', 255)->default(null)->change();
            $table->unsignedBigInteger('user_id')->default(null)->change();
            $table->unsignedTinyInteger('status')->default(null)->change();
        });
    }
}
