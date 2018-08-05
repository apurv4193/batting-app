<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTeamPlayerCapAmountInTeamsPlayersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('teams_players', function (Blueprint $table) {
            //Add columns
            $table->decimal('team_player_cap_amount', 8, 2)->nullble()->after('player_id')->comment('Player cap amount while created or updated team.');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('teams_players', function (Blueprint $table) {
            $table->dropColumn('team_player_cap_amount');
        });
    }

}
