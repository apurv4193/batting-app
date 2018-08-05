<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('username', 50)->unique()->nullable();
            $table->string('email')->unique();
            $table->string('phone', 15)->nullable();
            $table->date('dob')->nullable();
            $table->string('password');
            $table->string('location', 100)->nullable();
            $table->float('longitude', 10, 6)->nullable();
            $table->float('latitude', 10, 6)->nullable();
            $table->string('user_pic', 255)->nullable();
            $table->tinyInteger('gender')->comment('1:Male, 2:Female, 3:Other')->nullable();
            $table->bigInteger('points')->unsigned()->nullable();
            $table->bigInteger('roster_app_amount')->unsigned()->nullable();
            $table->decimal('funds', 8, 2)->nullable();
            $table->tinyInteger('is_admin')->comment('1:Admin, 0:Normal')->default(0);
            $table->rememberToken();
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
        Schema::dropIfExists('users');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

}
