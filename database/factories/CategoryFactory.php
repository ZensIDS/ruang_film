<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition()
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'landing_summary' => $this->faker->paragraph(),
            'image' => null,
            'detail_route' => '/program',
            'sort_order' => $this->faker->numberBetween(0, 10),
            'is_active' => true,
        ];
    }
}
