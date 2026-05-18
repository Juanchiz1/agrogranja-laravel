<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TareaFactory extends Factory
{
    protected $model = \App\Models\Tarea::class;

    public function definition(): array
    {
        return [
            'usuario_id' => \App\Models\Usuario::factory(),
            'titulo'     => fake()->sentence(4),
            'tipo'       => fake()->randomElement(['riego', 'fertilizacion', 'cosecha', 'vacunacion']),
            'fecha'      => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'prioridad'  => fake()->randomElement(['alta', 'media', 'baja']),
            'completada' => false,
        ];
    }
}