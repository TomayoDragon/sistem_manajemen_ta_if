<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StaffFactory extends Factory
{
    public function definition(): array
    {
        return [
            'npk' => $this->faker->unique()->numerify('211######'), // Contoh format NPK 8 digit
            'nama_lengkap' => $this->faker->name(),
        ];
    }
}