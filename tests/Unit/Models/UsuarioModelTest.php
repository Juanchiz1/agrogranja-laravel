<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Cultivo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioModelTest extends TestCase
{
    /** @test */
    public function usuario_tiene_campos_ocultos_correctos(): void
    {
        $usuario = new Usuario();
        $this->assertContains('password', $usuario->getHidden());
    }

    /** @test */
    public function usuario_tiene_casts_correctos(): void
    {
        $usuario = new Usuario();
        $casts = $usuario->getCasts();

        $this->assertArrayHasKey('onboarding_completado', $casts);
        $this->assertEquals('boolean', $casts['onboarding_completado']);
    }

    /** @test */
    public function usuario_tiene_timestamps_personalizados(): void
    {
        $this->assertEquals('creado_en', Usuario::CREATED_AT);
        $this->assertEquals('actualizado_en', Usuario::UPDATED_AT);
    }

    /** @test */
    public function usuario_tiene_relacion_con_cultivos(): void
    {
        $usuarioId = DB::table('usuarios')->insertGetId([
            'nombre'                => 'Test Relaciones',
            'email'                 => 'rel@test.com',
            'password'              => Hash::make('clave'),
            'onboarding_completado' => true,
            'activo'                => true,
            'creado_en'             => now(),
            'actualizado_en'        => now(),
        ]);

        DB::table('cultivos')->insert([
            'usuario_id'    => $usuarioId,
            'tipo'          => 'Café',
            'nombre'        => 'Café Relación',
            'fecha_siembra' => now()->format('Y-m-d'),
            'area'          => 1.0,
            'unidad'        => 'hectareas',
            'estado'        => 'activo',
            'creado_en'     => now(),
            'actualizado_en'=> now(),
        ]);

        $usuario = Usuario::find($usuarioId);
        $this->assertCount(1, $usuario->cultivos);
        $this->assertEquals('Café Relación', $usuario->cultivos->first()->nombre);
    }

    /** @test */
    public function password_se_puede_verificar_con_hash(): void
    {
        $clave = 'miClave123';
        $hash  = Hash::make($clave);

        $this->assertTrue(Hash::check($clave, $hash));
        $this->assertFalse(Hash::check('otraClave', $hash));
    }
}