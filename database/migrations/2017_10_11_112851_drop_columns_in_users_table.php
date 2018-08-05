<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropColumnsInUsersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn([
                'location',
                'address1',
                'address2',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('users', function(Blueprint $table) {
            $table->string('location', 100)->nullable()->after('password');
            $table->string('address1')->nullable()->after('latitude');
            $table->string('address2')->nullable()->after('address1');
        });
    }

}
