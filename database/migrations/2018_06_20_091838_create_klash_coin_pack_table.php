<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKlashCoinPackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('klash_coin_pack', function (Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->string('name', 100)->comment('Name of coin pack');
            $table->bigInteger('number_of_klash_coins')->unsigned()->nullable();
            $table->string('cost_to_user', 100)->nullable();
            $table->string('image', 100)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('klash_coin_pack');
    }
}
