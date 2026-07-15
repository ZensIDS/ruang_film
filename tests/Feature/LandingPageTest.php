<?php

namespace Tests\Feature;

use App\Models\SubmissionSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_program_page_hides_competition_stat_and_limits_jury_cards()
    {
        SubmissionSetting::create([
            'name' => 'FFH 2026',
            'open_at' => now()->subDay(),
            'close_at' => now()->addDay(),
            'last_year_stat_film_submitted' => 321,
        ]);

        User::factory()->role('juri')->create(['name' => 'Alpha Jury']);
        User::factory()->role('juri')->create(['name' => 'Bravo Jury']);
        User::factory()->role('juri')->create(['name' => 'Charlie Jury']);
        User::factory()->role('juri')->create(['name' => 'Delta Jury']);

        $response = $this->get(route('landing.program'));

        $response->assertOk();
        $response->assertDontSee('data-target="321"', false);
        $response->assertDontSeeText('Film Submitted');
        $response->assertSeeText('ALPHA JURY');
        $response->assertSeeText('BRAVO JURY');
        $response->assertSeeText('CHARLIE JURY');
        $response->assertDontSeeText('DELTA JURY');
        $response->assertSeeText('Masih memiliki pertanyaan lain? Hubungi tim festival melalui kontak resmi kami.');
    }
}
