<?php

namespace Tests\Feature\Animales;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class AnimalTest extends TestCase
{
    /** @test */
    public function puede_ver_lista_de_animales(): void
    {
        $this->crearYLoguearUsuario();
        $this->get(route('animales.index'))->assertStatus(200);
    }

    /** @test */
    public function puede_registrar_un_lote_animal(): void
    {
        $usuario = $this->crearYLoguearUsuario();

        $response = $this->post(route('animales.store'), [
            'especie'       => 'Ganado bovino',
            'nombre_lote'   => 'Lote Prueba Bovino',
            'cantidad'      => 10,
            'fecha_ingreso' => now()->format('Y-m-d'),
            'estado'        => 'activo',
            'peso_promedio' => 350,
            'unidad_peso'   => 'kg',
        ]);

        $this->assertDatabaseHas('animales', [
            'usuario_id'  => $usuario->id,
            'especie'     => 'Ganado bovino',
            'nombre_lote' => 'Lote Prueba Bovino',
            'cantidad'    => 10,
        ]);
    }

    /** @test */
    public function registro_animal_requiere_especie_y_cantidad(): void
    {
        $this->crearYLoguearUsuario();

        $response = $this->post(route('animales.store'), [
            'especie'   => '',
            'cantidad'  => '',
        ]);

        $response->assertSessionHasErrors(['especie', 'cantidad']);
    }

    /** @test */
    public function puede_ver_detalle_de_un_animal(): void
    {
        $usuario = $this->crearYLoguearUsuario();

        $animalId = DB::table('animales')->insertGetId([
            'usuario_id'  => $usuario->id,
            'especie'     => 'Gallinas',
            'nombre_lote' => 'Galpón Test',
            'cantidad'    => 50,
            'estado'      => 'activo',
            'creado_en'   => now(),
            'actualizado_en' => now(),
        ]);

        $this->get(route('animales.show', $animalId))->assertStatus(200);
    }
}