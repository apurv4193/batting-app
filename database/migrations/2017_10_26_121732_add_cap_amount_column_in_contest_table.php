<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCapAmountColumnInContestTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('contests', function (Blueprint $table) {
            $table->integer('roster_cap_amount')->after('contest_fees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('contests', function (Blueprint $table) {
            $table->dropColumn('roster_cap_amount');
        });
    }

}
