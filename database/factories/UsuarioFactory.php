<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UsuarioFactory extends Factory
{
    protected $model = \App\Models\Usuario::class;

    public function definition(): array
    {
        return [
            'nombre'                => fake()->name(),
            'email'                 => fake()->unique()->safeEmail(),
            'password'              => Hash::make('password'),
            'nombre_finca'          => fake()->company() . ' Farm',
            'departamento'          => fake()->randomElement(['Antioquia', 'Cundinamarca', 'Valle del Cauca', 'Córdoba']),
            'municipio'             => fake()->city(),
            'onboarding_completado' => true,
            'activo'                => true,
        ];
    }

    /** Estado: usuario que no ha completado onboarding */
    public function sinOnboarding(): static
    {
        return $this->state(['onboarding_completado' => false]);
    }
}