<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddedColumnsInTeamTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('teams', function (Blueprint $table) {
            //Add columns
            $table->string('team_image', 255)->nullble()->after('contest_type_id');
            $table->integer('win')->default(0)->after('team_image');
            $table->integer('loss')->default(0)->after('win');
            $table->decimal('team_cap_amount', 8, 2)->nullble()->after('loss');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn([
                'team_image',
                'win',
                'loss',
                'team_cap_amount'
            ]);
        });
    }

}
