<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnCapAmountInContestTypeTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('contest_type', function (Blueprint $table) {
            $table->integer('contest_cap_amount')->after('type')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('contest_type', function (Blueprint $table) {
            $table->dropColumn('contest_cap_amount');
        });
    }

}
