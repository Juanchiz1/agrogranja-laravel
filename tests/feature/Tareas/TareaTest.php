<?php

namespace Tests\Feature\Tareas;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class TareaTest extends TestCase
{
    /** @test */
    public function puede_ver_lista_de_tareas(): void
    {
        $this->crearYLoguearUsuario();
        $this->get('/tareas');
    }

    /** @test */
    public function puede_crear_una_tarea(): void
    {
        $usuario = $this->crearYLoguearUsuario();

        $response = $this->post(route('tareas.store'), [
            'titulo'   => 'Riego matutino lote 1',
            'tipo'     => 'riego',
            'fecha'    => now()->addDay()->format('Y-m-d'),
            'prioridad'=> 'alta',
        ]);

        $this->assertDatabaseHas('tareas', [
            'usuario_id' => $usuario->id,
            'titulo'     => 'Riego matutino lote 1',
            'prioridad'  => 'alta',
        ]);
    }

    /** @test */
    public function tarea_requiere_titulo_y_fecha(): void
    {
        $this->crearYLoguearUsuario();

        $response = $this->post(route('tareas.store'), [
            'titulo' => '',
            'fecha'  => '',
        ]);

        $response->assertSessionHasErrors(['titulo', 'fecha']);
    }

    /** @test */
    public function puede_marcar_tarea_como_completada(): void
    {
        $usuario = $this->crearYLoguearUsuario();

        $tareaId = DB::table('tareas')->insertGetId([
            'usuario_id' => $usuario->id,
            'titulo'     => 'Tarea pendiente',
            'tipo'       => 'riego',
            'fecha'      => now()->format('Y-m-d'),
            'prioridad'  => 'media',
            'completada' => false,
            'creado_en' => now(),   
        ]);

        $this->patch(route('tareas.update', $tareaId), [
            'titulo'     => 'Tarea pendiente',
            'tipo'       => 'riego',
            'fecha'      => now()->format('Y-m-d'),
            'prioridad'  => 'media',
            'completada' => true,
        ]);

        $this->assertDatabaseHas('tareas', [
            'id'         => $tareaId,
            'completada' => true,
        ]);
    }
}