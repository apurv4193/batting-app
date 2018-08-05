<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnsContestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contests', function (Blueprint $table) {

            //Add columns
            $table->tinyInteger('image_uploaded')->comment('0:No, 1:Yes')->after('is_teamwise')->default(0);
            $table->tinyInteger('image_approved')->comment('0:No, 1:Yes')->after('image_uploaded')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contests', function (Blueprint $table) {

            $table->dropColumn('image_uploaded');
            $table->dropColumn('image_approved');
        });
    }
}
