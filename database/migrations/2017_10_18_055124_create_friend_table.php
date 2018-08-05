<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFriendTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('friends', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('receiver_id')->unsigned()->nullable();
            $table->integer('requester_id')->unsigned()->nullable();
            $table->enum('status', array('pending', 'accepted', 'deleted', 'blocked', 'blocked-by'))->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('receiver_id')->references('id')->on('users');
            $table->foreign('requester_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::table('friends', function (Blueprint $table) {
            $table->dropForeign('friends_receiver_id_foreign');
            $table->dropForeign('friends_requester_id_foreign');
        });
        Schema::dropIfExists('friends');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

}
