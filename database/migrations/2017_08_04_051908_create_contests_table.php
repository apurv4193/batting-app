<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContestsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::create('contests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('game_id')->unsigned()->nullable();
            $table->integer('contest_fees');
            $table->dateTime('contest_start_time');
            $table->dateTime('contest_end_time');
            $table->string('contest_video_link')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('game_id')->references('id')->on('games');
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::table('contests', function(Blueprint $table) {
            $table->dropForeign('contests_game_id_foreign');
        });
        Schema::dropIfExists('contests');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

}
