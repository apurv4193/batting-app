<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnInItemTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::table('items', function (Blueprint $table) {
            $table->string('slug', 100)->after('name')->nullable();
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
        Schema::table('items', function (Blueprint $table) {
            //Drop Columns 
            $table->dropColumn('slug');            
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

}
