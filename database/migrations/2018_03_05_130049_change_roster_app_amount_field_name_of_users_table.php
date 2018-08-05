<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeRosterAppAmountFieldNameOfUsersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function __construct() {
        Schema::getConnection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }

    public function up() {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('roster_app_amount', 'virtual_currency');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('virtual_currency', 'roster_app_amount');
        });
    }

}
