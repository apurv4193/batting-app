<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddStatusColumnPrizeDistributionPlanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('prize_distribution_plan', function (Blueprint $table) {
            //Add columns
            $table->tinyInteger('status')->comment('0:Active, 1:Delete')->after('winner')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prize_distribution_plan', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
