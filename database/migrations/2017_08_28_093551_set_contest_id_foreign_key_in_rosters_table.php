<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetContestIdForeignKeyInRostersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::table('rosters', function (Blueprint $table) {
            $table->foreign('contest_id')->references('id')->on('contests');
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
        Schema::table('rosters', function(Blueprint $table) {
            $table->dropForeign('rosters_contest_id_foreign');
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

}
