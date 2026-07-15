<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserDetailFactory extends Factory
{
    protected $model = UserDetail::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'community_name' => $this->faker->company(),
            'provinsi_code' => '35',
            'provinsi_name' => 'Jawa Timur',
            'kabupaten_code' => '3501',
            'kabupaten_name' => 'Pacitan',
            'kecamatan_code' => '3501010',
            'kecamatan_name' => 'Pacitan',
            'desa_code' => '3501010001',
            'desa_name' => 'Sidoharjo',
            'username_ig' => $this->faker->userName(),
            'posisi' => 'Perwakilan',
            'alamat_lengkap' => $this->faker->address(),
            'tanggal_lahir' => $this->faker->date(),
        ];
    }
}
