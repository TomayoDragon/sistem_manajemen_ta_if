<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DosenFactory extends Factory
{
    public function definition(): array
    {
        return [
            'npk' => $this->faker->unique()->numerify('111######'), // Contoh format NPK 8 digit
            'nama_lengkap' => $this->faker->name(),
        ];
    }
}