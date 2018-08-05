<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnsGamecaseItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gamecase_items', function (Blueprint $table) {
            //Add columns
            $table->integer('alternate_item_id')->unsigned()->nullable()->after('possibility');
            $table->decimal('alternate_possibility',8, 2)->unsigned()->nullable()->after('alternate_item_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gamecase_items', function (Blueprint $table) {
            $table->dropColumn(['alternate_item_id','alternate_possibility']);
        });
    }
}
