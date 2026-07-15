<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProgramManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_program_categories_and_programs()
    {
        Storage::fake('public');

        $admin = User::factory()->role('admin')->create();

        $this->actingAs($admin)
            ->post(route('program-categories.store'), [
                'name' => 'Edukasi',
                'slug' => '',
                'description' => 'Workshop dan diskusi.',
                'sort_order' => 1,
                'is_active' => 1,
            ])
            ->assertRedirect(route('program-categories.index'));

        $category = ProgramCategory::first();

        $this->assertDatabaseHas('program_categories', [
            'id' => $category->id,
            'name' => 'Edukasi',
            'slug' => 'edukasi',
        ]);

        $this->actingAs($admin)
            ->post(route('admin-programs.store'), [
                'program_category_id' => $category->id,
                'title' => 'Ruang Cerita',
                'slug' => '',
                'summary' => 'Ringkasan program.',
                'content' => '<p>Konten detail.</p>',
                'poster' => UploadedFile::fake()->image('ruang-cerita.jpg'),
                'sort_order' => 2,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin-programs.index'));

        $program = Program::first();
        $oldPoster = $program->poster;

        $this->assertDatabaseHas('programs', [
            'id' => $program->id,
            'title' => 'Ruang Cerita',
            'slug' => 'ruang-cerita',
        ]);
        Storage::disk('public')->assertExists($program->poster);

        $secondCategory = ProgramCategory::factory()->create([
            'name' => 'Ekosistem',
            'slug' => 'ekosistem',
        ]);

        $this->actingAs($admin)
            ->put(route('admin-programs.update', $program), [
                'program_category_id' => $secondCategory->id,
                'title' => 'Ruang Cerita Baru',
                'slug' => '',
                'summary' => 'Ringkasan yang diperbarui.',
                'content' => '<p>Konten baru.</p>',
                'poster' => UploadedFile::fake()->image('ruang-cerita-baru.jpg'),
                'sort_order' => 3,
            ])
            ->assertRedirect(route('admin-programs.index'));

        $program = $program->fresh();

        $this->assertDatabaseHas('programs', [
            'id' => $program->id,
            'program_category_id' => $secondCategory->id,
            'title' => 'Ruang Cerita Baru',
            'slug' => 'ruang-cerita-baru',
            'is_active' => 0,
        ]);
        Storage::disk('public')->assertMissing($oldPoster);
        Storage::disk('public')->assertExists($program->poster);

        $latestPoster = $program->poster;

        $this->actingAs($admin)
            ->delete(route('admin-programs.destroy', $program))
            ->assertRedirect(route('admin-programs.index'));

        $this->assertDatabaseMissing('programs', ['id' => $program->id]);
        Storage::disk('public')->assertMissing($latestPoster);

        $categoryToDelete = ProgramCategory::factory()->create([
            'name' => 'Eksperiens',
            'slug' => 'eksperiens',
        ]);
        $posterPath = UploadedFile::fake()->image('legacy-program.jpg')->store('programs', 'public');
        $programInCategory = Program::factory()->create([
            'program_category_id' => $categoryToDelete->id,
            'poster' => $posterPath,
        ]);

        $this->actingAs($admin)
            ->delete(route('program-categories.destroy', $categoryToDelete))
            ->assertRedirect(route('program-categories.index'));

        $this->assertDatabaseMissing('program_categories', ['id' => $categoryToDelete->id]);
        $this->assertDatabaseMissing('programs', ['id' => $programInCategory->id]);
        Storage::disk('public')->assertMissing($posterPath);
    }
}
