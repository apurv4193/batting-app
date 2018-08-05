<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnContestLevelIdInContestsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::table('contests', function (Blueprint $table) {
            $table->integer('level_id')->nullable()->unsigned()->after('contest_type_id');
            $table->string('contest_max_participants')->after('contest_end_time')->nullable();
            $table->string('contest_max_winners')->after('contest_max_participants')->nullable();

            $table->foreign('level_id')->references('id')->on('level');
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
        Schema::table('contests', function (Blueprint $table) {
            $table->dropForeign('contests_level_id_foreign');
            
            $table->dropColumn('level_id');
            $table->dropColumn('contest_max_participants');
            $table->dropColumn('contest_max_winners');
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

}
