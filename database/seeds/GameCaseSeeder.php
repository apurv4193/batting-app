<?php

use Illuminate\Database\Seeder;

class GameCaseSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        \DB::statement("SET FOREIGN_KEY_CHECKS=0");

        \DB::table('gamecase')->truncate();

        \DB::table('gamecase')->insert(array(
            0 =>
            array(
                'id' => 1,
                'name' => 'Bronze Case',
                'slug' => 'bronze_case',
                'price' => 0,
                'items' => 3,
            ),
            1 =>
            array(
                'id' => 2,
                'name' => 'Silver Case',
                'slug' => 'silver_case',
                'price' => 0,
                'items' => 5,
            ),
            2 =>
            array(
                'id' => 3,
                'name' => 'Gold Case',
                'slug' => 'gold_case',
                'price' => 0,
                'items' => 7,
            ),
            3 =>
            array(
                'id' => 4,
                'name' => 'Rare Case',
                'slug' => 'rare_case',
                'price' => 0,
                'items' => 9,
            ),
            4 =>
            array(
                'id' => 5,
                'name' => 'Legendary Case',
                'slug' => 'legendary_case',
                'price' => 0,
                'items' => 11,
            )
        ));

        \DB::statement("SET FOREIGN_KEY_CHECKS=1");
    }

}
