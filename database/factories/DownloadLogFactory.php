<?php

namespace Database\Factories;

use App\Models\DownloadLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DownloadLogFactory extends Factory
{
    protected $model = DownloadLog::class;

    public function definition()
    {
        return [
            'file' => 'downloads/' . $this->faker->slug() . '.pdf',
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'user_id' => User::factory(),
        ];
    }
}
