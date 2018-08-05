<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamsPlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teams_players', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('team_id')->unsigned()->nullable();
            $table->integer('player_id')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('teams');
            $table->foreign('player_id')->references('id')->on('players');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::dropIfExists('teams_players');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
