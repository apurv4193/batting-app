<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVideoLinkOfAdsInContestTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('contests', function (Blueprint $table) {
            $table->string('sponsored_by')->after('prize')->nullable()->comment('Contest sponsor by this');
            $table->string('sponsored_video_link')->after('sponsored_by')->nullable()->comment('video link which is given by sponsor for this contest to advertise');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('contests', function (Blueprint $table) {
            $table->dropColumn([
                'sponsored_by',
                'sponsored_video_link'
            ]);
        });
    }

}
