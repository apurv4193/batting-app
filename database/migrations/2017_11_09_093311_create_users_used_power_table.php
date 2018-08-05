<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersUsedPowerTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('users_used_power', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('contest_id')->unsigned()->nullable();
            $table->integer('user_power_id')->unsigned()->nullable();
            $table->integer('item_id')->unsigned()->nullable();
            $table->integer('points')->default(0);
            $table->integer('remaining_pre_contest_substitution')->default(0);
            $table->integer('remaining_contest_substitution')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('contest_id')->references('id')->on('contests');
            $table->foreign('user_power_id')->references('id')->on('users_power');
            $table->foreign('item_id')->references('id')->on('items');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::table('users_used_power', function (Blueprint $table) {
            $table->dropForeign('users_used_power_user_id_foreign');
            $table->dropForeign('users_used_power_contest_id_foreign');
            $table->dropForeign('users_used_power_user_power_id_foreign');
            $table->dropForeign('users_used_power_item_id_foreign');
        });
        Schema::dropIfExists('users_used_power');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

}
