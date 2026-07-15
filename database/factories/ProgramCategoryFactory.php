<?php

namespace Database\Factories;

use App\Models\ProgramCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProgramCategoryFactory extends Factory
{
    protected $model = ProgramCategory::class;

    public function definition()
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'sort_order' => $this->faker->numberBetween(0, 10),
            'is_active' => true,
        ];
    }
}
