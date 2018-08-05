<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFieldTypeContestMaxParticipantsInContestTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    // Note: Renaming columns in a table with a enum column is not currently supported with Doctrine\DBAL.
    // To resolve issue
    public function __construct() {
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }

    public function up() {
        Schema::table('contests', function (Blueprint $table) {
            $table->integer('contest_max_participants')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('contests', function (Blueprint $table) {
            $table->string('contest_max_participants')->nullable()->change();
        });
    }

}
