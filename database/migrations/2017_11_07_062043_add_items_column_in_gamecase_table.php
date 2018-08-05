<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddItemsColumnInGamecaseTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::table('gamecase', function(Blueprint $table) {
            $table->integer('items')->after('price')->unsigned()->nullable();
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
        Schema::table('gamecase', function(Blueprint $table) {
            // Drop Columns
            $table->dropColumn('items');
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

}
