<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $uid  = session('usuario_id');
        $user = DB::table('usuarios')->find($uid);
        if ($user && !isset($user->created_at)) $user->created_at = $user->creado_en ?? null;

        $inicio   = now()->startOfMonth()->toDateString();
        $fin      = now()->endOfMonth()->toDateString();
        $hoy      = now()->toDateString();

        // Stats principales
        $cultivosActivos = DB::table('cultivos')->where('usuario_id',$uid)->where('estado','activo')->count();
        $gastosMes       = DB::table('gastos')->where('usuario_id',$uid)->whereBetween('fecha',[$inicio,$fin])->sum('valor');
        $ingresosMes     = DB::table('ingresos')->where('usuario_id',$uid)->whereBetween('fecha',[$inicio,$fin])->sum('valor_total');
        $tareasPend      = DB::table('tareas')->where('usuario_id',$uid)->where('completada',0)->where('fecha','<=',$hoy)->count();
        $animalesActivos = DB::table('animales')->where('usuario_id',$uid)->where('estado','activo')->sum('cantidad');

        // Tareas de hoy y vencidas
        $tareasHoy = DB::table('tareas')->where('usuario_id',$uid)->where('completada',0)
            ->whereDate('fecha',$hoy)->orderByRaw("FIELD(prioridad,'alta','media','baja')")->limit(4)->get();
        $tareasVencidas = DB::table('tareas')->where('usuario_id',$uid)->where('completada',0)
            ->where('fecha','<',$hoy)->count();

        // Últimos cultivos
        $recentCultivos = DB::table('cultivos')->where('usuario_id',$uid)->orderBy('id','desc')->limit(3)->get();

        // Alertas inventario
        $alertasInventario = DB::table('inventario')->where('usuario_id',$uid)->whereRaw('cantidad_actual <= stock_minimo')->count();

        // Próximas dosis animales
        try {
            $proximasDosis = DB::table('animal_eventos as ae')
                ->join('animales as a','a.id','=','ae.animal_id')
                ->where('ae.usuario_id',$uid)->whereNotNull('ae.proxima_dosis')
                ->whereBetween('ae.proxima_dosis',[$hoy, now()->addDays(7)->toDateString()])
                ->select('ae.titulo','ae.proxima_dosis','a.nombre_lote','a.especie')
                ->orderBy('ae.proxima_dosis')->limit(3)->get();
        } catch (\Exception $e) { $proximasDosis = collect(); }

        // Personas: pagado este mes a trabajadores
        try {
            $pagadoMesPersonas = DB::table('persona_pagos')->where('usuario_id',$uid)
                ->whereMonth('fecha',now()->month)->whereYear('fecha',now()->year)->sum('valor');
            $trabajadoresActivos = DB::table('personas')->where('usuario_id',$uid)->where('tipo','trabajador')->where('activo',1)->count();
        } catch (\Exception $e) { $pagadoMesPersonas = 0; $trabajadoresActivos = 0; }

        // Top cultivo rentable
        $topCultivo = DB::table('cultivos as c')->where('c.usuario_id',$uid)
            ->selectRaw('c.id, c.nombre, c.tipo,
                COALESCE((SELECT SUM(valor_total) FROM ingresos WHERE cultivo_id=c.id AND YEAR(fecha)=YEAR(CURDATE())),0)
                - COALESCE((SELECT SUM(valor) FROM gastos WHERE cultivo_id=c.id AND YEAR(fecha)=YEAR(CURDATE())),0)
                AS rentabilidad')
            ->orderByDesc('rentabilidad')->first();

        // Cosechas recientes
        $cosechasRecientes = DB::table('cosechas')->where('usuario_id',$uid)->orderBy('fecha_cosecha','desc')->limit(2)->get();

        return view('pages.dashboard', compact(
            'user','cultivosActivos','gastosMes','ingresosMes','tareasPend',
            'tareasHoy','tareasVencidas','recentCultivos','alertasInventario',
            'topCultivo','cosechasRecientes','animalesActivos',
            'proximasDosis','pagadoMesPersonas','trabajadoresActivos'
        ));
    }
}