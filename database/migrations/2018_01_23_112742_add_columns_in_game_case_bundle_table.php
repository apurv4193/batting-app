<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsInGameCaseBundleTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('gamecase_bundle', function (Blueprint $table) {
            $table->string('gamecase_slug', 255)->after('name')->nullable()->comment('From this we can identify that bundle created for this game case');
            $table->integer('size')->after('gamecase_image')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('gamecase_bundle', function (Blueprint $table) {
            $table->dropColumn([
                'gamecase_slug',
                'size'
            ]);
        });
    }

}
