<?php

namespace Database\Factories;

use App\Models\Film;
use App\Models\JuryScore;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class JuryScoreFactory extends Factory
{
    protected $model = JuryScore::class;

    public function definition()
    {
        return [
            'film_id' => Film::factory(),
            'jury_id' => User::factory()->role('juri'),
            'score' => $this->faker->randomFloat(2, 60, 100),
            'note' => $this->faker->sentence(),
        ];
    }
}
