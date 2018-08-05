<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUpdatedByColumnInContestsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::table('contests', function (Blueprint $table) {
            $table->integer('updated_by')->after('created_by')->unsigned()->nullable();
            
            $table->foreign('updated_by')->references('id')->on('users');
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
            $table->dropForeign('contests_updated_by_foreign');
            
            $table->dropColumn('updated_by');
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

}
