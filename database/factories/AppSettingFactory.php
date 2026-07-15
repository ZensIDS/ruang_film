<?php

namespace Database\Factories;

use App\Models\AppSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppSettingFactory extends Factory
{
    protected $model = AppSetting::class;

    public function definition()
    {
        return [
            'key' => $this->faker->unique()->slug(),
            'value' => $this->faker->word(),
        ];
    }
}
