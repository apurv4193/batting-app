<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeagueInvitedUserTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('league_invited_user', function (Blueprint $table) {
            $table->integer('league_id')->unsigned()->nullable();
            $table->integer('user_id')->unsigned()->nullable();

            $table->foreign('league_id')->references('id')->on('league');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('league_invited_user', function(Blueprint $table) {
            $table->dropForeign('league_invited_user_league_id_foreign');
            $table->dropForeign('league_invited_user_user_id_foreign');
        });

        Schema::dropIfExists('league_invited_user');
    }

}
