<?php

namespace App\Http\Controllers;

use App\Traits\ManejadorImagenes;
use App\Http\Requests\CosechaRequest;
use App\Models\Cosecha;
use App\Models\Cultivo;
use App\Models\Ingreso;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CosechaController extends Controller
{
    use ManejadorImagenes;

    private function destinos(): array
    {
        return [
            'venta'         => ['label' => '💵 Venta directa',    'crea_ingreso' => true],
            'intermediario' => ['label' => '🤝 Intermediario',    'crea_ingreso' => true],
            'plaza_mercado' => ['label' => '🏪 Plaza de mercado', 'crea_ingreso' => true],
            'exportacion'   => ['label' => '🌎 Exportación',      'crea_ingreso' => true],
            'autoconsumo'   => ['label' => '🏠 Autoconsumo',      'crea_ingreso' => false],
            'almacenaje'    => ['label' => '📦 Almacenaje',       'crea_ingreso' => false],
            'semilla'       => ['label' => '🌱 Reserva semilla',  'crea_ingreso' => false],
            'donacion'      => ['label' => '🤲 Donación',         'crea_ingreso' => false],
        ];
    }

    /**
     * Muestra el listado de cosechas con estadísticas del período.
     */
    public function index(Request $request)
    {
        $uid = session('usuario_id');

        // Mantiene JOIN en DB::table para traer el nombre del cultivo junto a la cosecha
        $query = DB::table('cosechas as cs')
            ->leftJoin('cultivos as c', 'c.id', '=', 'cs.cultivo_id')
            ->where('cs.usuario_id', $uid)
            ->select('cs.*', 'c.nombre as cultivo_nombre');

        if ($request->q)       $query->where(fn($w) => $w->where('cs.producto','like',"%{$request->q}%")->orWhere('cs.comprador','like',"%{$request->q}%"));
        if ($request->mes)     $query->whereRaw("DATE_FORMAT(cs.fecha_cosecha,'%Y-%m') = ?", [$request->mes]);
        if ($request->calidad) $query->where('cs.calidad', $request->calidad);
        if ($request->destino) $query->where('cs.destino', $request->destino);

        $cosechas = $query->orderBy('cs.fecha_cosecha', 'desc')->get();

        $totalMes    = Cosecha::delUsuario($uid)->delMes()->sum('valor_estimado');
        $totalAnio   = Cosecha::delUsuario($uid)->whereYear('fecha_cosecha', now()->year)->sum('valor_estimado');
        $cantidadMes = Cosecha::delUsuario($uid)->delMes()->count();

        $topProductos = Cosecha::delUsuario($uid)
            ->whereYear('fecha_cosecha', now()->year)
            ->selectRaw('producto, SUM(cantidad) as total_qty, SUM(valor_estimado) as total_valor')
            ->groupBy('producto')->orderByDesc('total_valor')->limit(5)->get();

        try {
            $enAlmacen = Cosecha::delUsuario($uid)
                ->where('destino', 'almacenaje')
                ->whereNotNull('almacen_hasta')
                ->where('almacen_hasta', '>=', now()->toDateString())
                ->count();
        } catch (\Exception $e) { $enAlmacen = 0; }

        $cultivos = Cultivo::delUsuario($uid)->orderBy('nombre')->get();
        $destinos = $this->destinos();
        $clientes = Persona::delUsuario($uid)->activos()->compradores()->orderBy('nombre')->get();

        return view('pages.cosechas', compact(
            'cosechas', 'totalMes', 'totalAnio', 'cantidadMes', 'topProductos',
            'enAlmacen', 'cultivos', 'destinos', 'clientes'
        ));
    }

    /**
     * Registra una nueva cosecha y opcionalmente crea un ingreso automático.
     */
    public function store(CosechaRequest $request)
    {
        $uid  = session('usuario_id');
        $cant = (float) $request->cantidad;
        $punit = $request->precio_unitario ? (float) $request->precio_unitario : null;
        $valor = $punit ? $cant * $punit : ($request->valor_estimado ? (float) $request->valor_estimado : null);

        if ($valor && $request->merma_porcentaje) {
            $valor = $valor * (1 - $request->merma_porcentaje / 100);
        }

        $foto = null;
        if ($request->hasFile('foto')) {
            $foto = $this->guardarImagen($request->file('foto'), 'cosechas');
        }

        $comprador = $request->comprador;
        if ($request->cliente_id) {
            $cli = Persona::find($request->cliente_id);
            if ($cli) $comprador = $cli->nombre;
        }

        $cosecha = Cosecha::create([
            'usuario_id'        => $uid,
            'cultivo_id'        => $request->cultivo_id ?: null,
            'producto'          => $request->producto,
            'cantidad'          => $cant,
            'unidad'            => $request->unidad,
            'precio_unitario'   => $punit,
            'valor_estimado'    => $valor,
            'fecha_cosecha'     => $request->fecha_cosecha,
            'calidad'           => $request->calidad ?? 'buena',
            'destino'           => $request->destino,
            'comprador'         => $comprador,
            'cliente_id'        => $request->cliente_id ?: null,
            'observaciones'     => $request->observaciones,
            'foto'              => $foto,
            'merma_porcentaje'  => $request->merma_porcentaje ?: null,
            'almacen_ubicacion' => $request->almacen_ubicacion,
            'almacen_hasta'     => $request->almacen_hasta ?: null,
            'ingreso_creado'    => 0,
        ]);

        if ($request->cultivo_id && $request->marcar_cosechado) {
            Cultivo::where('id', $request->cultivo_id)->where('usuario_id', $uid)
                ->update(['estado' => 'cosechado']);
        }

        $destinoInfo = $this->destinos()[$request->destino] ?? null;
        if ($valor && $destinoInfo && $destinoInfo['crea_ingreso'] && $request->crear_ingreso) {
            Ingreso::create([
                'usuario_id'     => $uid,
                'descripcion'    => 'Venta de '.$request->producto,
                'cantidad'       => $cant,
                'unidad'         => $request->unidad,
                'precio_unitario'=> $punit,
                'valor_total'    => $valor,
                'fecha'          => $request->fecha_cosecha,
                'comprador'      => $comprador,
                'cliente_id'     => $request->cliente_id ?: null,
                'tipo'           => 'cosecha_propia',
                'cultivo_id'     => $request->cultivo_id ?: null,
                'cosecha_id'     => $cosecha->id,
                'notas'          => 'Ingreso generado automáticamente desde cosecha #'.$cosecha->id,
            ]);
            $cosecha->update(['ingreso_creado' => 1]);
        }

        return redirect()->route('cosechas.index')
            ->with('msg', 'Cosecha registrada correctamente.')->with('msgType', 'success');
    }

    /**
     * Actualiza los datos de una cosecha existente.
     */
    public function update(CosechaRequest $request, $id)
    {
        $uid     = session('usuario_id');
        $cosecha = Cosecha::where('id', $id)->where('usuario_id', $uid)->firstOrFail();

        $cant  = (float) $request->cantidad;
        $punit = $request->precio_unitario ? (float) $request->precio_unitario : null;
        $valor = $punit ? $cant * $punit : ($request->valor_estimado ? (float) $request->valor_estimado : null);

        $data = [
            'cultivo_id'        => $request->cultivo_id ?: null,
            'producto'          => $request->producto,
            'cantidad'          => $cant,
            'unidad'            => $request->unidad,
            'precio_unitario'   => $punit,
            'valor_estimado'    => $valor,
            'fecha_cosecha'     => $request->fecha_cosecha,
            'calidad'           => $request->calidad,
            'destino'           => $request->destino,
            'comprador'         => $request->comprador,
            'observaciones'     => $request->observaciones,
            'merma_porcentaje'  => $request->merma_porcentaje ?: null,
            'almacen_ubicacion' => $request->almacen_ubicacion,
            'almacen_hasta'     => $request->almacen_hasta ?: null,
        ];

        if ($request->hasFile('foto')) {
            $this->eliminarImagen($cosecha->foto);
            $data['foto'] = $this->guardarImagen($request->file('foto'), 'cosechas');
        }

        $cosecha->update($data);

        return redirect()->route('cosechas.index')
            ->with('msg', 'Cosecha actualizada.')->with('msgType', 'success');
    }

    /**
     * Elimina una cosecha y su foto asociada.
     */
    public function destroy($id)
    {
        $uid     = session('usuario_id');
        $cosecha = Cosecha::where('id', $id)->where('usuario_id', $uid)->firstOrFail();
        $this->eliminarImagen($cosecha->foto);
        $cosecha->delete();

        return redirect()->route('cosechas.index')
            ->with('msg', 'Cosecha eliminada.')->with('msgType', 'warning');
    }
}