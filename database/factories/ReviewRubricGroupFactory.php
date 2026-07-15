<?php

namespace Database\Factories;

use App\Models\ReviewRubric;
use App\Models\ReviewRubricGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewRubricGroupFactory extends Factory
{
    protected $model = ReviewRubricGroup::class;

    public function definition()
    {
        return [
            'review_rubric_id' => ReviewRubric::factory(),
            'title' => $this->faker->words(2, true),
            'weight' => $this->faker->randomElement([null, 10, 20, 30, 50]),
            'sort_order' => $this->faker->numberBetween(0, 5),
        ];
    }
}
