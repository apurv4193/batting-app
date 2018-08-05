<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddedColumnsInPaymentDetailTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::table('payment_detail', function (Blueprint $table) {
            // Added columns
            $table->string('payout_batch_id')->after('transaction_id')->nullable();
            $table->string('payout_item_id')->after('payout_batch_id')->nullable();
            $table->decimal('wallet_points', 8, 2)->after('payout_item_id')->nullable()->comment('Actual points that is added or withdrawed from wallet. transaction_type = paid :: Added, transaction_type = received :: Withdrawed');
            $table->enum('payuot_item_transaction_status', array('NEW', 'SUCCESS', 'DENIED', 'PENDING', 'FAILED', 'UNCLAIMED', 'RETURNED', 'ONHOLD', 'BLOCKED', 'REFUNDED'))->after('status')->nullable();
            $table->enum('transaction_type', array('paid', 'received'))->default('paid')->after('payuot_item_transaction_status');
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::table('payment_detail', function (Blueprint $table) {
            $table->dropColumn(['payout_batch_id', 'payout_item_id', 'wallet_points','payuot_item_transaction_status','transaction_type']);
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

}
