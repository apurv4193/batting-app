<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100)->nullable();
            $table->string('item_image')->nullable();
            $table->decimal('points', 8, 2);
            $table->tinyInteger('pre_contest_substitution')->default(0)->comment('The user can update roster detail till contest go to live.');
            $table->tinyInteger('contest_substitution')->default(0)->comment('The user can update roster detail till end of the contest.');
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
        Schema::dropIfExists('items');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

}
