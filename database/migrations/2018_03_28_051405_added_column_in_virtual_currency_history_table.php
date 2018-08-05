<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddedColumnInVirtualCurrencyHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('virtual_currency_history', function (Blueprint $table) {
            $table->integer('gamecase_id')->after('virtual_currency')->unsigned()->nullable();
            $table->integer('gamecase_bundle_id')->after('gamecase_id')->unsigned()->nullable();
            $table->enum('status', array('credit', 'debit'))->after('gamecase_bundle_id');
            $table->foreign('gamecase_id')->references('id')->on('gamecase');
            $table->foreign('gamecase_bundle_id')->references('id')->on('gamecase_bundle');
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
            $table->dropForeign('virtual_currency_history_gamecase_id_foreign');
            $table->dropForeign('virtual_currency_history_gamecase_bundle_id_foreign');
            $table->dropColumn([
                'gamecase_id',
                'gamecase_bundle_id',
                'status'
            ]);
        });
    }
}
