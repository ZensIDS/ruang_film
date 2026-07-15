<?php

namespace Tests\Feature;

use App\Models\ProgramCategory;
use Database\Seeders\ProgramCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramCategorySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_program_category_seeder_creates_default_program_categories()
    {
        $this->seed(ProgramCategorySeeder::class);

        $this->assertCount(3, ProgramCategory::ordered()->get());
        $this->assertDatabaseHas('program_categories', [
            'slug' => 'edukasi',
            'description' => 'Workshop, Diskusi, dan Pengembangan Talenta.',
        ]);
        $this->assertDatabaseHas('program_categories', [
            'slug' => 'eksperiens',
            'description' => 'Pengalaman Imersif Berbasis Budaya & Ruang.',
        ]);
        $this->assertDatabaseHas('program_categories', [
            'slug' => 'ekosistem',
            'description' => 'Kolaborasi, Jaringan, dan Keberlanjutan Industri.',
        ]);
    }
}
