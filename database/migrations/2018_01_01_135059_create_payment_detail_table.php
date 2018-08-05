<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentDetailTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('payment_detail', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->string('transaction_id', 255)->nullable();
            $table->decimal('amount', 8, 2)->nullable();
            $table->enum('status', array('success', 'fail'))->default('success');
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
    public function down() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::table('payment_detail', function (Blueprint $table) {
            $table->dropForeign('payment_detail_user_id_foreign');
        });
        Schema::dropIfExists('payment_detail');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

}
