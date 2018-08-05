<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsInContestTable extends Migration {

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
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::table('contests', function (Blueprint $table) {

            // Dropped column
            $table->dropColumn('contest_max_winners');
            // Added columns
            $table->enum('privacy', array('public', 'friend-only', 'private'))->default('public')->after('contest_end_time')->nullable();
            $table->string('url')->after('privacy')->nullable();
            $table->integer('prize_distribution_plan_id')->after('url')->unsigned()->nullable();
            $table->integer('created_by')->after('contest_video_link')->default('0')->unsigned();

            $table->foreign('prize_distribution_plan_id')->references('id')->on('prize_distribution_plan');
            $table->foreign('created_by')->references('id')->on('users');
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
            $table->string('contest_max_winners')->after('contest_max_participants')->nullable();

            $table->dropForeign('contests_prize_distribution_plan_id_foreign');
            $table->dropForeign('contests_created_by_foreign');
            $table->dropColumn([
                'privacy',
                'url',
                'prize_distribution_plan_id',
                'created_by'
            ]);
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

}
