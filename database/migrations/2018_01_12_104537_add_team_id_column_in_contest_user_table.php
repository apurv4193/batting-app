<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTeamIdColumnInContestUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contest_user', function (Blueprint $table) {
            $table->integer('team_id')->unsigned()->nullable()->after('user_id');
            $table->foreign('team_id')->references('id')->on('teams');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contest_user', function (Blueprint $table) {
            $table->dropForeign('teams_team_id_foreign');
            $table->dropColumn('team_id');
        });
    }
}
