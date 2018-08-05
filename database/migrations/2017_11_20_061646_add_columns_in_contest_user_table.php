<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsInContestUserTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('contest_user', function (Blueprint $table) {
            $table->decimal('points_win', 8, 2)->default(0.00)->after('user_id');
            $table->decimal('score', 8, 2)->default(0.00)->after('points_win');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('contest_user', function (Blueprint $table) {
            $table->dropColumn([
                'points_win',
                'score'
            ]);
        });
    }

}
