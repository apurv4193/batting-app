<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnScoreInRostersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rosters', function (Blueprint $table) {
           $table->decimal('score', 8, 2)->default(0.00)->after('player_cap_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rosters', function (Blueprint $table) {
            $table->dropColumn([
                'score'
            ]);
        });
    }
}
