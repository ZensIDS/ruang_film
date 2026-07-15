<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            ProgramCategorySeeder::class,
            ProgramSeeder::class,
            CategorySeeder::class,
            SubmissionSettingSeeder::class,
            AppSettingSeeder::class,
            UserSeeder::class,
            CommerceSeeder::class,
            RubrikPenilaianSeeder::class,
            FilmSeeder::class,
        ]);
    }
}
