<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLeagueIdColumnInContestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
         Schema::table('contests', function(Blueprint $table) {
            $table->integer('league_id')->after('id')->unsigned()->nullable();
            $table->foreign('league_id')->references('id')->on('league');
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::table('contests', function(Blueprint $table) {
            $table->dropColumn('league_id');
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1');  
    }
}
