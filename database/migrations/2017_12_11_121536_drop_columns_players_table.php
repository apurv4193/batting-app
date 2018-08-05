<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropColumnsPlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::table('players', function($table) {
            
            Schema::table('players', function(Blueprint $table) {
                $table->dropForeign('players_game_id_foreign');
            });
            $table->dropColumn('game_id');
            $table->dropColumn('cap_amount');
            $table->dropColumn('win');
            $table->dropColumn('loss');
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
        Schema::table('players', function (Blueprint $table) {
            // Added columns
            $table->integer('game_id')->after('name')->unsigned()->nullable();
            $table->decimal('cap_amount', 8, 2)->after('profile_image')->nullable();
            $table->integer('win')->after('cap_amount')->unsigned()->nullable();
            $table->integer('loss')->after('win')->unsigned()->nullable();
            // Foreign Key            
            $table->foreign('game_id')->references('id')->on('games');
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
