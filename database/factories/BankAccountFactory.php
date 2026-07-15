<?php

namespace Database\Factories;

use App\Models\BankAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankAccountFactory extends Factory
{
    protected $model = BankAccount::class;

    public function definition()
    {
        return [
            'rek_name' => $this->faker->name(),
            'rek_bank_name' => $this->faker->randomElement(['BCA', 'BRI', 'Mandiri']),
            'rek_bank_no' => $this->faker->bankAccountNumber(),
            'is_active' => true,
        ];
    }
}
