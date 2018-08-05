<?php

use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        \DB::statement("SET FOREIGN_KEY_CHECKS=0");

        \DB::table('items')->truncate();

        \DB::table('items')->insert(array(
            0 =>
            array(
                'id' => 1,
                'name' => 'Bronze Token',
                'slug' => 'bronze_token',
                'points' => .05,
                'pre_contest_substitution' => 0,
                'contest_substitution' => 0
            ),
            1 =>
            array(
                'id' => 2,
                'name' => 'Silver Token',
                'slug' => 'silver_token',
                'points' => .25,
                'pre_contest_substitution' => 0,
                'contest_substitution' => 0
            ),
            2 =>
            array(
                'id' => 3,
                'name' => 'Gold Token',
                'slug' => 'gold_token',
                'points' => .5,
                'pre_contest_substitution' => 1,
                'contest_substitution' => 0
            ),
            3 =>
            array(
                'id' => 4,
                'name' => 'Rare Token',
                'slug' => 'rare_token',
                'points' => 1,
                'pre_contest_substitution' => 0,
                'contest_substitution' => 1
            ),
            4 =>
            array(
                'id' => 5,
                'name' => 'Legendary Token',
                'slug' => 'legendary_token',
                'points' => 2,
                'pre_contest_substitution' => 1,
                'contest_substitution' => 1
            )
        ));

        \DB::statement("SET FOREIGN_KEY_CHECKS=1");
    }

}
