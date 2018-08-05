<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRostersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('rosters', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('contest_id')->unsigned()->nullable();
            $table->string('roster', 255);
            $table->integer('roster_cap_amount')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::dropIfExists('rosters');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

}
