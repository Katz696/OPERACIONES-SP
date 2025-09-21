<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            StandardInfoSeeder::class,  // el que ya tienes
            SampleDataSeeder::class,    // el nuevo
        ]);
    }
}
