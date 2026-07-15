<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\ProgramCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_program_catalog_filters_and_paginates_by_category()
    {
        $edukasi = ProgramCategory::factory()->create([
            'name' => 'Edukasi',
            'slug' => 'edukasi',
        ]);
        $eksperiens = ProgramCategory::factory()->create([
            'name' => 'Eksperiens',
            'slug' => 'eksperiens',
        ]);

        Program::factory()->count(12)->sequence(
            fn ($sequence) => [
                'program_category_id' => $edukasi->id,
                'title' => sprintf('Program %02d', $sequence->index + 1),
                'slug' => sprintf('program-%02d', $sequence->index + 1),
                'sort_order' => $sequence->index + 1,
            ]
        )->create();

        Program::factory()->create([
            'program_category_id' => $edukasi->id,
            'title' => 'Program 13',
            'slug' => 'program-13',
            'sort_order' => 13,
        ]);

        Program::factory()->create([
            'program_category_id' => $eksperiens->id,
            'title' => 'Eksperiens Malam',
            'slug' => 'eksperiens-malam',
        ]);

        $this->get('/programs?category=edukasi')
            ->assertOk()
            ->assertSee('Program 01')
            ->assertDontSee('Eksperiens Malam')
            ->assertDontSee('Program 13');

        $this->get('/programs?category=edukasi&page=2')
            ->assertOk()
            ->assertSee('Program 13')
            ->assertDontSee('Eksperiens Malam');
    }

    public function test_program_detail_renders_summary_poster_and_trusted_html_content()
    {
        $category = ProgramCategory::factory()->create([
            'name' => 'Ekosistem',
            'slug' => 'ekosistem',
        ]);

        $program = Program::factory()->create([
            'program_category_id' => $category->id,
            'title' => 'Ruang Kolaborasi',
            'slug' => 'ruang-kolaborasi',
            'summary' => 'Program kolaborasi lintas komunitas.',
            'content' => '<p><strong>Rich Program</strong> content.</p><ul><li>Poin penting</li></ul>',
            'poster' => 'programs/poster-utama.jpg',
        ]);

        $this->get(route('programs.show', ['program' => $program->slug]))
            ->assertOk()
            ->assertSee('Ruang Kolaborasi')
            ->assertSee('Program kolaborasi lintas komunitas.')
            ->assertSee('storage/programs/poster-utama.jpg')
            ->assertSee('<strong>Rich Program</strong>', false)
            ->assertSee('<li>Poin penting</li>', false);
    }

    public function test_inactive_programs_or_categories_are_hidden_from_public_pages()
    {
        $inactiveCategory = ProgramCategory::factory()->create([
            'name' => 'Kategori Rahasia',
            'slug' => 'kategori-rahasia',
            'is_active' => false,
        ]);
        $activeCategory = ProgramCategory::factory()->create([
            'name' => 'Edukasi',
            'slug' => 'edukasi',
        ]);

        $programInInactiveCategory = Program::factory()->create([
            'program_category_id' => $inactiveCategory->id,
            'title' => 'Program Rahasia',
            'slug' => 'program-rahasia',
        ]);
        $inactiveProgram = Program::factory()->create([
            'program_category_id' => $activeCategory->id,
            'title' => 'Program Nonaktif',
            'slug' => 'program-nonaktif',
            'is_active' => false,
        ]);

        $this->get('/program')
            ->assertOk()
            ->assertDontSee('Kategori Rahasia')
            ->assertDontSee('Program Rahasia')
            ->assertDontSee('Program Nonaktif');

        $this->get('/programs')
            ->assertOk()
            ->assertDontSee('Program Rahasia')
            ->assertDontSee('Program Nonaktif');

        $this->get(route('programs.show', ['program' => $programInInactiveCategory->slug]))
            ->assertNotFound();

        $this->get(route('programs.show', ['program' => $inactiveProgram->slug]))
            ->assertNotFound();
    }
}
