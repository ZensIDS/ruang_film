<?php

namespace Database\Factories;

use App\Models\SubmissionSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubmissionSettingFactory extends Factory
{
    protected $model = SubmissionSetting::class;

    public function definition()
    {
        $openAt = now()->subDay();

        return [
            'name' => 'Submission ' . $this->faker->monthName() . ' ' . now()->year,
            'hero_title' => "FESTIVAL FILM\nHOROR " . now()->year,
            'hero_description' => $this->faker->sentence(),
            'about_title' => 'Festival Film Horor ' . now()->year,
            'about_description' => $this->faker->paragraph(),
            'about_description_secondary' => $this->faker->paragraph(),
            'hashtag' => '#FestivalFilmHoror' . now()->year,
            'theme_title' => 'Tema Festival Film Horor ' . now()->year,
            'theme_name' => strtoupper($this->faker->word()),
            'theme_quote' => $this->faker->sentence(),
            'theme_description' => $this->faker->paragraph(3, true),
            'festival_board' => [],
            'last_year_title' => $this->faker->sentence(3),
            'last_year_description' => $this->faker->paragraph(),
            'last_year_catalog_label' => 'Download Katalog Festival',
            'last_year_catalog_url' => '/download/ekatalog',
            'last_year_featured_film_ids' => [],
            'last_year_catalog_file' => null,
            'last_year_stat_film_submitted' => 285,
            'last_year_stat_special_films' => 60,
            'last_year_stat_audience' => 8000,
            'last_year_stat_participants' => 62,
            'timeline_items' => SubmissionSetting::defaultTimelineItems(
                $openAt,
                (clone $openAt)->addDays(7),
                'Submission ' . now()->year
            ),
            'open_at' => $openAt,
            'close_at' => (clone $openAt)->addDays(7),
        ];
    }
}
