<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterContestsSponsorPrizeColumn extends Migration
{
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
    public function up()
    {
        Schema::table('contests', function (Blueprint $table) {
            $table->decimal('sponsored_prize', 8, 2)->default(NULL)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contests', function(Blueprint $table) {
            $table->decimal('sponsored_prize', 8, 2)->default('0.00')->change();
        });
    }
}
