<?php

use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        \DB::statement("SET FOREIGN_KEY_CHECKS=0");

        // \DB::table('users')->truncate();

        \DB::table('users')->insert(array(
            0 =>
            array(
                'id' => 1,
                'name' => 'Betting App Admin',
                'username' => 'inx_betting_app',
                'email' => 'bettingapp@inexture.in',
                'password' => bcrypt('appadmin'),
                'is_admin' => 1,
                'created_at' => '2017-08-03 17:04:00',
                'updated_at' => '2017-08-03 17:04:00',
                'deleted_at' => NULL,
            ),
        ));

        \DB::statement("SET FOREIGN_KEY_CHECKS=1");
    }

}
