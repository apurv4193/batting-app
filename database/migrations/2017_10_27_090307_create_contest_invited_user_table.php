<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContestInvitedUserTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('contest_invited_user', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('contest_id')->unsigned()->nullable();
            $table->integer('user_id')->unsigned()->nullable();
            $table->enum('invitation_status', array('pending', 'accepted', 'deleted'))->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('contest_id')->references('id')->on('contests');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::table('contest_invited_user', function (Blueprint $table) {
            $table->dropForeign('contest_invited_user_contest_id_foreign');
            $table->dropForeign('contest_invited_user_user_id_foreign');
        });
        Schema::dropIfExists('contest_invited_user');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

}
