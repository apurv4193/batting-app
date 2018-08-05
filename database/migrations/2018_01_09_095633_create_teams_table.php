<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('game_id')->unsigned()->nullable();
            $table->integer('contest_type_id')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('game_id')->references('id')->on('games');
            $table->foreign('contest_type_id')->references('id')->on('contest_type');
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
        Schema::dropIfExists('teams');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
