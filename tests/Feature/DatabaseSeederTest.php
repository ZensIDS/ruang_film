<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Film;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ReviewRubric;
use App\Models\SubmissionSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_core_demo_data()
    {
        $this->seed();

        $this->assertDatabaseHas('users', ['email' => 'admin@gmail.com', 'role' => 'admin']);
        $this->assertDatabaseHas('users', ['email' => 'kurator@gmail.com', 'role' => 'kurator']);
        $this->assertDatabaseHas('users', ['email' => 'juri.umum@gmail.com', 'role' => 'juri']);
        $this->assertDatabaseHas('users', ['email' => 'peserta.umum@gmail.com', 'role' => 'peserta']);

        $this->assertDatabaseHas('categories', ['slug' => 'umum-nasional']);
        $this->assertDatabaseHas('program_categories', ['slug' => 'edukasi']);
        $this->assertDatabaseHas('programs', ['slug' => 'kelas-kritik-horor']);
        $this->assertDatabaseHas('merchandise_categories', ['slug' => 'apparel']);
        $this->assertDatabaseHas('merchandises', ['slug' => 'official-festival-t-shirt']);
        $this->assertDatabaseHas('expeditions', ['name' => 'JNE', 'service_name' => 'REG']);
        $this->assertDatabaseHas('bank_accounts', ['rek_bank_name' => 'BCA']);
        $this->assertDatabaseHas('review_rubric_items', ['title' => 'Rasa Takut Yang Bermakna']);

        $this->assertCount(3, SubmissionSetting::orderBy('open_at')->get());
        $this->assertCount(3, ProgramCategory::ordered()->get());
        $this->assertCount(15, Program::ordered()->get());
        $this->assertSame(20, Film::query()->whereNotNull('winner_rank')->count());
        $this->assertSame(8, ReviewRubric::query()->count());
        $this->assertTrue(SubmissionSetting::where('name', 'Periode Akhir 2026')->exists());
        $this->assertTrue(Category::where('slug', 'pelajar-jawa-timur')->exists());
        $this->assertTrue(User::where('email', 'peserta.pelajar@gmail.com')->exists());
        $this->assertDatabaseHas('films', ['winner_rank' => '1']);

        $currentPeriod = SubmissionSetting::where('name', 'Periode Awal 2026')->firstOrFail();

        $this->assertSame(3, Film::where('submission_setting_id', $currentPeriod->id)->where('curation_status', Film::CURATION_SUBMITTED)->count());
        $this->assertSame(3, Film::where('submission_setting_id', $currentPeriod->id)->where('curation_status', Film::CURATION_VERIFIED)->count());
        $this->assertSame(3, Film::where('submission_setting_id', $currentPeriod->id)->where('curation_status', Film::CURATION_UNDER_REVIEW)->count());
        $this->assertSame(3, Film::where('submission_setting_id', $currentPeriod->id)->where('curation_status', Film::CURATION_DETERMINATION)->count());
        $this->assertSame(3, Film::where('submission_setting_id', $currentPeriod->id)->where('curation_status', Film::CURATION_APPROVED)->count());
        $this->assertSame(3, Film::where('submission_setting_id', $currentPeriod->id)->where('curation_status', Film::CURATION_REJECTED)->count());
    }
}
