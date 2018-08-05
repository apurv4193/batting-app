<?php

use Illuminate\Database\Seeder;

class ContestTypeSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        \DB::statement("SET FOREIGN_KEY_CHECKS=0");

        \DB::table('contest_type')->truncate();

        \DB::table('contest_type')->insert(array(
            0 =>
            array(
                'id' => 1,
                'type' => '1V1',
            ),
            1 =>
            array(
                'id' => 2,
                'type' => '2V2',
            ),
            2 =>
            array(
                'id' => 3,
                'type' => '4V4',
            ),
            3 =>
            array(
                'id' => 4,
                'type' => '6V6',
            ),
        ));

        \DB::statement("SET FOREIGN_KEY_CHECKS=1");
    }

}
