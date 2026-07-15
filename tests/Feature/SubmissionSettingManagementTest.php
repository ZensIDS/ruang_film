<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Film;
use App\Models\SubmissionSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubmissionSettingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_period_inherits_current_landing_content()
    {
        $admin = User::factory()->role('admin')->create();
        $category = Category::factory()->create();
        $participant = User::factory()->role('peserta')->create([
            'category_id' => $category->id,
        ]);
        $currentSetting = SubmissionSetting::factory()->create([
            'name' => 'Periode Sekarang',
            'hero_title' => 'Hero Saat Ini',
            'about_title' => 'Tentang Saat Ini',
            'theme_name' => 'INDIGO',
            'festival_board' => [
                ['name' => 'Board Satu', 'title' => 'Ketua Festival', 'image' => 'landing/images/user.png'],
            ],
            'last_year_stat_film_submitted' => 285,
            'timeline_items' => [
                ['period' => '8 Juni - 6 Agustus 2026', 'title' => 'Open Submission', 'description' => 'Publikasi', 'icon' => 'fas fa-inbox'],
            ],
            'open_at' => now()->subDay(),
            'close_at' => now()->addDays(5),
        ]);
        $featuredFilm = Film::factory()->create([
            'user_id' => $participant->id,
            'category_id' => $category->id,
            'submission_setting_id' => $currentSetting->id,
        ]);
        $currentSetting->update([
            'last_year_featured_film_ids' => [$featuredFilm->id],
        ]);

        $this->actingAs($admin)
            ->post(route('settingStore'), [
                'name' => 'Periode Baru',
                'open_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
                'close_at' => now()->addDays(20)->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect(route('settingIndex'));

        $newSetting = SubmissionSetting::where('name', 'Periode Baru')->firstOrFail();

        $this->assertSame($currentSetting->hero_title, $newSetting->hero_title);
        $this->assertSame($currentSetting->about_title, $newSetting->about_title);
        $this->assertSame($currentSetting->theme_name, $newSetting->theme_name);
        $this->assertSame($currentSetting->festival_board, $newSetting->festival_board);
        $this->assertSame($currentSetting->last_year_featured_film_ids, $newSetting->last_year_featured_film_ids);
        $this->assertSame($currentSetting->last_year_stat_film_submitted, $newSetting->last_year_stat_film_submitted);
        $this->assertSame($currentSetting->timeline_items, $newSetting->timeline_items);
    }

    public function test_admin_can_update_landing_content_separately_from_period_dates()
    {
        Storage::fake('public');

        $admin = User::factory()->role('admin')->create();
        $category = Category::factory()->create();
        $participant = User::factory()->role('peserta')->create([
            'category_id' => $category->id,
        ]);
        $filmA = Film::factory()->create([
            'user_id' => $participant->id,
            'category_id' => $category->id,
            'name' => 'Film A',
        ]);
        $filmB = Film::factory()->create([
            'user_id' => $participant->id,
            'category_id' => $category->id,
            'name' => 'Film B',
        ]);
        $setting = SubmissionSetting::factory()->create([
            'name' => 'Periode 2026',
            'open_at' => now()->subDay(),
            'close_at' => now()->addDays(7),
        ]);
        $originalOpenAt = $setting->open_at->toDateTimeString();
        $originalCloseAt = $setting->close_at->toDateTimeString();

        $this->actingAs($admin)
            ->put(route('settingLandingUpdate', $setting), [
                'hero_title' => 'Hero Baru',
                'hero_description' => 'Deskripsi hero baru',
                'about_title' => 'Tentang Baru',
                'theme_title' => 'Tema Baru',
                'theme_name' => 'NOCTURNE',
                'festival_board' => [
                    ['name' => 'Anggota Satu', 'title' => 'Direktur'],
                    ['name' => 'Anggota Dua', 'title' => 'Programmer'],
                ],
                'last_year_title' => 'Highlight Baru',
                'last_year_catalog_label' => 'Lihat Katalog',
                'last_year_catalog_file' => UploadedFile::fake()->create('catalog.pdf', 120, 'application/pdf'),
                'last_year_featured_film_ids' => [$filmB->id, $filmA->id],
                'last_year_stat_film_submitted' => 320,
                'last_year_stat_special_films' => 64,
                'last_year_stat_audience' => 9000,
                'last_year_stat_participants' => 75,
                'timeline_items' => [
                    ['period' => '8 Juni - 6 Agustus 2026', 'title' => 'Open Submission', 'description' => 'Publikasi dan penjaringan karya.', 'icon' => 'fas fa-inbox'],
                    ['period' => '13 Agustus - 25 Agustus 2026', 'title' => 'Kurasi & Seleksi', 'description' => 'Kurasi karya terbaik.', 'icon' => 'fas fa-search'],
                ],
            ])
            ->assertRedirect(route('settingIndex'));

        $setting->refresh();

        $this->assertSame('Periode 2026', $setting->name);
        $this->assertSame($originalOpenAt, $setting->open_at->toDateTimeString());
        $this->assertSame($originalCloseAt, $setting->close_at->toDateTimeString());
        $this->assertSame('Hero Baru', $setting->hero_title);
        $this->assertSame('NOCTURNE', $setting->theme_name);
        $this->assertCount(2, $setting->festival_board);
        $this->assertSame('Anggota Dua', data_get($setting->festival_board, '1.name'));
        $this->assertSame([$filmB->id, $filmA->id], $setting->last_year_featured_film_ids);
        $this->assertSame(320, $setting->last_year_stat_film_submitted);
        $this->assertSame(64, $setting->last_year_stat_special_films);
        $this->assertSame(9000, $setting->last_year_stat_audience);
        $this->assertSame(75, $setting->last_year_stat_participants);
        $this->assertCount(2, $setting->timeline_items);
        $this->assertNotNull($setting->last_year_catalog_file);
        Storage::disk('public')->assertExists($setting->last_year_catalog_file);
    }
}
