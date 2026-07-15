<?php

namespace Database\Factories;

use App\Models\ReviewRubricGroup;
use App\Models\ReviewRubricItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewRubricItemFactory extends Factory
{
    protected $model = ReviewRubricItem::class;

    public function definition()
    {
        return [
            'review_rubric_group_id' => ReviewRubricGroup::factory(),
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'weight' => $this->faker->randomElement([1, 5, 10, 15, 20]),
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }
}
