<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnInContestUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contest_user', function (Blueprint $table) {
            $table->tinyInteger('is_paid')->comment('1:Yes, 0:No')->after('user_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contest_user', function (Blueprint $table) {
            $table->dropColumn([
                'is_paid'
            ]);
        });
    }
}
