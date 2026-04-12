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

        return view('pages.reportes', compact(
            'anio','tab','gastosArr','ingresosArr','balanceArr','totalGastos','totalIngresos','balance',
            'gastosCat','gastosCultivo','gastosAnimal','gastosGeneral',
            'ingresosTipo','topCompradores','topProveedores',
            'cultivosEst','cultivosTipo','rentCultivos',
            'cosechasArr','totalCosechas','valorCosechas',
            'animalesEst','animalesPorEspecie','valorHato','ventasAnimales',
            'tareasStats','tareasTipo','insights'
        ));
    }
}