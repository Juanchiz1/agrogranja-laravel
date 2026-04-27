<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProduccionAnimalController extends Controller
{
    // Unidades por tipo de producción
    private function unidades(): array {
        return [
            'leche'  => ['litros', 'ml'],
            'huevos' => ['unidades', 'docenas'],
            'lana'   => ['kg', 'lb'],
            'miel'   => ['kg', 'litros'],
            'otro'   => ['kg', 'unidades', 'litros'],
        ];
    }

    public function index(Request $request)
    {
        $uid = session('usuario_id');

        // Animales con producción continua activos
        $animales = DB::table('animales')
            ->where('usuario_id', $uid)
            ->where('estado', 'activo')
            ->whereNotNull('produccion')
            ->where('produccion', '!=', 'carne')
            ->where('produccion', '!=', '')
            ->orderBy('nombre_lote')
            ->get();

        // Filtros
        $animalId = $request->animal_id;
        $mes      = $request->mes ?? now()->format('Y-m');

        $query = DB::table('animal_produccion as ap')
            ->join('animales as a', 'a.id', '=', 'ap.animal_id')
            ->where('ap.usuario_id', $uid)
            ->whereRaw("DATE_FORMAT(ap.fecha, '%Y-%m') = ?", [$mes])
            ->select('ap.*', 'a.nombre_lote', 'a.especie', 'a.cantidad as cabezas');

        if ($animalId) $query->where('ap.animal_id', $animalId);

        $registros = $query->orderBy('ap.fecha', 'desc')->get();

        // Resumen del mes
        $resumenMes = DB::table('animal_produccion')
            ->where('usuario_id', $uid)
            ->whereRaw("DATE_FORMAT(fecha, '%Y-%m') = ?", [$mes])
            ->selectRaw('tipo_produccion, SUM(cantidad) as total_qty, SUM(valor_total) as total_valor, COUNT(*) as dias')
            ->groupBy('tipo_produccion')
            ->get();

        // Producción diaria promedio por animal (últimos 30 días)
        $promedios = DB::table('animal_produccion')
            ->where('usuario_id', $uid)
            ->where('fecha', '>=', now()->subDays(30)->toDateString())
            ->selectRaw('animal_id, tipo_produccion, unidad, AVG(cantidad) as promedio_dia')
            ->groupBy('animal_id', 'tipo_produccion', 'unidad')
            ->get()
            ->groupBy('animal_id');

        return view('pages.produccion-animal', compact(
            'animales', 'registros', 'resumenMes', 'promedios', 'mes', 'animalId'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'animal_id'       => 'required|integer',
            'fecha'           => 'required|date',
            'tipo_produccion' => 'required',
            'cantidad'        => 'required|numeric|min:0.01',
            'unidad'          => 'required',
        ]);

        $uid      = session('usuario_id');
        $cantidad = (float) $request->cantidad;
        $punit    = $request->precio_unitario ? (float) $request->precio_unitario : null;
        $valor    = $punit ? round($cantidad * $punit, 2) : null;

        $id = DB::table('animal_produccion')->insertGetId([
            'animal_id'       => $request->animal_id,
            'usuario_id'      => $uid,
            'fecha'           => $request->fecha,
            'tipo_produccion' => $request->tipo_produccion,
            'cantidad'        => $cantidad,
            'unidad'          => $request->unidad,
            'precio_unitario' => $punit,
            'valor_total'     => $valor,
            'vendido'         => $request->vendido ? 1 : 0,
            'comprador'       => $request->comprador ?: null,
            'notas'           => $request->notas ?: null,
            'ingreso_creado'  => 0,
            'creado_en'       => now()->toDateTimeString(),
        ]);

        // AUTO-INGRESO si se marcó como vendido y tiene precio
        if ($request->vendido && $valor > 0) {
            $animal = DB::table('animales')->find($request->animal_id);

            DB::table('ingresos')->insert([
                'usuario_id'      => $uid,
                'animal_id'       => $request->animal_id,
                'referencia_id'   => $id,
                'referencia_tipo' => 'produccion',
                'tipo'            => 'produccion_animal',
                'descripcion'     => 'Venta de ' . $request->tipo_produccion .
                                     ' — ' . ($animal->nombre_lote ?? $animal->especie),
                'cantidad'        => $cantidad,
                'unidad'          => $request->unidad,
                'precio_unitario' => $punit,
                'valor_total'     => $valor,
                'fecha'           => $request->fecha,
                'comprador'       => $request->comprador ?: null,
                'notas'           => 'Generado automáticamente desde producción #' . $id,
                'creado_en'       => now()->toDateTimeString(),
            ]);

            DB::table('animal_produccion')
                ->where('id', $id)
                ->update(['ingreso_creado' => 1]);
        }

        return redirect()->route('produccion-animal.index')
            ->with('msg', 'Producción registrada' . ($request->vendido && $valor > 0 ? ' e ingreso creado automáticamente.' : '.'))
            ->with('msgType', 'success');
    }

    public function destroy($id)
    {
        $uid     = session('usuario_id');
        $registro = DB::table('animal_produccion')
            ->where('id', $id)
            ->where('usuario_id', $uid)
            ->first();

        if ($registro && $registro->ingreso_creado) {
            // Eliminar ingreso asociado
            DB::table('ingresos')
                ->where('referencia_id', $id)
                ->where('referencia_tipo', 'produccion')
                ->where('usuario_id', $uid)
                ->delete();
        }

        DB::table('animal_produccion')
            ->where('id', $id)
            ->where('usuario_id', $uid)
            ->delete();

        return redirect()->route('produccion-animal.index')
            ->with('msg', 'Registro eliminado.')
            ->with('msgType', 'warning');
    }
}