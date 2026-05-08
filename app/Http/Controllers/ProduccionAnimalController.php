<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProduccionAnimalController extends Controller
{
    // Tipos de sesión por especie/producción
    private const SESIONES = [
        'leche'  => ['am' => 'Ordeño AM', 'pm' => 'Ordeño PM', 'noche' => 'Ordeño Noche'],
        'huevos' => ['manana' => 'Recolección Mañana', 'tarde' => 'Recolección Tarde'],
        'miel'   => ['unica' => 'Cosecha Única'],
        'lana'   => ['unica' => 'Esquila'],
        'otro'   => ['general' => 'Producción General'],
    ];

    // Destinos disponibles
    private const DESTINOS = [
        'venta_directa'    => 'Venta directa',
        'consumo_familiar' => 'Consumo familiar',
        'transformacion'   => 'Transformacion (queso, yogur...)',
        'inventario'       => 'A inventario',
        'desperdicio'      => 'Desperdicio/merma',
    ];

    // ─────────────────────────────────────────────────────────────────
    // INDEX — Dashboard de producción con sesiones del día
    // ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $uid   = session('usuario_id');
        $fecha = $request->fecha ?? now()->toDateString();
        $animalFiltro = $request->animal_id;

        // Todos los animales productivos del usuario
        $animalesProductivos = DB::table('animales')
            ->where('usuario_id', $uid)
            ->where('estado', 'activo')
            ->whereNotNull('produccion')
            ->whereNotIn(DB::raw('LOWER(TRIM(produccion))'), ['carne', '', 'null'])
            ->orderBy('especie')->orderBy('nombre_lote')
            ->get();

        // Producción del día seleccionado agrupada por animal y sesión
        $prodDia = DB::table('animal_produccion as p')
            ->join('animales as a', 'a.id', '=', 'p.animal_id')
            ->where('p.usuario_id', $uid)
            ->where('p.fecha', $fecha)
            ->when($animalFiltro, fn($q) => $q->where('p.animal_id', $animalFiltro))
            ->select('p.*', 'a.nombre_lote', 'a.especie', 'a.foto as animal_foto')
            ->orderBy('a.especie')->orderBy('p.sesion')
            ->get();

        // Agrupar por animal para mostrar AM/PM en columnas
        $prodPorAnimal = $prodDia->groupBy('animal_id');

        // Totales del día
        $totalDia   = $prodDia->sum('cantidad');
        $valorDia   = $prodDia->sum('valor_total');
        $registrosDia = $prodDia->count();

        // Histórico 30 días para la gráfica
        $historico = DB::table('animal_produccion')
            ->where('usuario_id', $uid)
            ->where('fecha', '>=', now()->subDays(30)->toDateString())
            ->when($animalFiltro, fn($q) => $q->where('animal_id', $animalFiltro))
            ->selectRaw('fecha, SUM(cantidad) as total, SUM(valor_total) as valor,
                         tipo_produccion')
            ->groupBy('fecha', 'tipo_produccion')
            ->orderBy('fecha')
            ->get();

        $chartLabels = $historico->pluck('fecha')
            ->unique()
            ->map(fn($f) => Carbon::parse($f)->format('d/m'))
            ->values()->toArray();

        $chartCantidad = $historico->groupBy('fecha')
            ->map(fn($g) => round($g->sum('total'), 2))
            ->values()->toArray();

        $chartValor = $historico->groupBy('fecha')
            ->map(fn($g) => round($g->sum('valor'), 0))
            ->values()->toArray();

        // Resumen del mes actual
        $mesActual = now()->format('Y-m');
        $resMes = DB::table('animal_produccion')
            ->where('usuario_id', $uid)
            ->whereRaw("DATE_FORMAT(fecha,'%Y-%m') = ?", [$mesActual])
            ->selectRaw('tipo_produccion, SUM(cantidad) as total, SUM(valor_total) as valor,
                         unidad, COUNT(*) as registros')
            ->groupBy('tipo_produccion', 'unidad')
            ->get();

        $destinos  = self::DESTINOS;
        $sesiones  = self::SESIONES;

        return view('pages.produccion.index', compact(
            'animalesProductivos', 'prodDia', 'prodPorAnimal',
            'totalDia', 'valorDia', 'registrosDia',
            'historico', 'chartLabels', 'chartCantidad', 'chartValor',
            'resMes', 'fecha', 'animalFiltro', 'destinos', 'sesiones'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // STORE — Registrar producción con sesión y destino
    // ─────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'animal_id'      => 'required|integer',
            'fecha'          => 'required|date',
            'tipo_produccion'=> 'required',
            'cantidad'       => 'required|numeric|min:0.001',
            'unidad'         => 'required',
        ]);

        $uid    = session('usuario_id');
        $animal = DB::table('animales')
            ->where('id', $request->animal_id)->where('usuario_id', $uid)->first();
        if (!$animal) abort(404);

        $precio     = $request->precio_unitario ?: null;
        $valorTotal = ($precio && $request->cantidad) ? round($precio * $request->cantidad, 2) : null;
        $destino    = $request->destino ?? 'venta_directa';

        // Calcular costo estimado por unidad del período (últimos 30 días)
        $costoUnitario = $this->calcularCostoUnitario(
            $request->animal_id, $uid,
            now()->subDays(30)->toDateString(), $request->fecha
        );

        // Crear el registro de producción
        $prodId = DB::table('animal_produccion')->insertGetId([
            'animal_id'          => $request->animal_id,
            'usuario_id'         => $uid,
            'fecha'              => $request->fecha,
            'tipo_produccion'    => $request->tipo_produccion,
            'cantidad'           => $request->cantidad,
            'unidad'             => $request->unidad,
            'precio_unitario'    => $precio,
            'valor_total'        => $valorTotal,
            'vendido'            => 0,
            'comprador'          => $request->comprador ?: null,
            'notas'              => $request->notas ?: null,
            'sesion'             => $request->sesion ?? 'unica',
            'destino'            => $destino,
            'transformacion_tipo'=> $request->transformacion_tipo ?: null,
            'costo_estimado'     => $costoUnitario,
            'periodo'            => $request->periodo ?? 'dia',
            'ingreso_creado'     => 0,
            'creado_en'          => now()->toDateTimeString(),
        ]);

        // ── Acción según destino ─────────────────────────────────────
        $msgExtra = '';

        if ($destino === 'venta_directa' && $valorTotal > 0) {
            // Crear ingreso automáticamente
            $ingresoId = DB::table('ingresos')->insertGetId([
                'usuario_id'  => $uid,
                'descripcion' => 'Produccion: ' . ucfirst($request->tipo_produccion) .
                                 ' — ' . ($animal->nombre_lote ?? $animal->especie),
                'valor_total' => $valorTotal,
                'fecha'       => $request->fecha,
                'animal_id'   => $request->animal_id,
                'tipo'        => 'produccion',
                'comprador'   => $request->comprador ?: null,
                'notas'       => $request->notas,
                'creado_en'   => now()->toDateTimeString(),
            ]);
            DB::table('animal_produccion')->where('id', $prodId)
                ->update(['vendido' => 1, 'ingreso_creado' => 1]);
            $msgExtra = ' Ingreso creado: $' . number_format($valorTotal, 0, ',', '.');
        }

        if ($destino === 'inventario') {
            // Agregar al inventario automáticamente
            $invId = $this->agregarAInventario(
                $uid, $request, $animal, $valorTotal, $prodId
            );
            if ($invId) {
                DB::table('animal_produccion')->where('id', $prodId)
                    ->update(['inventario_id' => $invId]);
                $msgExtra = ' Agregado al inventario.';
            }
        }

        if ($destino === 'transformacion') {
            $msgExtra = ' Registrado como ' . ($request->transformacion_tipo ?? 'producto transformado') . '.';
        }

        $sesionLabel = $this->getSesionLabel($request->tipo_produccion, $request->sesion ?? 'unica');
        $msg = $sesionLabel . ': ' . $request->cantidad . ' ' . $request->unidad .
               ' — ' . (self::DESTINOS[$destino] ?? $destino) . $msgExtra;

        return redirect()->route('produccion-animal.index', ['fecha' => $request->fecha])
            ->with('msg', $msg)->with('msgType', 'success');
    }

    // ─────────────────────────────────────────────────────────────────
    // PRODUCTIVIDAD — Comparativa por animal individual
    // ─────────────────────────────────────────────────────────────────

    public function productividad(Request $request)
    {
        $uid     = session('usuario_id');
        $meses   = min(6, max(1, (int) ($request->meses ?? 3)));
        $tipo    = $request->tipo ?? null;
        $desde   = now()->subMonths($meses)->toDateString();

        // Producción acumulada por animal en el período
        $porAnimal = DB::table('animal_produccion as p')
            ->join('animales as a', 'a.id', '=', 'p.animal_id')
            ->where('p.usuario_id', $uid)
            ->where('p.fecha', '>=', $desde)
            ->when($tipo, fn($q) => $q->where('p.tipo_produccion', $tipo))
            ->select(
                'a.id', 'a.nombre_lote', 'a.especie', 'a.foto',
                'a.cantidad as num_animales',
                'p.tipo_produccion', 'p.unidad',
                DB::raw('SUM(p.cantidad) as total_cantidad'),
                DB::raw('SUM(p.valor_total) as total_valor'),
                DB::raw('COUNT(DISTINCT p.fecha) as dias_registrados'),
                DB::raw('AVG(p.cantidad) as promedio_diario')
            )
            ->groupBy('a.id', 'a.nombre_lote', 'a.especie', 'a.foto',
                      'a.cantidad', 'p.tipo_produccion', 'p.unidad')
            ->orderByDesc('total_cantidad')
            ->get();

        // Calcular producción por cabeza individual
        $porAnimal = $porAnimal->map(function ($r) use ($uid, $desde) {
            $r->prod_por_cabeza = ($r->num_animales > 0)
                ? round($r->total_cantidad / $r->num_animales, 2)
                : $r->total_cantidad;

            // Costo estimado del período
            $costos = DB::table('gastos')
                ->where('usuario_id', $uid)
                ->where('animal_id', $r->id)
                ->where('fecha', '>=', $desde)
                ->sum('valor');
            $r->costo_periodo = round($costos, 0);
            $r->costo_unitario = $r->total_cantidad > 0
                ? round($costos / $r->total_cantidad, 2) : null;
            $r->margen_unitario = ($r->costo_unitario !== null && $r->total_valor > 0)
                ? round(($r->total_valor - $costos) / $r->total_cantidad, 2) : null;

            return $r;
        });

        // Top productor
        $topProductor = $porAnimal->sortByDesc('prod_por_cabeza')->first();

        // Tipos de producción disponibles
        $tiposDisponibles = DB::table('animal_produccion')
            ->where('usuario_id', $uid)
            ->distinct()->pluck('tipo_produccion');

        // Tendencia semanal del top productor
        $tendenciaSemanal = [];
        if ($topProductor) {
            $tendenciaSemanal = DB::table('animal_produccion')
                ->where('usuario_id', $uid)
                ->where('animal_id', $topProductor->id)
                ->where('fecha', '>=', $desde)
                ->selectRaw('WEEK(fecha) as semana, YEAR(fecha) as anio,
                             SUM(cantidad) as total, MIN(fecha) as fecha_inicio')
                ->groupBy('semana', 'anio')
                ->orderBy('anio')->orderBy('semana')
                ->get();
        }

        // Datos para la gráfica de barras de productividad
        $chartAnimales = $porAnimal->pluck('nombre_lote')->toArray();
        $chartProd     = $porAnimal->pluck('prod_por_cabeza')->toArray();
        $chartCostoU   = $porAnimal->map(fn($r) => $r->costo_unitario ?? 0)->toArray();

        // Sesiones AM/PM: distribución del día
        $distribucionSesion = DB::table('animal_produccion')
            ->where('usuario_id', $uid)
            ->where('fecha', '>=', $desde)
            ->when($tipo, fn($q) => $q->where('tipo_produccion', $tipo))
            ->selectRaw('sesion, SUM(cantidad) as total, COUNT(*) as registros')
            ->groupBy('sesion')
            ->orderByDesc('total')
            ->get();

        // Desglose por destino
        $desgloseDest = DB::table('animal_produccion')
            ->where('usuario_id', $uid)
            ->where('fecha', '>=', $desde)
            ->when($tipo, fn($q) => $q->where('tipo_produccion', $tipo))
            ->selectRaw('destino, SUM(cantidad) as total, SUM(valor_total) as valor')
            ->groupBy('destino')
            ->orderByDesc('total')
            ->get();

        $destinos = self::DESTINOS;

        return view('pages.produccion.productividad', compact(
            'porAnimal', 'topProductor', 'meses', 'tipo', 'desde',
            'tiposDisponibles', 'tendenciaSemanal',
            'chartAnimales', 'chartProd', 'chartCostoU',
            'distribucionSesion', 'desgloseDest', 'destinos'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // CALCULAR Y GUARDAR COSTOS POR PERÍODO
    // ─────────────────────────────────────────────────────────────────

    public function calcularCostos(Request $request)
    {
        $request->validate([
            'animal_id'    => 'required|integer',
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $uid      = session('usuario_id');
        $animalId = $request->animal_id;

        $resultado = $this->construirCostoPeriodo($animalId, $uid,
            $request->fecha_inicio, $request->fecha_fin);

        // Guardar o actualizar el análisis
        DB::table('produccion_costos_periodo')->updateOrInsert(
            ['animal_id' => $animalId, 'usuario_id' => $uid,
             'fecha_inicio' => $request->fecha_inicio, 'fecha_fin' => $request->fecha_fin],
            array_merge($resultado, [
                'creado_en'      => now()->toDateTimeString(),
                'actualizado_en' => now()->toDateTimeString(),
            ])
        );

        return redirect()->route('produccion-animal.productividad')
            ->with('msg', 'Costo por unidad calculado: $' .
                number_format($resultado['costo_por_unidad'] ?? 0, 2) .
                ' por ' . $resultado['unidad'] . '.')
            ->with('msgType', 'success');
    }

    // ─────────────────────────────────────────────────────────────────
    // DESTROY
    // ─────────────────────────────────────────────────────────────────

    public function destroy($id)
    {
        $uid = session('usuario_id');
        $prod = DB::table('animal_produccion')
            ->where('id', $id)->where('usuario_id', $uid)->first();

        if ($prod) {
            // Si fue a inventario, revertir el movimiento
            if ($prod->inventario_id) {
                DB::table('inventario_movimientos')
                    ->where('referencia_id', $id)
                    ->where('tipo_movimiento', 'entrada')
                    ->delete();
                // Actualizar stock del inventario
                $inv = DB::table('inventario')->find($prod->inventario_id);
                if ($inv) {
                    $nuevoStock = max(0, $inv->stock_actual - ($prod->cantidad ?? 0));
                    DB::table('inventario')->where('id', $prod->inventario_id)
                        ->update(['stock_actual' => $nuevoStock]);
                }
            }
            DB::table('animal_produccion')->where('id', $id)->delete();
        }

        return back()->with('msg', 'Registro eliminado.')->with('msgType', 'warning');
    }

    // ─────────────────────────────────────────────────────────────────
    // PRIVADOS
    // ─────────────────────────────────────────────────────────────────

    private function calcularCostoUnitario(int $animalId, int $uid,
                                            string $desde, string $hasta): ?float
    {
        // Gastos del animal en el período
        $gastoTotal = DB::table('gastos')
            ->where('usuario_id', $uid)->where('animal_id', $animalId)
            ->whereBetween('fecha', [$desde, $hasta])
            ->sum('valor');

        if (!$gastoTotal) return null;

        // Producción del animal en el período
        $prodTotal = DB::table('animal_produccion')
            ->where('usuario_id', $uid)->where('animal_id', $animalId)
            ->whereBetween('fecha', [$desde, $hasta])
            ->sum('cantidad');

        if (!$prodTotal) return null;

        return round($gastoTotal / $prodTotal, 4);
    }

    private function construirCostoPeriodo(int $animalId, int $uid,
                                            string $desde, string $hasta): array
    {
        $gastos = DB::table('gastos')
            ->where('usuario_id', $uid)->where('animal_id', $animalId)
            ->whereBetween('fecha', [$desde, $hasta])
            ->selectRaw('categoria, SUM(valor) as total')
            ->groupBy('categoria')->get();

        $costoAlim  = 0; $costoSan = 0; $costoMO = 0; $costoOtros = 0;
        foreach ($gastos as $g) {
            $cat = strtolower($g->categoria ?? '');
            if (str_contains($cat, 'aliment'))     $costoAlim  += $g->total;
            elseif (str_contains($cat, 'sanid') ||
                    str_contains($cat, 'veterin'))  $costoSan   += $g->total;
            elseif (str_contains($cat, 'mano') ||
                    str_contains($cat, 'labor'))    $costoMO    += $g->total;
            else                                    $costoOtros += $g->total;
        }
        $costoTotal = $costoAlim + $costoSan + $costoMO + $costoOtros;

        $prod = DB::table('animal_produccion')
            ->where('usuario_id', $uid)->where('animal_id', $animalId)
            ->whereBetween('fecha', [$desde, $hasta])
            ->selectRaw('SUM(cantidad) as total, AVG(precio_unitario) as precio_prom,
                         unidad, tipo_produccion')
            ->groupBy('unidad', 'tipo_produccion')
            ->orderByDesc('total')->first();

        $unidades   = $prod ? (float) $prod->total : 0;
        $costoUnit  = ($unidades > 0) ? round($costoTotal / $unidades, 4) : null;
        $precioProm = $prod ? (float) $prod->precio_prom : null;
        $margen     = ($costoUnit && $precioProm) ? round($precioProm - $costoUnit, 4) : null;

        return [
            'animal_id'           => $animalId,
            'usuario_id'          => $uid,
            'fecha_inicio'        => $desde,
            'fecha_fin'           => $hasta,
            'costo_alimentacion'  => $costoAlim,
            'costo_sanidad'       => $costoSan,
            'costo_mano_obra'     => $costoMO,
            'costo_otros'         => $costoOtros,
            'costo_total'         => $costoTotal,
            'unidades_producidas' => $unidades,
            'unidad'              => $prod->unidad ?? 'unidades',
            'tipo_produccion'     => $prod->tipo_produccion ?? null,
            'costo_por_unidad'    => $costoUnit,
            'precio_venta_promedio' => $precioProm,
            'margen_unitario'     => $margen,
        ];
    }

    private function agregarAInventario(int $uid, Request $request,
                                        $animal, ?float $valor, int $prodId): ?int
    {
        try {
            // Buscar si ya existe este producto en inventario
            $nombre = ucfirst($request->tipo_produccion) . ' de ' .
                      ($animal->nombre_lote ?? $animal->especie);

            $inv = DB::table('inventario')
                ->where('usuario_id', $uid)
                ->where('nombre', $nombre)->first();

            if ($inv) {
                DB::table('inventario')->where('id', $inv->id)->update([
                    'stock_actual' => $inv->stock_actual + $request->cantidad,
                    'actualizado_en' => now()->toDateTimeString(),
                ]);
                $invId = $inv->id;
            } else {
                $invId = DB::table('inventario')->insertGetId([
                    'usuario_id'    => $uid,
                    'nombre'        => $nombre,
                    'categoria'     => 'produccion_propia',
                    'unidad'        => $request->unidad,
                    'stock_actual'  => $request->cantidad,
                    'stock_minimo'  => 0,
                    'precio_costo'  => $request->precio_unitario ?: null,
                    'precio_venta'  => $request->precio_unitario ?: null,
                    'animal_id'     => $request->animal_id,
                    'creado_en'     => now()->toDateTimeString(),
                    'actualizado_en'=> now()->toDateTimeString(),
                ]);
            }

            // Registrar movimiento
            DB::table('inventario_movimientos')->insert([
                'inventario_id'   => $invId,
                'usuario_id'      => $uid,
                'tipo_movimiento' => 'entrada',
                'cantidad'        => $request->cantidad,
                'precio_unitario' => $request->precio_unitario ?: null,
                'motivo'          => 'Produccion animal — ' . ($animal->nombre_lote ?? ''),
                'referencia_id'   => $prodId,
                'creado_en'       => now()->toDateTimeString(),
            ]);

            return $invId;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getSesionLabel(string $tipo, string $sesion): string
    {
        $map = self::SESIONES;
        $tipoKey = strtolower($tipo);
        foreach ($map as $k => $sesiones) {
            if (str_contains($tipoKey, $k)) {
                return $sesiones[$sesion] ?? ucfirst($sesion);
            }
        }
        return ucfirst($sesion);
    }
}