<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterGamecaseBundleTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::table('gamecase_bundle', function(Blueprint $table) {
            $table->dropColumn('gamecase_ids');
            $table->string('gamecase_image')->after('name')->nullable();
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
        Schema::table('gamecase_bundle', function(Blueprint $table) {
            // Old Drop Columns
            $table->string('gamecase_ids')->after('name')->nullable();
            // Drop Columns
            $table->dropColumn('gamecase_image');
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

}
