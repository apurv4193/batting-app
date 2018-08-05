<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContestImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::create('contest_score_images', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('contest_id')->unsigned()->nullable();
            $table->string('contest_image', 255)->nullable();
            
            $table->enum('status',['0','1','2'])->default('0')->comment('0=>pending,1=>approved,2=>rejected');
            $table->timestamps();

            $table->foreign('contest_id')->references('id')->on('contests');
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
        Schema::table('contest_score_images', function(Blueprint $table) {
            $table->dropForeign('contest_score_images_contest_id_foreign');
        });
        Schema::dropIfExists('contest_score_images');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
