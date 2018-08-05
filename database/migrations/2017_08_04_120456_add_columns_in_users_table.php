<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsInUsersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('users', function(Blueprint $table) {
            $table->string('address1')->nullable()->after('latitude');
            $table->string('address2')->nullable()->after('address1');
            $table->string('zip_code')->nullable()->after('address2');
            $table->string('city')->nullable()->after('zip_code');
            $table->string('state')->nullable()->after('city');
            $table->string('country')->nullable()->after('state');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn(['address1',
                'address2',
                'zip_code',
                'city',
                'state',
                'country'
            ]);
        });
    }

}
