<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGamesPlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::create('games_players', function (Blueprint $table) {
            // Added columns
            $table->increments('id');
            $table->integer('game_id')->unsigned()->nullable();
            $table->integer('player_id')->unsigned()->nullable();
            $table->decimal('cap_amount', 8, 2)->nullable();
            $table->integer('win')->unsigned()->nullable();
            $table->integer('loss')->unsigned()->nullable();
            // Foreign Key            
            $table->foreign('game_id')->references('id')->on('games');
            $table->foreign('player_id')->references('id')->on('players');
            $table->timestamps();
            $table->softDeletes();
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::dropIfExists('games_players');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
