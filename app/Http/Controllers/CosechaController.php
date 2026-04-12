<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CosechaController extends Controller
{
    private function destinos(): array {
        return [
            'venta'           => ['label'=>'💵 Venta directa',    'crea_ingreso'=>true],
            'intermediario'   => ['label'=>'🤝 Intermediario',    'crea_ingreso'=>true],
            'plaza_mercado'   => ['label'=>'🏪 Plaza de mercado', 'crea_ingreso'=>true],
            'exportacion'     => ['label'=>'🌎 Exportación',      'crea_ingreso'=>true],
            'autoconsumo'     => ['label'=>'🏠 Autoconsumo',      'crea_ingreso'=>false],
            'almacenaje'      => ['label'=>'📦 Almacenaje',       'crea_ingreso'=>false],
            'semilla'         => ['label'=>'🌱 Reserva semilla',  'crea_ingreso'=>false],
            'donacion'        => ['label'=>'🤲 Donación',         'crea_ingreso'=>false],
        ];
    }

    private function guardarImagen($file, string $sub = 'cosechas'): string {
        $dir = public_path("img/{$sub}");
        if (!file_exists($dir)) mkdir($dir, 0775, true);
        $nombre = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $file->move($dir, $nombre);
        return "img/{$sub}/{$nombre}";
    }

    private function eliminarImagen(?string $ruta): void {
        if ($ruta) { $f = public_path($ruta); if (file_exists($f)) unlink($f); }
    }

    public function index(Request $request)
    {
        $uid = session('usuario_id');
        $query = DB::table('cosechas as cs')
            ->leftJoin('cultivos as c','c.id','=','cs.cultivo_id')
            ->where('cs.usuario_id',$uid)
            ->select('cs.*','c.nombre as cultivo_nombre');

        if ($request->q)        $query->where(fn($w) => $w->where('cs.producto','like',"%{$request->q}%")->orWhere('cs.comprador','like',"%{$request->q}%"));
        if ($request->mes)      $query->whereRaw("DATE_FORMAT(cs.fecha_cosecha,'%Y-%m') = ?",[$request->mes]);
        if ($request->calidad)  $query->where('cs.calidad',$request->calidad);
        if ($request->destino)  $query->where('cs.destino',$request->destino);

        $cosechas = $query->orderBy('cs.fecha_cosecha','desc')->get();

        $totalMes    = DB::table('cosechas')->where('usuario_id',$uid)->whereMonth('fecha_cosecha',now()->month)->whereYear('fecha_cosecha',now()->year)->sum('valor_estimado');
        $totalAnio   = DB::table('cosechas')->where('usuario_id',$uid)->whereYear('fecha_cosecha',now()->year)->sum('valor_estimado');
        $cantidadMes = DB::table('cosechas')->where('usuario_id',$uid)->whereMonth('fecha_cosecha',now()->month)->whereYear('fecha_cosecha',now()->year)->count();

        $topProductos = DB::table('cosechas')->where('usuario_id',$uid)->whereYear('fecha_cosecha',now()->year)
            ->selectRaw('producto, SUM(cantidad) as total_qty, SUM(valor_estimado) as total_valor')
            ->groupBy('producto')->orderByDesc('total_valor')->limit(5)->get();

        // En almacenaje activo
        try {
            $enAlmacen = DB::table('cosechas')->where('usuario_id',$uid)->where('destino','almacenaje')
                ->whereNotNull('almacen_hasta')->where('almacen_hasta','>=',now()->toDateString())->count();
        } catch (\Exception $e) { $enAlmacen = 0; }

        $cultivos  = DB::table('cultivos')->where('usuario_id',$uid)->orderBy('nombre')->get();
        $destinos  = $this->destinos();

        try {
            $clientes = DB::table('clientes')->where('usuario_id',$uid)->where('activo',1)->orderBy('nombre')->get();
        } catch (\Exception $e) { $clientes = collect(); }

        return view('pages.cosechas', compact(
            'cosechas','totalMes','totalAnio','cantidadMes','topProductos',
            'enAlmacen','cultivos','destinos','clientes'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'producto'      => 'required|min:2',
            'cantidad'      => 'required|numeric|min:0.01',
            'unidad'        => 'required',
            'fecha_cosecha' => 'required|date',
        ]);

        $uid   = session('usuario_id');
        $cant  = (float) $request->cantidad;
        $punit = $request->precio_unitario ? (float) $request->precio_unitario : null;
        $valor = $punit ? $cant * $punit : ($request->valor_estimado ? (float)$request->valor_estimado : null);

        // Merma aplicada
        if ($valor && $request->merma_porcentaje) {
            $valor = $valor * (1 - $request->merma_porcentaje/100);
        }

        $foto = null;
        if ($request->hasFile('foto')) $foto = $this->guardarImagen($request->file('foto'),'cosechas');

        // Comprador desde cliente guardado
        $comprador = $request->comprador;
        if ($request->cliente_id) {
            $cli = DB::table('clientes')->find($request->cliente_id);
            if ($cli) $comprador = $cli->nombre;
        }

        $id = DB::table('cosechas')->insertGetId([
            'usuario_id'       => $uid,
            'cultivo_id'       => $request->cultivo_id ?: null,
            'producto'         => $request->producto,
            'cantidad'         => $cant,
            'unidad'           => $request->unidad,
            'precio_unitario'  => $punit,
            'valor_estimado'   => $valor,
            'fecha_cosecha'    => $request->fecha_cosecha,
            'calidad'          => $request->calidad ?? 'buena',
            'destino'          => $request->destino,
            'comprador'        => $comprador,
            'cliente_id'       => $request->cliente_id ?: null,
            'observaciones'    => $request->observaciones,
            'foto'             => $foto,
            'merma_porcentaje' => $request->merma_porcentaje ?: null,
            'almacen_ubicacion'=> $request->almacen_ubicacion,
            'almacen_hasta'    => $request->almacen_hasta ?: null,
            'ingreso_creado'   => 0,
            'creado_en'        => now()->toDateTimeString(),
            'actualizado_en'   => now()->toDateTimeString(),
        ]);

        // Marcar cultivo como cosechado
        if ($request->cultivo_id && $request->marcar_cosechado) {
            DB::table('cultivos')->where('id',$request->cultivo_id)->where('usuario_id',$uid)
                ->update(['estado'=>'cosechado','actualizado_en'=>now()->toDateTimeString()]);
        }

        // Crear ingreso automático si el destino genera venta
        $destinoInfo = $this->destinos()[$request->destino] ?? null;
        if ($valor && $destinoInfo && $destinoInfo['crea_ingreso'] && $request->crear_ingreso) {
            DB::table('ingresos')->insert([
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
                'cosecha_id'     => $id,
                'notas'          => 'Ingreso generado automáticamente desde cosecha #'.$id,
                'creado_en'      => now()->toDateTimeString(),
            ]);
            DB::table('cosechas')->where('id',$id)->update(['ingreso_creado'=>1]);
        }

        return redirect()->route('cosechas.index')->with('msg','Cosecha registrada correctamente.')->with('msgType','success');
    }

    public function update(Request $request, $id)
    {
        $request->validate(['producto'=>'required|min:2','cantidad'=>'required|numeric|min:0.01','unidad'=>'required','fecha_cosecha'=>'required|date']);
        $uid     = session('usuario_id');
        $cosecha = DB::table('cosechas')->where('id',$id)->where('usuario_id',$uid)->first();
        if (!$cosecha) abort(404);

        $cant  = (float) $request->cantidad;
        $punit = $request->precio_unitario ? (float) $request->precio_unitario : null;
        $valor = $punit ? $cant * $punit : ($request->valor_estimado ? (float)$request->valor_estimado : null);

        $data = [
            'cultivo_id'       => $request->cultivo_id ?: null,
            'producto'         => $request->producto,
            'cantidad'         => $cant,
            'unidad'           => $request->unidad,
            'precio_unitario'  => $punit,
            'valor_estimado'   => $valor,
            'fecha_cosecha'    => $request->fecha_cosecha,
            'calidad'          => $request->calidad,
            'destino'          => $request->destino,
            'comprador'        => $request->comprador,
            'observaciones'    => $request->observaciones,
            'merma_porcentaje' => $request->merma_porcentaje ?: null,
            'almacen_ubicacion'=> $request->almacen_ubicacion,
            'almacen_hasta'    => $request->almacen_hasta ?: null,
            'actualizado_en'   => now()->toDateTimeString(),
        ];

        if ($request->hasFile('foto')) {
            $this->eliminarImagen($cosecha->foto ?? null);
            $data['foto'] = $this->guardarImagen($request->file('foto'),'cosechas');
        }

        DB::table('cosechas')->where('id',$id)->where('usuario_id',$uid)->update($data);
        return redirect()->route('cosechas.index')->with('msg','Cosecha actualizada.')->with('msgType','success');
    }

    public function destroy($id)
    {
        $uid     = session('usuario_id');
        $cosecha = DB::table('cosechas')->where('id',$id)->where('usuario_id',$uid)->first();
        if ($cosecha) $this->eliminarImagen($cosecha->foto ?? null);
        DB::table('cosechas')->where('id',$id)->where('usuario_id',$uid)->delete();
        return redirect()->route('cosechas.index')->with('msg','Cosecha eliminada.')->with('msgType','warning');
    }
}