<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class RegisterTest extends TestCase
{
    /** @test */
    public function muestra_formulario_de_registro(): void
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);
    }

    /** @test */
    public function usuario_puede_registrarse(): void
    {
        $response = $this->post(route('register'), [
            'nombre'   => 'María Granjera',
            'email'    => 'maria@granja.com',
            'password' => 'segura123',
        ]);

        $this->assertDatabaseHas('usuarios', ['email' => 'maria@granja.com']);
        // El registro redirige a onboarding
        $response->assertRedirect();
    }

    /** @test */
    public function no_permite_registro_con_email_duplicado(): void
    {
        DB::table('usuarios')->insert([
            'nombre'   => 'Existente',
            'email'    => 'existe@test.com',
            'password' => bcrypt('clave'),
            'creado_en'    => now(),
            'actualizado_en' => now(),
        ]);

        $response = $this->post(route('register'), [
            'nombre'   => 'Otro',
            'email'    => 'existe@test.com',
            'password' => 'otraclave',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function registro_requiere_nombre_minimo_2_caracteres(): void
    {
        $response = $this->post(route('register'), [
            'nombre'   => 'A',
            'email'    => 'valido@test.com',
            'password' => 'clave123',
        ]);

        $response->assertSessionHasErrors(['nombre']);
    }

    /** @test */
    public function registro_requiere_password_minimo_6_caracteres(): void
    {
        $response = $this->post(route('register'), [
            'nombre'   => 'Pedro',
            'email'    => 'pedro@test.com',
            'password' => '123',
        ]);

        $response->assertSessionHasErrors(['password']);
    }
}