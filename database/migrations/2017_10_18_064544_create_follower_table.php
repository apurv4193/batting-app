<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFollowerTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('follower', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('following_id')->unsigned()->nullable();
            $table->integer('follower_id')->unsigned()->nullable();
            $table->enum('status', array('pending', 'accepted', 'deleted', 'blocked', 'blocked-by'))->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('following_id')->references('id')->on('users');
            $table->foreign('follower_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::table('follower', function (Blueprint $table) {
            $table->dropForeign('follower_following_id_foreign');
            $table->dropForeign('follower_follower_id_foreign');
        });
        Schema::dropIfExists('follower');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

}
