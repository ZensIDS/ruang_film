<?php

namespace Database\Factories;

use App\Models\Program;
use App\Models\ProgramCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProgramFactory extends Factory
{
    protected $model = Program::class;

    public function definition()
    {
        $title = $this->faker->unique()->sentence(3);

        return [
            'program_category_id' => ProgramCategory::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'summary' => $this->faker->paragraph(),
            'content' => '<p>' . $this->faker->paragraph() . '</p>',
            'poster' => null,
            'sort_order' => $this->faker->numberBetween(0, 10),
            'is_active' => true,
        ];
    }
}
