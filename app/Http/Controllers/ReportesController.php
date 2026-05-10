<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $uid  = session('usuario_id');
        $anio = $request->anio ?? now()->year;
        $tab  = $request->tab ?? 'resumen';

        /* ─── Ingresos y Gastos por mes ─── */
        $gastosPorMes   = DB::table('gastos')->where('usuario_id',$uid)->whereYear('fecha',$anio)
            ->selectRaw('MONTH(fecha) as mes, SUM(valor) as total')->groupBy('mes')->get()->keyBy('mes');
        $ingresosPorMes = DB::table('ingresos')->where('usuario_id',$uid)->whereYear('fecha',$anio)
            ->selectRaw('MONTH(fecha) as mes, SUM(valor_total) as total')->groupBy('mes')->get()->keyBy('mes');

        $gastosArr = []; $ingresosArr = []; $balanceArr = [];
        for ($m=1; $m<=12; $m++) {
            $g = $gastosPorMes[$m]->total ?? 0;
            $i = $ingresosPorMes[$m]->total ?? 0;
            $gastosArr[]   = round($g, 0);
            $ingresosArr[] = round($i, 0);
            $balanceArr[]  = round($i - $g, 0);
        }

        $totalGastos   = array_sum($gastosArr);
        $totalIngresos = array_sum($ingresosArr);
        $balance       = $totalIngresos - $totalGastos;

        /* ─── Gastos por categoría ─── */
        $gastosCat = DB::table('gastos')->where('usuario_id',$uid)->whereYear('fecha',$anio)
            ->selectRaw('categoria, SUM(valor) as total')->groupBy('categoria')->orderByDesc('total')->get();

        /* ─── Gastos: cultivo vs animal vs sin asociar ─── */
        $gastosCultivo = DB::table('gastos')->where('usuario_id',$uid)->whereYear('fecha',$anio)->whereNotNull('cultivo_id')->sum('valor');
        $gastosAnimal  = DB::table('gastos')->where('usuario_id',$uid)->whereYear('fecha',$anio)->whereNotNull('animal_id')->sum('valor');
        $gastosGeneral = $totalGastos - $gastosCultivo - $gastosAnimal;

        /* ─── Ingresos por tipo ─── */
        $ingresosTipo = DB::table('ingresos')->where('usuario_id',$uid)->whereYear('fecha',$anio)
            ->selectRaw('COALESCE(tipo,"venta") as tipo, SUM(valor_total) as total, COUNT(*) as cnt')
            ->groupBy('tipo')->orderByDesc('total')->get();

        /* ─── Top compradores ─── */
        $topCompradores = DB::table('ingresos')->where('usuario_id',$uid)->whereYear('fecha',$anio)
            ->whereNotNull('comprador')
            ->selectRaw('comprador, SUM(valor_total) as total, COUNT(*) as cnt')
            ->groupBy('comprador')->orderByDesc('total')->limit(5)->get();

        /* ─── Top proveedores ─── */
        $topProveedores = DB::table('gastos')->where('usuario_id',$uid)->whereYear('fecha',$anio)
            ->whereNotNull('proveedor')
            ->selectRaw('proveedor, SUM(valor) as total, COUNT(*) as cnt')
            ->groupBy('proveedor')->orderByDesc('total')->limit(5)->get();

        /* ─── Cultivos ─── */
        $cultivosEst = DB::table('cultivos')->where('usuario_id',$uid)
            ->selectRaw('estado, count(*) as c')->groupBy('estado')->pluck('c','estado');
        $cultivosTipo = DB::table('cultivos')->where('usuario_id',$uid)->where('estado','activo')
            ->selectRaw('tipo, COUNT(*) as c')->groupBy('tipo')->orderByDesc('c')->get();

        /* ─── Rentabilidad por cultivo ─── */
        $rentCultivos = DB::table('cultivos as cu')
            ->leftJoin(DB::raw('(SELECT cultivo_id, SUM(valor) as tg FROM gastos WHERE usuario_id='.$uid.' GROUP BY cultivo_id) as g'), 'g.cultivo_id','=','cu.id')
            ->leftJoin(DB::raw('(SELECT cultivo_id, SUM(valor_total) as ti FROM ingresos WHERE usuario_id='.$uid.' GROUP BY cultivo_id) as i'), 'i.cultivo_id','=','cu.id')
            ->where('cu.usuario_id',$uid)
            ->select('cu.nombre','cu.tipo',DB::raw('COALESCE(g.tg,0) as gastos'),DB::raw('COALESCE(i.ti,0) as ingresos'),DB::raw('COALESCE(i.ti,0)-COALESCE(g.tg,0) as balance'))
            ->orderByDesc('balance')->limit(8)->get();

        /* ─── Cosechas ─── */
        $cosechasPorMes = DB::table('cosechas')->where('usuario_id',$uid)->whereYear('fecha_cosecha',$anio)
            ->selectRaw('MONTH(fecha_cosecha) as mes, COUNT(*) as cnt, SUM(valor_estimado) as valor')->groupBy('mes')->get()->keyBy('mes');
        $cosechasArr = [];
        for ($m=1;$m<=12;$m++) $cosechasArr[] = round($cosechasPorMes[$m]->valor ?? 0, 0);
        $totalCosechas = DB::table('cosechas')->where('usuario_id',$uid)->whereYear('fecha_cosecha',$anio)->count();
        $valorCosechas = array_sum($cosechasArr);

        /* ─── Animales ─── */
        $animalesEst = DB::table('animales')->where('usuario_id',$uid)
            ->selectRaw('estado, SUM(cantidad) as c')->groupBy('estado')->pluck('c','estado');
        $animalesPorEspecie = DB::table('animales')->where('usuario_id',$uid)->where('estado','activo')
            ->selectRaw('especie, SUM(cantidad) as total')->groupBy('especie')->orderByDesc('total')->get();
        $valorHato = DB::table('animales')->where('usuario_id',$uid)->where('estado','activo')
            ->selectRaw('SUM(CASE WHEN vende_por_kilo=1 THEN precio_kilo*peso_promedio*cantidad ELSE precio_unidad*cantidad END) as valor')->value('valor') ?? 0;
        $ventasAnimales = DB::table('animales')->where('usuario_id',$uid)->whereYear('actualizado_en',$anio)->where('estado','vendido')->sum('valor_venta');

        /* ─── Tareas ─── */
        $tareasStats = DB::table('tareas')->where('usuario_id',$uid)
            ->selectRaw('COUNT(*) as total, SUM(completada) as completadas')->first();
        $tareasTipo = DB::table('tareas')->where('usuario_id',$uid)->where('completada',0)
            ->selectRaw('tipo, COUNT(*) as c')->groupBy('tipo')->orderByDesc('c')->limit(5)->get();

        /* ─── Insights automáticos ─── */
        $insights = [];
        if ($balance > 0) $insights[] = ['tipo'=>'positivo','texto'=>'Operando con ganancia de $'.number_format($balance,0,',','.').' este año.'];
        elseif ($balance < 0) $insights[] = ['tipo'=>'negativo','texto'=>'Los gastos superan los ingresos en $'.number_format(abs($balance),0,',','.').'. Revisa las categorías de mayor gasto.'];
        if ($gastosCat->count()) $insights[] = ['tipo'=>'info','texto'=>'Tu mayor gasto es en "'.$gastosCat->first()->categoria.'" con $'.number_format($gastosCat->first()->total,0,',','.').' ('.(round($gastosCat->first()->total/$totalGastos*100)).'% del total).'];
        $mesMayorIngreso = array_search(max($ingresosArr),$ingresosArr)+1;
        if (max($ingresosArr) > 0) { $meses=['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic']; $insights[] = ['tipo'=>'info','texto'=>'Tu mejor mes de ingresos fue '.$meses[$mesMayorIngreso-1].' con $'.number_format(max($ingresosArr),0,',','.').'.'];}
        $tareasPendientes = DB::table('tareas')->where('usuario_id',$uid)->where('completada',0)->where('fecha','<',now()->toDateString())->count();
        if ($tareasPendientes > 0) $insights[] = ['tipo'=>'alerta','texto'=>$tareasPendientes.' tarea(s) vencida(s) sin completar en la agenda.'];
        if ($animalesPorEspecie->count()) $insights[] = ['tipo'=>'info','texto'=>'Tienes '.$animalesEst['activo'].' animal(es) activo(s) con un valor estimado de hato de $'.number_format($valorHato,0,',','.').'.'];

        /* ─── Rentabilidad por cultivo (tab dedicado) ─── */
        $rentDatos = collect();
        if ($tab === 'rentabilidad') {
            $orden = $request->orden ?? 'rentabilidad';
            $cultivos = DB::table('cultivos')->where('usuario_id',$uid)->get();
            $rentDatos = $cultivos->map(function($cultivo) use ($uid,$anio) {
                $gastos    = DB::table('gastos')->where('usuario_id',$uid)->where('cultivo_id',$cultivo->id)->whereYear('fecha',$anio)->sum('valor');
                $ingresos  = DB::table('ingresos')->where('usuario_id',$uid)->where('cultivo_id',$cultivo->id)->whereYear('fecha',$anio)->sum('valor_total');
                $cosechas  = DB::table('cosechas')->where('usuario_id',$uid)->where('cultivo_id',$cultivo->id)->whereYear('fecha_cosecha',$anio)->sum('valor_estimado');
                $ingresoTotal = $ingresos > 0 ? $ingresos : $cosechas;
                $rent = $ingresoTotal - $gastos;
                $roi  = $gastos > 0 ? round(($rent/$gastos)*100,1) : ($ingresoTotal > 0 ? 100 : 0);
                $margen = $ingresoTotal > 0 ? round(($rent/$ingresoTotal)*100,1) : 0;
                $gastosCat = DB::table('gastos')->where('usuario_id',$uid)->where('cultivo_id',$cultivo->id)->whereYear('fecha',$anio)
                    ->selectRaw('categoria, SUM(valor) as total')->groupBy('categoria')->orderByDesc('total')->get();
                return (object)['id'=>$cultivo->id,'nombre'=>$cultivo->nombre,'tipo'=>$cultivo->tipo,
                    'estado'=>$cultivo->estado,'area'=>$cultivo->area,'unidad'=>$cultivo->unidad,
                    'gastos'=>(float)$gastos,'ingresos'=>(float)$ingresoTotal,'rentabilidad'=>(float)$rent,
                    'roi'=>$roi,'margen'=>$margen,'gastosPorCategoria'=>$gastosCat];
            });
            $rentDatos = match($orden ?? 'rentabilidad') {
                'nombre'   => $rentDatos->sortBy('nombre'),
                'ingresos' => $rentDatos->sortByDesc('ingresos'),
                'gastos'   => $rentDatos->sortByDesc('gastos'),
                default    => $rentDatos->sortByDesc('rentabilidad'),
            };
            $rentDatos = $rentDatos->values();
        } else {
            $orden = 'rentabilidad';
        }
        $rentMejor = $rentDatos->filter(fn($d) => $d->rentabilidad > 0)->sortByDesc('rentabilidad')->first();
        $rentPeor  = $rentDatos->filter(fn($d) => $d->gastos > 0 || $d->ingresos > 0)->sortBy('rentabilidad')->first();
        $rentChartLabels   = $rentDatos->pluck('nombre')->toArray();
        $rentChartGastos   = $rentDatos->pluck('gastos')->toArray();
        $rentChartIngresos = $rentDatos->pluck('ingresos')->toArray();

        // ── FASE 9: Rentabilidad por línea productiva ─────────────────
        $lineasActivas = \App\Models\LineaProductiva::activasDelUsuario($uid);
        $inicioAnio = $anio . '-01-01';
        $finAnio    = $anio . '-12-31';

        $rentabilidadLineas = [];
        $emojiLinea = ['cultivos'=>'🌱','bovino'=>'🐄','avicola'=>'🐔','porcino'=>'🐷',
                       'piscicola'=>'🐟','caprino_ovino'=>'🐐','equino'=>'🐴','cunicola'=>'🐰'];

        $especiesLinea = [
            'bovino'       => ['Ganado bovino','Terneros'],
            'avicola'      => ['Gallinas','Patos','Pavos'],
            'porcino'      => ['Cerdos','Cerdas de cría'],
            'piscicola'    => ['Peces'],
            'caprino_ovino'=> ['Cabras','Ovejas'],
            'equino'       => ['Caballos'],
            'cunicola'     => ['Conejos'],
        ];

        foreach ($lineasActivas as $linea) {
            $ing = 0; $gas = 0;
            if ($linea === 'cultivos') {
                $ing = DB::table('ingresos')->where('usuario_id',$uid)
                    ->whereBetween('fecha',[$inicioAnio,$finAnio])
                    ->whereNotNull('cultivo_id')->sum('valor_total');
                $gas = DB::table('gastos')->where('usuario_id',$uid)
                    ->whereBetween('fecha',[$inicioAnio,$finAnio])
                    ->whereNotNull('cultivo_id')->sum('valor');
            } elseif (isset($especiesLinea[$linea])) {
                $ids = DB::table('animales')->where('usuario_id',$uid)
                    ->whereIn('especie',$especiesLinea[$linea])->pluck('id');
                if ($ids->count()) {
                    $ing = DB::table('ingresos')->where('usuario_id',$uid)
                        ->whereBetween('fecha',[$inicioAnio,$finAnio])
                        ->whereIn('animal_id',$ids)->sum('valor_total');
                    $gas = DB::table('gastos')->where('usuario_id',$uid)
                        ->whereBetween('fecha',[$inicioAnio,$finAnio])
                        ->whereIn('animal_id',$ids)->sum('valor');
                }
            } else { continue; }

            $rentabilidadLineas[$linea] = [
                'nombre'       => ucfirst(str_replace('_',' ',$linea)),
                'emoji'        => $emojiLinea[$linea] ?? '🌾',
                'ingresos'     => round((float)$ing, 0),
                'gastos'       => round((float)$gas, 0),
                'rentabilidad' => round((float)$ing - (float)$gas, 0),
                'margen'       => $ing > 0 ? round((($ing-$gas)/$ing)*100,1) : 0,
                'es_rentable'  => ($ing - $gas) >= 0,
            ];
        }
        uasort($rentabilidadLineas, fn($a,$b) => $b['rentabilidad'] - $a['rentabilidad']);

        // KPIs específicos por línea
        $kpisLineasReporte = [];
        $diasAnio = now()->year == $anio ? now()->dayOfYear : 365;
        foreach ($lineasActivas as $linea) {
            try {
                if ($linea === 'bovino') {
                    $vacasL = DB::table('animales')->where('usuario_id',$uid)
                        ->where('estado','activo')->where('especie','Ganado bovino')
                        ->where('produccion','like','%leche%')->sum('cantidad');
                    $lecheT = DB::table('animal_produccion as ap')
                        ->join('animales as a','a.id','=','ap.animal_id')
                        ->where('ap.usuario_id',$uid)->whereYear('ap.fecha',$anio)
                        ->where('ap.tipo_produccion','leche')->sum('ap.cantidad');
                    if ($vacasL > 0 && $lecheT > 0) {
                        $kpisLineasReporte['bovino'] = [
                            'emoji' => '🐄', 'titulo' => 'KPIs Bovinos',
                            'items' => [
                                ['kpi'=>'Litros totales '.$anio,'valor'=>number_format((float)$lecheT,0).' L'],
                                ['kpi'=>'L/vaca/dia','valor'=>round($lecheT/($vacasL*$diasAnio),1).' L','meta'=>'10-25 L Holstein / 3-8 L criolla'],
                            ]
                        ];
                    }
                } elseif ($linea === 'avicola') {
                    $aves = DB::table('animales')->where('usuario_id',$uid)
                        ->where('estado','activo')->whereIn('especie',['Gallinas'])->sum('cantidad');
                    $hT = DB::table('animal_produccion as ap')
                        ->join('animales as a','a.id','=','ap.animal_id')
                        ->where('ap.usuario_id',$uid)->whereYear('ap.fecha',$anio)
                        ->where('ap.tipo_produccion','huevos')->sum('ap.cantidad');
                    if ($aves > 0 && $hT > 0) {
                        $kpisLineasReporte['avicola'] = [
                            'emoji' => '🐔', 'titulo' => 'KPIs Avicola',
                            'items' => [
                                ['kpi'=>'Huevos totales '.$anio,'valor'=>number_format((float)$hT,0).' uds'],
                                ['kpi'=>'Huevos/ave/dia','valor'=>round($hT/($aves*$diasAnio),3),'meta'=>'0.85-0.95 ideal'],
                                ['kpi'=>'Postura','valor'=>round(($hT/($aves*$diasAnio))*100,1).'%','meta'=>'> 85% excelente'],
                            ]
                        ];
                    }
                } elseif ($linea === 'piscicola') {
                    $cosechas = DB::table('piscicola_cosechas')->where('usuario_id',$uid)
                        ->whereYear('fecha_cosecha',$anio)->get();
                    if ($cosechas->count()) {
                        $kpisLineasReporte['piscicola'] = [
                            'emoji' => '🐟', 'titulo' => 'KPIs Piscicola',
                            'items' => [
                                ['kpi'=>'CA promedio','valor'=>round($cosechas->whereNotNull('conversion_alimenticia')->avg('conversion_alimenticia')??0,2),'meta'=>'< 1.5 Cachama'],
                                ['kpi'=>'Sobrevivencia','valor'=>round($cosechas->whereNotNull('sobrevivencia_pct')->avg('sobrevivencia_pct')??0,1).'%','meta'=>'> 90%'],
                                ['kpi'=>'kg/m2 promedio','valor'=>round($cosechas->whereNotNull('rendimiento_kg_m2')->avg('rendimiento_kg_m2')??0,2),'meta'=>'3-5 kg/m2 cachama'],
                            ]
                        ];
                    }
                }
            } catch (\Exception $e) {}
        }

        return view('pages.reportes', compact(
            'anio','tab','gastosArr','ingresosArr','balanceArr','totalGastos','totalIngresos','balance',
            'gastosCat','gastosCultivo','gastosAnimal','gastosGeneral',
            'ingresosTipo','topCompradores','topProveedores',
            'cultivosEst','cultivosTipo','rentCultivos',
            'cosechasArr','totalCosechas','valorCosechas',
            'animalesEst','animalesPorEspecie','valorHato','ventasAnimales',
            'tareasStats','tareasTipo','insights',
            'rentDatos','rentMejor','rentPeor','rentChartLabels','rentChartGastos','rentChartIngresos','orden',
            'lineasActivas','rentabilidadLineas','kpisLineasReporte'
        ));
    }
}