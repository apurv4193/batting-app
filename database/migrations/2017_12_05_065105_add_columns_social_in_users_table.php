<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsSocialInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('social_type',['0','1','2'])->default('0')->comment('0=>App,1=>Gmail,2=>Facebook')->after('remember_token')->nullable();
            $table->string('social_id',255)->after('social_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('social_type');
            $table->dropColumn('social_id');
        });
    }
}
