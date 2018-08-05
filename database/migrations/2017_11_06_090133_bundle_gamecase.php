<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BundleGamecase extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('bundle_game_case', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('game_case_bundle_id')->unsigned()->nullable();
            $table->foreign('game_case_bundle_id')->references('id')->on('gamecase_bundle');
            $table->integer('game_case_id')->unsigned()->nullable();
            $table->foreign('game_case_id')->references('id')->on('gamecase');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('bundle_game_case', function (Blueprint $table) {
            $table->dropForeign('game_case_bundle_id');
            $table->dropForeign('game_case_id');
            $table->dropColumn([
                'id',
                'game_case_bundle_id',
                'game_case_id',
            ]);
        });
    }

}
