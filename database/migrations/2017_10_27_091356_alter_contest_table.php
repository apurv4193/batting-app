<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterContestTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::table('contests', function (Blueprint $table) {
            // Dropped column
            $table->dropColumn('url');
            // Added columns
            $table->enum('status', array('upcoming', 'contest-locked', 'roster-locked', 'live', 'completed', 'cancelled', 'pending'))->default('upcoming')->after('created_by');
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
        Schema::table('contests', function (Blueprint $table) {
            $table->string('url')->after('privacy')->nullable();
            $table->dropColumn('status');
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

}
