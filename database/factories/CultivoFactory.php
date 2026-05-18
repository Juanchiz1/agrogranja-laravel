<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CultivoFactory extends Factory
{
    protected $model = \App\Models\Cultivo::class;

    public function definition(): array
    {
        return [
            'usuario_id'   => \App\Models\Usuario::factory(),
            'tipo'         => fake()->randomElement(['Maíz', 'Yuca', 'Plátano', 'Café', 'Frijol']),
            'nombre'       => fake()->words(3, true),
            'fecha_siembra'=> fake()->date(),
            'area'         => fake()->randomFloat(2, 0.5, 10),
            'unidad'       => 'hectareas',
            'estado'       => 'activo',
            'notas'        => fake()->sentence(),
        ];
    }

    public function cosechado(): static
    {
        return $this->state(['estado' => 'cosechado']);
    }
}