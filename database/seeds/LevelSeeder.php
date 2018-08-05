<?php

use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        \DB::statement("SET FOREIGN_KEY_CHECKS=0");

        \DB::table('level')->truncate();

        \DB::table('level')->insert(array(
            0 =>
            array(
                'id' => 1,
                'name' => 'BEGINNER',
            ),
            1 =>
            array(
                'id' => 2,
                'name' => 'INTERMEDIATE',
            ),
            2 =>
            array(
                'id' => 3,
                'name' => 'ADVANCE',
            ),
            3 =>
            array(
                'id' => 4,
                'name' => 'EXTREME',
            ),
        ));

        \DB::statement("SET FOREIGN_KEY_CHECKS=1");
    }

}
