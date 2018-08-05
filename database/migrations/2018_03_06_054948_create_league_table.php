<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeagueTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('league', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('game_id')->unsigned()->nullable();
            $table->integer('contest_type_id')->unsigned()->nullable();
            $table->integer('level_id')->unsigned()->nullable();
            $table->string('league_name')->nullable();
            $table->date('league_start_date')->nullable();
            $table->date('league_end_date')->nullable();
            $table->integer('league_min_participants')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->enum('status', ['upcoming', 'live', 'completed', 'cancelled', 'pending'])->default('upcoming');
            $table->timestamps();

            $table->foreign('game_id')->references('id')->on('games');
            $table->foreign('contest_type_id')->references('id')->on('contest_type');
            $table->foreign('level_id')->references('id')->on('level');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('league', function(Blueprint $table) {
            $table->dropForeign('league_game_id_foreign');
            $table->dropForeign('league_contest_type_id_foreign');
            $table->dropForeign('league_level_id_foreign');
            $table->dropForeign('league_created_by_foreign');
        });
        
        Schema::dropIfExists('league');
    }

}
