<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    }

   
    protected function crearYLoguearUsuario(array $overrides = []): object
    {
        $data = array_merge([
            'nombre'                => 'Usuario Test',
            'email'                 => 'test@agrogranja.test',
            'password'              => Hash::make('password123'),
            'nombre_finca'          => 'Finca Test',
            'departamento'          => 'Antioquia',
            'municipio'             => 'Medellín',
            'onboarding_completado' => true,
            'activo'                => true,
            'creado_en'             => now(),
            'actualizado_en'        => now(),
        ], $overrides);

        $id = DB::table('usuarios')->insertGetId($data);
        $usuario = DB::table('usuarios')->find($id);

        // Simular la sesión manual que usa el proyecto
        session(['usuario_id' => $id, 'usuario_nombre' => $usuario->nombre]);

        return $usuario;
    }

   
    protected function crearUsuario(array $overrides = []): object
    {
        $data = array_merge([
            'nombre'                => 'Usuario Sin Sesión',
            'email'                 => 'sinsesion@agrogranja.test',
            'password'              => Hash::make('password123'),
            'nombre_finca'          => 'Finca B',
            'onboarding_completado' => true,
            'activo'                => true,
            'creado_en'             => now(),
            'actualizado_en'        => now(),
        ], $overrides);

        $id = DB::table('usuarios')->insertGetId($data);
        return DB::table('usuarios')->find($id);
    }
}