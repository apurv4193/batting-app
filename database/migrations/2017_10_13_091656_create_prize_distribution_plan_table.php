<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrizeDistributionPlanTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('prize_distribution_plan', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('winner')->unsigned();
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
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::dropIfExists('prize_distribution_plan');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

}
