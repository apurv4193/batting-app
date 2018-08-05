<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVirtualCurrencyHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('virtual_currency_history', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->bigInteger('points')->unsigned()->nullable();
            $table->bigInteger('virtual_currency')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::table('virtual_currency_history', function(Blueprint $table) {
            $table->dropForeign('virtual_currency_history_user_id_foreign');
        });
        
        Schema::dropIfExists('virtual_currency_history');
    }
}
