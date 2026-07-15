<?php

namespace Database\Factories;

use App\Models\MerchandiseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MerchandiseCategoryFactory extends Factory
{
    protected $model = MerchandiseCategory::class;

    public function definition()
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . strtolower(Str::random(4)),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
