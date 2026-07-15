<?php

namespace Database\Seeders;

use App\Models\ProgramCategory;
use Illuminate\Database\Seeder;

class ProgramCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        collect([
            [
                'name' => 'Edukasi',
                'slug' => 'edukasi',
                'description' => 'Workshop, Diskusi, dan Pengembangan Talenta.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Eksperiens',
                'slug' => 'eksperiens',
                'description' => 'Pengalaman Imersif Berbasis Budaya & Ruang.',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Ekosistem',
                'slug' => 'ekosistem',
                'description' => 'Kolaborasi, Jaringan, dan Keberlanjutan Industri.',
                'sort_order' => 3,
                'is_active' => true,
            ],
        ])->each(function ($category) {
            ProgramCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        });
    }
}
