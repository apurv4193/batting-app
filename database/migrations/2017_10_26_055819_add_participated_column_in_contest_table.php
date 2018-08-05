<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParticipatedColumnInContestTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('contests', function (Blueprint $table) {
            $table->integer('participated')->after('contest_max_participants')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('contests', function (Blueprint $table) {
            $table->dropColumn('participated');
        });
    }

}
