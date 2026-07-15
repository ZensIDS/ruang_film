<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\ProgramCategory;
use Database\Seeders\ProgramSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_program_seeder_creates_five_programs_per_default_category()
    {
        $this->seed(ProgramSeeder::class);

        $this->assertCount(3, ProgramCategory::ordered()->get());
        $this->assertCount(15, Program::ordered()->get());

        $this->assertEquals(5, Program::whereHas('category', function ($query) {
            $query->where('slug', 'edukasi');
        })->count());

        $this->assertEquals(5, Program::whereHas('category', function ($query) {
            $query->where('slug', 'eksperiens');
        })->count());

        $this->assertEquals(5, Program::whereHas('category', function ($query) {
            $query->where('slug', 'ekosistem');
        })->count());

        $this->assertDatabaseHas('programs', [
            'slug' => 'kelas-kritik-horor',
            'poster' => 'landing/images/Fest.jpg',
        ]);

        $this->assertDatabaseHas('programs', [
            'slug' => 'instalasi-horor-immersive',
            'poster' => 'landing/images/wisata/wisata5.jpg',
        ]);

        $this->assertDatabaseHas('programs', [
            'slug' => 'ruang-keberlanjutan-festival',
            'poster' => 'landing/images/collab/col5.png',
        ]);
    }
}
