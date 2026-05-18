<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GastoFactory extends Factory
{
    protected $model = \App\Models\Gasto::class;

    public function definition(): array
    {
        return [
            'usuario_id' => \App\Models\Usuario::factory(),
            'categoria'  => fake()->randomElement(['Semillas', 'Fertilizantes', 'Mano de obra', 'Transporte']),
            'descripcion'=> fake()->sentence(),
            'cantidad'   => fake()->numberBetween(1, 20),
            'valor'      => fake()->numberBetween(10000, 500000),
            'fecha'      => fake()->date(),
        ];
    }
}