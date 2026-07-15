<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\ReviewRubric;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewRubricFactory extends Factory
{
    protected $model = ReviewRubric::class;

    public function definition()
    {
        $stage = $this->faker->randomElement(ReviewRubric::stages());

        return [
            'category_id' => Category::factory(),
            'stage' => $stage,
            'name' => 'Rubrik ' . ucfirst($stage),
            'is_active' => true,
        ];
    }

    public function curation()
    {
        return $this->state(function () {
            return [
                'stage' => ReviewRubric::STAGE_CURATION,
                'name' => 'Rubrik Kurasi',
            ];
        });
    }

    public function jury()
    {
        return $this->state(function () {
            return [
                'stage' => ReviewRubric::STAGE_JURY,
                'name' => 'Rubrik Penjurian',
            ];
        });
    }
}
