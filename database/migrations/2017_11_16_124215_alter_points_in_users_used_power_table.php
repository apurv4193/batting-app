<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPointsInUsersUsedPowerTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('users_used_power', function (Blueprint $table) {
            $table->decimal('points', 8, 2)->default(0.00)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('users_used_power', function (Blueprint $table) {
            $table->integer('points')->default(0)->change();
        });
    }

}
