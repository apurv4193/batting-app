<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersPowerTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('users_power', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('item_id')->unsigned()->nullable();
            $table->integer('gamecase_id')->unsigned()->nullable();
            $table->integer('gamecase_bundle_id')->unsigned()->nullable();
            $table->boolean('used')->default(0)->comment('0: Not used (Available for use), 1: Used in contest (Not available)');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('gamecase_id')->references('id')->on('gamecase');
            $table->foreign('gamecase_bundle_id')->references('id')->on('gamecase_bundle');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::table('users_power', function (Blueprint $table) {
            $table->dropForeign('users_power_user_id_foreign');
            $table->dropForeign('users_power_item_id_foreign');
            $table->dropForeign('users_power_gamecase_id_foreign');
            $table->dropForeign('users_power_gamecase_bundle_id_foreign');
        });
        Schema::dropIfExists('users_power');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

}
