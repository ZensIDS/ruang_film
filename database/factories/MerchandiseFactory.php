<?php

namespace Database\Factories;

use App\Models\Merchandise;
use App\Models\MerchandiseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MerchandiseFactory extends Factory
{
    protected $model = Merchandise::class;

    public function definition()
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'merchandise_category_id' => MerchandiseCategory::factory(),
            'name' => $name,
            'slug' => Str::slug($name) . '-' . strtolower(Str::random(4)),
            'image' => null,
            'price' => $this->faker->numberBetween(50000, 200000),
            'discount_price' => null,
            'weight' => $this->faker->numberBetween(100, 500),
            'qty_stock' => $this->faker->numberBetween(5, 20),
            'summary' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'is_active' => true,
        ];
    }
}
