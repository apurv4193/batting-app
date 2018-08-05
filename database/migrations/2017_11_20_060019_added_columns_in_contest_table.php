<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddedColumnsInContestTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('contests', function (Blueprint $table) {
            $table->enum('cancel_by', array('admin', 'user', 'none'))->default('none')->after('status');
            $table->string('cancellation_reason')->default('Not Available')->after('cancel_by');
            $table->boolean('result_declare_status')->after('cancellation_reason')->default(0)->comment('0: Not declared, 1: Declared');
            $table->timestamp('result_declare_date')->after('result_declare_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('contests', function (Blueprint $table) {
            $table->dropColumn([
                'cancel_by',
                'cancellation_reason',
                'result_declare_status',
                'result_declare_date'
            ]);
        });
    }

}
