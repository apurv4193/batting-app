<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnContestUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('contest_user', function (Blueprint $table) {

            //Add columns
            $table->tinyInteger('is_win')->comment('0:Not win, 1:Win')->after('rank')->default(0);
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

            $table->dropColumn('is_win');
        });
    }
}
