<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MahasiswaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nrp' => $this->faker->unique()->numerify('1604#####'), // Contoh format NRP 9 digit
            'nama_lengkap' => $this->faker->name(),
        ];
    }
}