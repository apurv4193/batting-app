<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $this->call(AdminUserSeeder::class);
        $this->command->info('Users table seeded!');
        $this->call(ContestTypeSeeder::class);
        $this->command->info('Contest type table seeded!');
        $this->call(LevelSeeder::class);
        $this->command->info('Level table seeded!');
        $this->call(ItemSeeder::class);
        $this->command->info('Item table seeded!');
        $this->call(GameCaseSeeder::class);
        $this->command->info('Game Case table seeded!');
    }

}
