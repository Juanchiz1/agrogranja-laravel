<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $uid = session('usuario_id');
        $user = DB::table('usuarios')->find($uid);
        // Normalize column names for compatibility with both old and new DB schemas
        if ($user && !isset($user->created_at)) {
            $user->created_at = $user->creado_en ?? null;
        }

        $cultivosActivos = DB::table('cultivos')->where('usuario_id', $uid)->where('estado', 'activo')->count();
        $primerDia = now()->startOfMonth()->toDateString();
        $ultimoDia = now()->endOfMonth()->toDateString();

        $gastosMes   = DB::table('gastos')->where('usuario_id', $uid)->whereBetween('fecha', [$primerDia, $ultimoDia])->sum('valor');
        $ingresosMes = DB::table('ingresos')->where('usuario_id', $uid)->whereBetween('fecha', [$primerDia, $ultimoDia])->sum('valor_total');
        $tareasPend  = DB::table('tareas')->where('usuario_id', $uid)->where('completada', 0)->where('fecha', '>=', now()->toDateString())->count();
        
        $cosechasRecientes = DB::table('cosechas')
    ->where('usuario_id', $uid)
    ->orderBy('fecha_cosecha', 'desc')
    ->limit(3)
    ->get();

$valorCosechasMes = DB::table('cosechas')
    ->where('usuario_id', $uid)
    ->whereMonth('fecha_cosecha', now()->month)
    ->whereYear('fecha_cosecha', now()->year)
    ->sum('valor_estimado');

        $tareasHoy = DB::table('tareas')
            ->where('usuario_id', $uid)->where('completada', 0)->whereDate('fecha', today())
            ->orderByRaw("FIELD(prioridad,'alta','media','baja')")->limit(3)->get();

        $recentCultivos = DB::table('cultivos')->where('usuario_id', $uid)
            ->orderBy('id', 'desc')->limit(3)->get();

        // Alertas de inventario para el dashboard
$alertasInventario = DB::table('inventario')
    ->where('usuario_id', $uid)
    ->whereRaw('cantidad_actual <= stock_minimo')
    ->count();   
    
    // Top cultivo por rentabilidad este año
$topCultivo = DB::table('cultivos as c')
    ->where('c.usuario_id', $uid)
    ->selectRaw('c.id, c.nombre, c.tipo,
        COALESCE((SELECT SUM(valor_total) FROM ingresos WHERE cultivo_id=c.id AND YEAR(fecha)=YEAR(CURDATE())),0)
        - COALESCE((SELECT SUM(valor) FROM gastos WHERE cultivo_id=c.id AND YEAR(fecha)=YEAR(CURDATE())),0)
        AS rentabilidad')
    ->orderByDesc('rentabilidad')
    ->first();

        return view('pages.dashboard', compact(
            'user','cultivosActivos','gastosMes','ingresosMes','tareasPend','tareasHoy','recentCultivos','alertasInventario','topCultivo'
        ));

    }
}

