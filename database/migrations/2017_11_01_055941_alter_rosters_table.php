<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRostersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::table('rosters', function(Blueprint $table) {
            $table->integer('user_id')->nullable()->unsigned()->after('contest_id');
            $table->integer('player_id')->nullable()->unsigned()->after('user_id');
            $table->decimal('player_cap_amount', 8, 2)->nullable()->after('player_id');

            $table->dropColumn([
                'roster',
                'roster_cap_amount'
            ]);

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('player_id')->references('id')->on('players');
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

            $table->dropForeign('rosters_user_id_foreign');
            $table->dropForeign('rosters_player_id_foreign');

            $table->dropColumn([
                'user_id',
                'player_id',
                'player_cap_amount'
            ]);

            $table->string('roster', 255)->after('contest_id');
            $table->integer('roster_cap_amount')->nullable()->after('roster');
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

}
