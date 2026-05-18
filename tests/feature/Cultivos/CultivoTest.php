<?php

namespace Tests\Feature\Cultivos;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class CultivoTest extends TestCase
{
    /** @test */
    public function lista_cultivos_requiere_sesion_activa(): void
    {
        $response = $this->get(route('cultivos.index'));
        // Sin sesión debe redirigir al login
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function usuario_logueado_puede_ver_lista_de_cultivos(): void
    {
        $usuario = $this->crearYLoguearUsuario();

        $response = $this->get(route('cultivos.index'));
        $response->assertStatus(200);
    }

    /** @test */
    public function puede_crear_un_cultivo(): void
    {
        $usuario = $this->crearYLoguearUsuario();

        $response = $this->post(route('cultivos.store'), [
            'tipo'          => 'Maíz',
            'nombre'        => 'Maíz Lote Norte',
            'fecha_siembra' => now()->format('Y-m-d'),
            'area'          => 2.5,
            'unidad'        => 'hectareas',
            'estado'        => 'activo',
            'notas'         => 'Primera siembra del año',
        ]);

        $this->assertDatabaseHas('cultivos', [
            'usuario_id' => $usuario->id,
            'tipo'       => 'Maíz',
            'nombre'     => 'Maíz Lote Norte',
        ]);

        $response->assertRedirect();
    }

    /** @test */
    public function crear_cultivo_requiere_campos_obligatorios(): void
    {
        $this->crearYLoguearUsuario();

        $response = $this->post(route('cultivos.store'), [
            'tipo'  => '',  // Falta tipo
            'nombre'=> '',  // Falta nombre
        ]);

        $response->assertSessionHasErrors(['tipo', 'nombre']);
    }

    /** @test */
    public function usuario_solo_ve_sus_propios_cultivos(): void
    {
        $usuario1 = $this->crearYLoguearUsuario(['email' => 'u1@test.com']);
        DB::table('cultivos')->insert([
            'usuario_id'    => $usuario1->id,
            'tipo'          => 'Yuca',
            'nombre'        => 'Cultivo de u1',
            'fecha_siembra' => now()->format('Y-m-d'),
            'area'          => 1.0,
            'unidad'        => 'hectareas',
            'estado'        => 'activo',
            'creado_en'     => now(),
            'actualizado_en'=> now(),
        ]);

        $usuario2 = $this->crearUsuario(['email' => 'u2@test.com']);
        DB::table('cultivos')->insert([
            'usuario_id'    => $usuario2->id,
            'tipo'          => 'Café',
            'nombre'        => 'Cultivo de u2',
            'fecha_siembra' => now()->format('Y-m-d'),
            'area'          => 0.5,
            'unidad'        => 'hectareas',
            'estado'        => 'activo',
            'creado_en'     => now(),
            'actualizado_en'=> now(),
        ]);

        $response = $this->get(route('cultivos.index'));
        $response->assertSee('Cultivo de u1');
        $response->assertDontSee('Cultivo de u2');
    }

    /** @test */
    public function puede_actualizar_un_cultivo(): void
    {
        $usuario = $this->crearYLoguearUsuario();

        $cultivoId = DB::table('cultivos')->insertGetId([
            'usuario_id'    => $usuario->id,
            'tipo'          => 'Plátano',
            'nombre'        => 'Lote Original',
            'fecha_siembra' => now()->format('Y-m-d'),
            'area'          => 1.0,
            'unidad'        => 'hectareas',
            'estado'        => 'activo',
            'creado_en'     => now(),
            'actualizado_en'=> now(),
        ]);

        $response = $this->put(route('cultivos.update', $cultivoId), [
            'tipo'          => 'Plátano',
            'nombre'        => 'Lote Actualizado',
            'fecha_siembra' => now()->format('Y-m-d'),
            'area'          => 2.0,
            'unidad'        => 'hectareas',
            'estado'        => 'activo',
        ]);

        $this->assertDatabaseHas('cultivos', [
            'id'     => $cultivoId,
            'nombre' => 'Lote Actualizado',
            'area'   => 2.0,
        ]);
    }

    /** @test */
    public function puede_eliminar_un_cultivo(): void
    {
        $usuario = $this->crearYLoguearUsuario();

        $cultivoId = DB::table('cultivos')->insertGetId([
            'usuario_id'    => $usuario->id,
            'tipo'          => 'Frijol',
            'nombre'        => 'Cultivo a Eliminar',
            'fecha_siembra' => now()->format('Y-m-d'),
            'area'          => 0.5,
            'unidad'        => 'hectareas',
            'estado'        => 'activo',
            'creado_en'     => now(),
            'actualizado_en'=> now(),
        ]);

        $response = $this->delete(route('cultivos.destroy', $cultivoId));

        $this->assertDatabaseMissing('cultivos', ['id' => $cultivoId]);
        $response->assertRedirect();
    }
}