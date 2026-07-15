<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Film;
use App\Models\SubmissionSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FilmFactory extends Factory
{
    protected $model = Film::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'submission_setting_id' => SubmissionSetting::factory(),
            'category_id' => Category::factory(),
            'name' => $this->faker->sentence(3),
            'duration' => 900,
            'tahun_produksi' => (string) $this->faker->numberBetween(2023, 2026),
            'subtitle' => $this->faker->randomElement(['Ya', 'Tidak']),
            'sinopsis' => $this->faker->paragraph(),
            'sutradara' => $this->faker->name(),
            'produser' => $this->faker->name(),
            'penulis' => $this->faker->name(),
            'poster' => 'posters/test.jpg',
            'gsm' => json_encode(['gsm/test-1.jpg']),
            'trailer' => 'https://example.com/trailer',
            'film' => 'https://example.com/film',
            'kru' => 'kru/test.pdf',
            'other_1' => null,
            'other_2' => null,
            'status' => Film::CURATION_SUBMITTED,
            'curation_status' => Film::CURATION_SUBMITTED,
            'winner_rank' => null,
        ];
    }
}
