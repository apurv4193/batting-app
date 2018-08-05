<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusColumnInTeamTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('teams', function (Blueprint $table) {
            //Add columns
            $table->tinyInteger('status')->comment('0:Active, 1:Delete')->after('team_cap_amount')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('teams', function(Blueprint $table) {
            $table->dropColumn([
                'status'
            ]);
        });
    }

}
