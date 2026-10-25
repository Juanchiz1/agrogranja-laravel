<?php

namespace Tests\Feature\Finanzas;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class GastoTest extends TestCase
{
    /** @test */
    public function lista_gastos_requiere_sesion(): void
    {
        $this->get(route('gastos.index'))->assertRedirect(route('login'));
    }

    /** @test */
    public function puede_ver_lista_de_gastos(): void
    {
        $this->crearYLoguearUsuario();
        $this->get(route('gastos.index'))->assertStatus(200);
    }

    /** @test */
    public function puede_crear_un_gasto(): void
    {
        $usuario = $this->crearYLoguearUsuario();

        $response = $this->post(route('gastos.store'), [
            'categoria'  => 'Semillas',
            'descripcion'=> 'Semillas de maíz híbrido',
            'cantidad'   => 10,
            'valor'      => 85000,
            'fecha'      => now()->format('Y-m-d'),
        ]);

        $this->assertDatabaseHas('gastos', [
            'usuario_id' => $usuario->id,
            'categoria'  => 'Semillas',
            'valor'      => 85000,
        ]);
    }

    /** @test */
    public function gasto_requiere_categoria_y_valor(): void
    {
        $this->crearYLoguearUsuario();

        $response = $this->post(route('gastos.store'), [
            'categoria' => '',
            'valor'     => '',
            'fecha'     => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors(['categoria', 'valor']);
    }

    /** @test */
    public function valor_del_gasto_debe_ser_positivo(): void
    {
        $this->crearYLoguearUsuario();

        $response = $this->post(route('gastos.store'), [
            'categoria'  => 'Semillas',
            'descripcion'=> 'Test',
            'valor'      => -5000,  // Negativo
            'fecha'      => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors(['valor']);
    }

    /** @test */
    public function puede_eliminar_un_gasto(): void
    {
        $usuario = $this->crearYLoguearUsuario();

        $gastoId = DB::table('gastos')->insertGetId([
            'usuario_id' => $usuario->id,
            'categoria'  => 'Herramientas',
            'descripcion'=> 'Gasto a eliminar',
            'valor'      => 50000,
            'fecha'      => now()->format('Y-m-d'),
            'creado_en' => now(),  
        ]);

        $this->delete(route('gastos.destroy', $gastoId));

        $this->assertDatabaseMissing('gastos', ['id' => $gastoId]);
    }
}