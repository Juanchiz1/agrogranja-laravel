<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $uid  = session('usuario_id');
        $anio = $request->anio ?? now()->year;
        $tab  = $request->tab ?? 'resumen';

        $gastosPorMes   = DB::table('gastos')->where('usuario_id',$uid)->whereYear('fecha',$anio)->selectRaw('MONTH(fecha) as mes, SUM(valor) as total')->groupBy('mes')->orderBy('mes')->get()->keyBy('mes');
        $ingresosPorMes = DB::table('ingresos')->where('usuario_id',$uid)->whereYear('fecha',$anio)->selectRaw('MONTH(fecha) as mes, SUM(valor_total) as total')->groupBy('mes')->orderBy('mes')->get()->keyBy('mes');

        $gastosArr   = []; $ingresosArr = [];
        for ($m=1;$m<=12;$m++) { $gastosArr[]=$gastosPorMes[$m]->total??0; $ingresosArr[]=$ingresosPorMes[$m]->total??0; }

        $totalGastos   = array_sum($gastosArr);
        $totalIngresos = array_sum($ingresosArr);
        $gastosCat     = DB::table('gastos')->where('usuario_id',$uid)->whereYear('fecha',$anio)->selectRaw('categoria, SUM(valor) as total')->groupBy('categoria')->orderByDesc('total')->get();
        $cultivosEst   = DB::table('cultivos')->where('usuario_id',$uid)->selectRaw('estado, count(*) as c')->groupBy('estado')->pluck('c','estado');
        $tareasStats   = DB::table('tareas')->where('usuario_id',$uid)->selectRaw('COUNT(*) as total, SUM(completada) as completadas')->first();

        return view('pages.reportes', compact('anio','tab','gastosArr','ingresosArr','totalGastos','totalIngresos','gastosCat','cultivosEst','tareasStats'));
    }
}
