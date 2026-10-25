<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class LoginTest extends TestCase
{
    /** @test */
    public function muestra_formulario_de_login(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('Iniciar sesión', false);
    }

    /** @test */
    public function usuario_puede_iniciar_sesion_con_credenciales_correctas(): void
    {
        DB::table('usuarios')->insert([
            'nombre'                => 'Juan Test',
            'email'                 => 'juan@test.com',
            'password'              => Hash::make('clave123'),
            'onboarding_completado' => true,
            'activo'                => true,
            'creado_en'             => now(),
            'actualizado_en'        => now(),
        ]);

        $response = $this->post(route('login'), [
            'email'    => 'juan@test.com',
            'password' => 'clave123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertEquals('juan@test.com', DB::table('usuarios')->where('email', 'juan@test.com')->value('email'));
    }

    /** @test */
    public function falla_login_con_contrasena_incorrecta(): void
    {
        DB::table('usuarios')->insert([
            'nombre'                => 'Carlos Test',
            'email'                 => 'carlos@test.com',
            'password'              => Hash::make('correcta'),
            'onboarding_completado' => true,
            'activo'                => true,
            'creado_en'             => now(),
            'actualizado_en'        => now(),
        ]);

        $response = $this->post(route('login'), [
            'email'    => 'carlos@test.com',
            'password' => 'incorrecta',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function falla_login_sin_email(): void
    {
        $response = $this->post(route('login'), [
            'email'    => '',
            'password' => 'algo',
        ]);
        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function usuario_inactivo_no_puede_iniciar_sesion(): void
    {
        DB::table('usuarios')->insert([
            'nombre'                => 'Inactivo Test',
            'email'                 => 'inactivo@test.com',
            'password'              => Hash::make('clave123'),
            'onboarding_completado' => true,
            'activo'                => false,  // ← inactivo
            'creado_en'             => now(),
            'actualizado_en'        => now(),
        ]);

        $response = $this->post(route('login'), [
            'email'    => 'inactivo@test.com',
            'password' => 'clave123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function usuario_sin_onboarding_es_redirigido_al_onboarding(): void
    {
        DB::table('usuarios')->insert([
            'nombre'                => 'Nuevo Test',
            'email'                 => 'nuevo@test.com',
            'password'              => Hash::make('clave123'),
            'onboarding_completado' => false,
            'activo'                => true,
            'creado_en'             => now(),
            'actualizado_en'        => now(),
        ]);

        $response = $this->post(route('login'), [
            'email'    => 'nuevo@test.com',
            'password' => 'clave123',
        ]);

        $response->assertRedirect(route('onboarding'));
    }
}