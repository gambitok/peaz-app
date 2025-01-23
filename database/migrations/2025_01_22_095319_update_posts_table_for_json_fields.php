<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePostsTableForJsonFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('dietary');
            $table->json('tags')->nullable()->change();
            $table->json('dietaries')->nullable()->after('tags');
            $table->json('cuisines')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->longText('dietary')->nullable()->after('tags');
            $table->dropColumn('dietaries');
            $table->longText('tags')->nullable()->change();
            $table->longText('cuisines')->nullable()->change();
        });
    }
}
