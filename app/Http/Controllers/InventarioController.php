<?php

namespace App\Http\Controllers;

use App\Traits\ManejadorImagenes;
use App\Http\Requests\StoreInventarioRequest;
use App\Http\Requests\UpdateInventarioRequest;
use App\Models\Inventario;
use App\Models\Gasto;
use App\Models\Cultivo;
use App\Models\Animal;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    use ManejadorImagenes;

    private function categorias(): array
    {
        return [
            '🌱 Cultivos' => ['Semillas','Fertilizantes','Abonos','Plaguicidas','Herbicidas','Fungicidas'],
            '🐄 Animales' => ['Alimento animal','Medicamentos veterinarios','Vacunas animales','Suplementos'],
            '🔧 Finca'    => ['Herramientas','Equipos','Combustible','Repuestos','Empaques'],
            '📦 General'  => ['Otros'],
        ];
    }

    private function categoriasPlanas(): array
    {
        $r = [];
        foreach ($this->categorias() as $items) {
            foreach ($items as $c) $r[] = $c;
        }
        return $r;
    }

    /**
     * Muestra el listado de insumos con estadísticas y últimos movimientos.
     */
    public function index(Request $request)
    {
        $uid   = session('usuario_id');
        $query = Inventario::delUsuario($uid);

        if ($request->q)      $query->where('nombre', 'like', "%{$request->q}%");
        if ($request->cat)    $query->where('categoria', $request->cat);
        if ($request->uso)    $query->where('uso_principal', $request->uso);
        if ($request->alerta) $query->conStockBajo();

        $insumos = $query->orderBy('nombre')->get();

        $alertasStock    = $insumos->filter(fn($i) => $i->cantidad_actual <= $i->stock_minimo)->count();
        $porVencer       = $insumos->filter(fn($i) => $i->fecha_vencimiento
            && \Carbon\Carbon::parse($i->fecha_vencimiento)->diffInDays(now()) <= 30
            && \Carbon\Carbon::parse($i->fecha_vencimiento)->isFuture())->count();
        $valorInventario = $insumos->sum(fn($i) => $i->cantidad_actual * ($i->precio_unitario ?? 0));
        $totalInsumos    = $insumos->count();

        // Mantiene JOIN en DB::table para traer columnas de 4 tablas distintas
        $movimientos = DB::table('inventario_movimientos as m')
            ->join('inventario as i',    'i.id',   '=', 'm.inventario_id')
            ->leftJoin('cultivos as c',  'c.id',   '=', 'm.cultivo_id')
            ->leftJoin('animales as a',  'a.id',   '=', 'm.animal_id')
            ->leftJoin('personas as per','per.id', '=', 'm.persona_id')
            ->where('m.usuario_id', $uid)
            ->select('m.*', 'i.nombre as insumo_nombre', 'i.unidad',
                     'c.nombre as cultivo_nombre',
                     'a.nombre_lote as animal_nombre', 'a.especie as animal_especie',
                     'per.nombre as persona_nombre')
            ->orderBy('m.creado_en', 'desc')->limit(15)->get();

        $cultivos     = Cultivo::delUsuario($uid)->activos()->orderBy('nombre')->get();
        $animales     = Animal::delUsuario($uid)->activos()->orderBy('nombre_lote')->get();
        $categorias   = $this->categorias();
        $catPlanas    = $this->categoriasPlanas();
        $trabajadores = Persona::delUsuario($uid)->activos()->orderBy('nombre')->get();

        return view('pages.inventario', compact(
            'insumos', 'totalInsumos', 'alertasStock', 'porVencer', 'valorInventario',
            'movimientos', 'cultivos', 'animales', 'categorias', 'catPlanas', 'trabajadores'
        ));
    }

    /**
     * Registra un nuevo insumo con su stock inicial como primer movimiento.
     */
    public function store(StoreInventarioRequest $request)
    {
        $uid  = session('usuario_id');
        $foto = null;
        if ($request->hasFile('foto')) {
            $foto = $this->guardarImagen($request->file('foto'), 'inventario');
        }

        $insumo = Inventario::create([
            'usuario_id'       => $uid,
            'nombre'           => $request->nombre,
            'categoria'        => $request->categoria,
            'cantidad_actual'  => $request->cantidad_actual,
            'stock_minimo'     => $request->stock_minimo,
            'unidad'           => $request->unidad,
            'precio_unitario'  => $request->precio_unitario ?: null,
            'proveedor'        => $request->proveedor ?: null,
            'fecha_vencimiento'=> $request->fecha_vencimiento ?: null,
            'notas'            => $request->notas ?: null,
            'foto'             => $foto,
            'ubicacion'        => $request->ubicacion ?: null,
            'uso_principal'    => $request->uso_principal ?? 'general',
        ]);

        if ((float) $request->cantidad_actual > 0) {
            DB::table('inventario_movimientos')->insert([
                'inventario_id'  => $insumo->id,
                'usuario_id'     => $uid,
                'tipo'           => 'entrada',
                'cantidad'       => $request->cantidad_actual,
                'precio_unitario'=> $request->precio_unitario ?: null,
                'motivo'         => 'Stock inicial',
                'persona'        => $request->persona_registro ?: null,
                'fecha'          => now()->toDateString(),
                'creado_en'      => now()->toDateTimeString(),
            ]);
        }

        return redirect()->route('inventario.index')
            ->with('msg', 'Insumo registrado correctamente.')->with('msgType', 'success');
    }

    /**
     * Actualiza los datos de un insumo existente.
     */
    public function update(UpdateInventarioRequest $request, $id)
    {
        $uid    = session('usuario_id');
        $insumo = Inventario::where('id', $id)->where('usuario_id', $uid)->firstOrFail();

        $data = [
            'nombre'           => $request->nombre,
            'categoria'        => $request->categoria,
            'stock_minimo'     => $request->stock_minimo,
            'unidad'           => $request->unidad,
            'precio_unitario'  => $request->precio_unitario ?: null,
            'proveedor'        => $request->proveedor ?: null,
            'fecha_vencimiento'=> $request->fecha_vencimiento ?: null,
            'notas'            => $request->notas ?: null,
            'ubicacion'        => $request->ubicacion ?: null,
            'uso_principal'    => $request->uso_principal ?? 'general',
        ];

        if ($request->hasFile('foto')) {
            $this->eliminarImagen($insumo->foto);
            $data['foto'] = $this->guardarImagen($request->file('foto'), 'inventario');
        }

        $insumo->update($data);

        return redirect()->route('inventario.index')
            ->with('msg', 'Insumo actualizado.')->with('msgType', 'success');
    }

    /**
     * Elimina un insumo y su foto asociada.
     */
    public function destroy($id)
    {
        $uid    = session('usuario_id');
        $insumo = Inventario::where('id', $id)->where('usuario_id', $uid)->firstOrFail();
        $this->eliminarImagen($insumo->foto);
        $insumo->delete();

        return redirect()->route('inventario.index')
            ->with('msg', 'Insumo eliminado.')->with('msgType', 'warning');
    }

    /**
     * Registra un movimiento de stock. Las entradas con precio generan gasto automático.
     * Los tipos en_uso y devolucion NO modifican el stock físico.
     */
    public function movimiento(Request $request, $id)
    {
        $request->validate([
            'tipo'     => 'required|in:entrada,salida,ajuste,en_uso,devolucion',
            'cantidad' => 'required|numeric|min:0.01',
            'fecha'    => 'required|date',
        ]);

        $uid      = session('usuario_id');
        $insumo   = Inventario::where('id', $id)->where('usuario_id', $uid)->firstOrFail();
        $cantidad = (float) $request->cantidad;

        $nueva = match($request->tipo) {
            'entrada'    => $insumo->cantidad_actual + $cantidad,
            'salida'     => max(0, $insumo->cantidad_actual - $cantidad),
            'ajuste'     => $cantidad,
            'en_uso',
            'devolucion' => $insumo->cantidad_actual,
            default      => $insumo->cantidad_actual,
        };

        $insumo->update(['cantidad_actual' => $nueva]);

        $fotoSoporte = null;
        if ($request->hasFile('foto_soporte')) {
            $fotoSoporte = $this->guardarImagen($request->file('foto_soporte'), 'inventario/soportes');
        }

        DB::table('inventario_movimientos')->insert([
            'inventario_id'  => $id,
            'usuario_id'     => $uid,
            'cultivo_id'     => $request->cultivo_id ?: null,
            'animal_id'      => $request->animal_id  ?: null,
            'persona_id'     => $request->persona_id ?: null,
            'tipo'           => $request->tipo,
            'cantidad'       => $cantidad,
            'precio_unitario'=> $request->precio_unitario ?: null,
            'motivo'         => $request->motivo ?: null,
            'persona'        => $request->persona ?: null,
            'foto_soporte'   => $fotoSoporte,
            'fecha'          => $request->fecha,
            'creado_en'      => now()->toDateTimeString(),
        ]);

        // Gasto automático al registrar una entrada con precio unitario
        if ($request->tipo === 'entrada' && $request->precio_unitario) {
            Gasto::create([
                'usuario_id'      => $uid,
                'cultivo_id'      => $request->cultivo_id ?: null,
                'animal_id'       => $request->animal_id  ?: null,
                'categoria'       => $insumo->categoria,
                'descripcion'     => 'Compra de '.$insumo->nombre,
                'cantidad'        => $cantidad,
                'unidad_cantidad' => $insumo->unidad,
                'valor'           => $cantidad * (float) $request->precio_unitario,
                'fecha'           => $request->fecha,
                'proveedor'       => $request->proveedor_mov ?: $insumo->proveedor,
                'notas'           => 'Generado automáticamente desde inventario',
                'pendiente_sync'  => 0,
            ]);
        }

        $msg = match($request->tipo) {
            'entrada'    => "Entrada registrada. Stock: {$nueva} {$insumo->unidad}",
            'salida'     => "Salida registrada. Stock: {$nueva} {$insumo->unidad}",
            'ajuste'     => "Stock ajustado a: {$nueva} {$insumo->unidad}",
            'en_uso'     => "Marcado en uso. Stock disponible: {$nueva} {$insumo->unidad}",
            'devolucion' => "Devolución registrada. Stock: {$nueva} {$insumo->unidad}",
            default      => "Movimiento registrado. Stock: {$nueva} {$insumo->unidad}",
        };

        $stockBajo = $nueva <= $insumo->stock_minimo;
        if ($stockBajo) $msg .= ' ⚠️ Stock bajo mínimo.';

        return redirect()->route('inventario.index')
            ->with('msg', $msg)
            ->with('msgType', $stockBajo ? 'warning' : 'success');
    }

    /**
     * Muestra la vista de alertas: stock bajo, por vencer y vencidos.
     */
    public function alertas()
    {
        $uid = session('usuario_id');

        $alertasStock = Inventario::delUsuario($uid)->conStockBajo()
            ->orderByRaw('(cantidad_actual / GREATEST(stock_minimo, 0.01)) ASC')
            ->get();

        $porVencer = Inventario::delUsuario($uid)->porVencer(30)
            ->orderBy('fecha_vencimiento')->get();

        $vencidos = Inventario::delUsuario($uid)
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<', now()->toDateString())
            ->orderBy('fecha_vencimiento')->get();

        return view('pages.inventario-alertas', compact('alertasStock', 'porVencer', 'vencidos'));
    }
}