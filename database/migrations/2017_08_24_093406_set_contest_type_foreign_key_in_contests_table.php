<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetContestTypeForeignKeyInContestsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::table('contests', function (Blueprint $table) {
            $table->foreign('contest_type_id')->references('id')->on('contest_type');
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
            $table->dropForeign('contests_contest_type_id_foreign');
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

}
