<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToBillboardsAndRestaurantsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('billboards', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('verified')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('link')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        DB::statement('UPDATE billboards SET user_id = (SELECT id FROM users LIMIT 1) WHERE user_id IS NULL');
        DB::statement('UPDATE restaurants SET user_id = (SELECT id FROM users LIMIT 1) WHERE user_id IS NULL');

        Schema::table('billboards', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
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
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
}
