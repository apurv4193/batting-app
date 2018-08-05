<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGamecaseBundleTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('gamecase_bundle', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->string('gamecase_ids');
            $table->decimal('price', 8, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::dropIfExists('gamecase_bundle');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

}
