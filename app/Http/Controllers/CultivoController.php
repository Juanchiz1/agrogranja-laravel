<?php

namespace App\Http\Controllers;

use App\Traits\ManejadorImagenes;
use App\Http\Requests\CultivoRequest;
use App\Models\Cultivo;
use App\Models\Gasto;
use App\Models\Ingreso;
use App\Models\Tarea;
use App\Models\Cosecha;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CultivoController extends Controller
{
    use ManejadorImagenes;

    private function emojis(): array
    {
        return [
            'Maíz'=>'🌽','Yuca'=>'🍠','Plátano'=>'🍌','Arroz'=>'🌾','Frijol'=>'🫘',
            'Tomate'=>'🍅','Cebolla'=>'🧅','Ají'=>'🌶️','Papa'=>'🥔','Aguacate'=>'🥑',
            'Café'=>'☕','Cacao'=>'🍫','Limón'=>'🍋','Naranja'=>'🍊','Mango'=>'🥭',
            'Caña de azúcar'=>'🎋','Ganado bovino'=>'🐄','Cerdos'=>'🐷',
            'Gallinas'=>'🐔','Peces'=>'🐟','Caballos'=>'🐴','Cabras'=>'🐐',
        ];
    }

    private function tiposCultivo(): array
    {
        return [
            'Maíz','Yuca','Plátano','Arroz','Frijol','Tomate','Cebolla','Ají','Papa',
            'Aguacate','Limón','Naranja','Mango','Caña de azúcar','Café','Cacao',
            'Ganado bovino','Cerdos','Gallinas','Peces','Caballos','Cabras','Otro',
        ];
    }

    /**
     * Muestra el listado de cultivos con filtros y estadísticas por estado.
     */
    public function index(Request $request)
    {
        $uid   = session('usuario_id');
        $query = Cultivo::delUsuario($uid);

        if ($request->q) {
            $q = $request->q;
            $query->where(fn($w) => $w->where('nombre', 'like', "%$q%")
                                      ->orWhere('tipo', 'like', "%$q%"));
        }

        if ($request->estado) {
            $query->where('estado', $request->estado);
        }

        $cultivos     = $query->latest('id')->get();
        $stats        = Cultivo::delUsuario($uid)
                            ->selectRaw('estado, count(*) as c')
                            ->groupBy('estado')
                            ->pluck('c', 'estado');
        $tiposCultivo = $this->tiposCultivo();

        return view('pages.cultivos', compact('cultivos', 'stats', 'tiposCultivo'));
    }

    /**
     * Muestra el detalle completo de un cultivo con timeline unificado.
     */
    public function show($id)
    {
        $uid     = session('usuario_id');
        $cultivo = Cultivo::where('id', $id)->where('usuario_id', $uid)->firstOrFail();

        $gastos        = Gasto::where('cultivo_id', $id)->where('usuario_id', $uid)->orderBy('fecha', 'desc')->get();
        $totalGastos   = $gastos->sum('valor');

        $ingresos      = Ingreso::where('cultivo_id', $id)->where('usuario_id', $uid)->orderBy('fecha', 'desc')->get();
        $totalIngresos = $ingresos->sum('valor_total');

        $tareas            = Tarea::where('cultivo_id', $id)->where('usuario_id', $uid)->orderBy('fecha', 'desc')->get();
        $tareasCompletadas = $tareas->where('completada', true)->count();
        $tareasPendientes  = $tareas->where('completada', false)->count();

        $cosechas       = Cosecha::where('cultivo_id', $id)->where('usuario_id', $uid)->orderBy('fecha_cosecha', 'desc')->get();
        $totalCosechado = $cosechas->sum('cantidad');

        // Movimientos de inventario — mantiene JOIN en DB::table porque
        // necesita columnas de dos tablas en una sola colección
        try {
            $movimientos = DB::table('inventario_movimientos as m')
                ->join('inventario as i', 'i.id', '=', 'm.inventario_id')
                ->where('m.cultivo_id', $id)->where('m.usuario_id', $uid)
                ->select('m.*', 'i.nombre as insumo_nombre', 'i.unidad as insumo_unidad')
                ->orderBy('m.fecha', 'desc')->get();
        } catch (\Exception $e) { $movimientos = collect(); }

        try {
            $fotos = DB::table('cultivo_fotos')
                ->where('cultivo_id', $id)->where('usuario_id', $uid)
                ->orderBy('creado_en', 'desc')->get();
        } catch (\Exception $e) { $fotos = collect(); }

        try {
            $eventosDB = DB::table('cultivo_eventos')
                ->where('cultivo_id', $id)->where('usuario_id', $uid)
                ->orderBy('fecha', 'desc')->get();
        } catch (\Exception $e) { $eventosDB = collect(); }

        // Timeline unificado — combina eventos, gastos, cosechas y tareas completadas
        $timeline = collect();

        foreach ($eventosDB as $ev) {
            $timeline->push(['tipo'=>$ev->tipo, 'titulo'=>$ev->titulo, 'descripcion'=>$ev->descripcion,
                'fecha'=>$ev->fecha, 'foto'=>$ev->foto_ruta, 'origen'=>'evento', 'id'=>$ev->id]);
        }
        foreach ($gastos as $g) {
            $timeline->push(['tipo'=>'gasto', 'titulo'=>'💰 '.$g->descripcion,
                'descripcion'=>'$'.number_format($g->valor, 0, ',', '.').' · '.$g->categoria,
                'fecha'=>$g->fecha, 'foto'=>null, 'origen'=>'gasto', 'id'=>$g->id]);
        }
        foreach ($cosechas as $co) {
            $timeline->push(['tipo'=>'cosecha', 'titulo'=>'🌾 Cosecha: '.$co->producto,
                'descripcion'=>$co->cantidad.' '.$co->unidad.' · calidad '.$co->calidad,
                'fecha'=>$co->fecha_cosecha, 'foto'=>null, 'origen'=>'cosecha', 'id'=>$co->id]);
        }
        foreach ($tareas->where('completada', true) as $t) {
            $timeline->push(['tipo'=>'tarea_completada', 'titulo'=>'✅ '.$t->titulo,
                'descripcion'=>'Tipo: '.$t->tipo, 'fecha'=>$t->fecha,
                'foto'=>null, 'origen'=>'tarea', 'id'=>$t->id]);
        }

        $timeline     = $timeline->sortByDesc('fecha')->values();
        $emojis       = $this->emojis();
        $tiposCultivo = $this->tiposCultivo();
        $personas     = Persona::delUsuario($uid)->activos()->orderBy('nombre')->get();

        return view('pages.cultivo-detalle', compact(
            'cultivo', 'gastos', 'totalGastos', 'ingresos', 'totalIngresos',
            'tareas', 'tareasCompletadas', 'tareasPendientes',
            'cosechas', 'totalCosechado', 'movimientos',
            'fotos', 'timeline', 'emojis', 'tiposCultivo'
        ));
    }

    /**
     * Registra un nuevo cultivo y su evento inicial en el timeline.
     */
    public function store(CultivoRequest $request)
    {
        $uid = session('usuario_id');

        $imagenPath = null;
        if ($request->hasFile('imagen')) {
            $imagenPath = $this->guardarImagen($request->file('imagen'), 'cultivos');
        }

        $cultivo = Cultivo::create([
            'usuario_id'    => $uid,
            'tipo'          => $request->tipo,
            'nombre'        => $request->nombre,
            'fecha_siembra' => $request->fecha_siembra ?? now()->toDateString(),
            'area'          => $request->area ?: null,
            'unidad'        => $request->unidad ?? 'hectareas',
            'estado'        => $request->estado ?? 'activo',
            'notas'         => $request->notas,
            'imagen'        => $imagenPath,
        ]);

        try {
            DB::table('cultivo_eventos')->insert([
                'cultivo_id'  => $cultivo->id,
                'usuario_id'  => $uid,
                'tipo'        => 'nota',
                'titulo'      => '🌱 Cultivo registrado',
                'descripcion' => 'Inicio del cultivo '.$request->nombre,
                'fecha'       => $request->fecha_siembra ?? now()->toDateString(),
                'creado_en'   => now()->toDateTimeString(),
            ]);
        } catch (\Exception $e) {}

        return redirect()->route('cultivos.index')
            ->with('msg', 'Cultivo registrado correctamente.')
            ->with('msgType', 'success');
    }

    /**
     * Actualiza los datos de un cultivo. Registra evento si cambia el estado.
     */
    public function update(CultivoRequest $request, $id)
    {
        $uid     = session('usuario_id');
        $cultivo = Cultivo::where('id', $id)->where('usuario_id', $uid)->firstOrFail();

        $estadoAnterior = $cultivo->estado;

        $data = [
            'tipo'          => $request->tipo,
            'nombre'        => $request->nombre,
            'fecha_siembra' => $request->fecha_siembra,
            'area'          => $request->area ?: null,
            'unidad'        => $request->unidad,
            'estado'        => $request->estado,
            'notas'         => $request->notas,
        ];

        if ($request->hasFile('imagen')) {
            $this->eliminarImagen($cultivo->imagen);
            $data['imagen'] = $this->guardarImagen($request->file('imagen'), 'cultivos');
        }

        $cultivo->update($data);

        if ($estadoAnterior !== $request->estado) {
            try {
                DB::table('cultivo_eventos')->insert([
                    'cultivo_id'  => $id,
                    'usuario_id'  => $uid,
                    'tipo'        => 'cambio_estado',
                    'titulo'      => 'Estado cambiado a: '.ucfirst($request->estado),
                    'descripcion' => 'Antes: '.$estadoAnterior,
                    'fecha'       => now()->toDateString(),
                    'creado_en'   => now()->toDateTimeString(),
                ]);
            } catch (\Exception $e) {}
        }

        $back = $request->input('back', 'list');
        if ($back === 'detalle') {
            return redirect()->route('cultivos.show', $id)
                ->with('msg', 'Cultivo actualizado.')->with('msgType', 'success');
        }

        return redirect()->route('cultivos.index')
            ->with('msg', 'Cultivo actualizado.')->with('msgType', 'success');
    }

    /**
     * Elimina un cultivo y su imagen principal.
     */
    public function destroy($id)
    {
        $uid     = session('usuario_id');
        $cultivo = Cultivo::where('id', $id)->where('usuario_id', $uid)->firstOrFail();
        $this->eliminarImagen($cultivo->imagen);
        $cultivo->delete();

        return redirect()->route('cultivos.index')
            ->with('msg', 'Cultivo eliminado.')->with('msgType', 'warning');
    }

    /**
     * Sube una foto al log fotográfico y registra un evento en el timeline.
     */
    public function uploadFoto(Request $request, $id)
    {
        $request->validate(['foto' => 'required|image|max:5120']);
        $uid     = session('usuario_id');
        $cultivo = Cultivo::where('id', $id)->where('usuario_id', $uid)->firstOrFail();

        $ruta   = $this->guardarImagen($request->file('foto'), 'cultivos/fotos');
        $fotoId = DB::table('cultivo_fotos')->insertGetId([
            'cultivo_id'  => $id,
            'usuario_id'  => $uid,
            'ruta'        => $ruta,
            'titulo'      => $request->titulo,
            'descripcion' => $request->descripcion,
            'creado_en'   => now()->toDateTimeString(),
        ]);

        DB::table('cultivo_eventos')->insert([
            'cultivo_id'   => $id,
            'usuario_id'   => $uid,
            'tipo'         => 'foto',
            'titulo'       => '📷 '.($request->titulo ?: 'Nueva foto agregada'),
            'descripcion'  => $request->descripcion,
            'foto_ruta'    => $ruta,
            'fecha'        => now()->toDateString(),
            'referencia_id'=> $fotoId,
            'creado_en'    => now()->toDateTimeString(),
        ]);

        return redirect()->route('cultivos.show', $id)
            ->with('msg', 'Foto agregada.')->with('msgType', 'success');
    }

    /**
     * Elimina una foto del log y su evento asociado en el timeline.
     */
    public function deleteFoto($cultivoId, $fotoId)
    {
        $uid  = session('usuario_id');
        $foto = DB::table('cultivo_fotos')
            ->where('id', $fotoId)->where('cultivo_id', $cultivoId)->where('usuario_id', $uid)
            ->first();

        if ($foto) {
            $this->eliminarImagen($foto->ruta);
            DB::table('cultivo_fotos')->where('id', $fotoId)->delete();
            DB::table('cultivo_eventos')
                ->where('referencia_id', $fotoId)->where('tipo', 'foto')
                ->where('cultivo_id', $cultivoId)->delete();
        }

        return redirect()->route('cultivos.show', $cultivoId)
            ->with('msg', 'Foto eliminada.')->with('msgType', 'warning');
    }

    /**
     * Registra un evento en el timeline del cultivo (aplicación, riego, nota, etc.).
     */
    public function storeEvento(Request $request, $id)
    {
        $request->validate([
            'tipo'   => 'required',
            'titulo' => 'required|string|max:200',
            'fecha'  => 'required|date',
        ]);

        $uid     = session('usuario_id');
        $cultivo = Cultivo::where('id', $id)->where('usuario_id', $uid)->firstOrFail();

        $fotoRuta = null;
        if ($request->hasFile('foto')) {
            $fotoRuta = $this->guardarImagen($request->file('foto'), 'cultivos/eventos');
        }

        DB::table('cultivo_eventos')->insert([
            'cultivo_id'  => $id,
            'usuario_id'  => $uid,
            'tipo'        => $request->tipo,
            'titulo'      => $request->titulo,
            'descripcion' => $request->descripcion,
            'foto_ruta'   => $fotoRuta,
            'fecha'       => $request->fecha,
            'persona_id'  => $request->persona_id ?: null,
            'creado_en'   => now()->toDateTimeString(),
        ]);

        return redirect()->route('cultivos.show', $id)
            ->with('msg', 'Evento registrado.')->with('msgType', 'success');
    }

    /**
     * Elimina un evento del timeline y su foto si tiene.
     */
    public function destroyEvento($cultivoId, $eventoId)
    {
        $uid    = session('usuario_id');
        $evento = DB::table('cultivo_eventos')
            ->where('id', $eventoId)->where('cultivo_id', $cultivoId)->where('usuario_id', $uid)
            ->first();

        if ($evento) {
            $this->eliminarImagen($evento->foto_ruta);
        }

        DB::table('cultivo_eventos')->where('id', $eventoId)->where('usuario_id', $uid)->delete();

        return redirect()->route('cultivos.show', $cultivoId)
            ->with('msg', 'Evento eliminado.')->with('msgType', 'warning');
    }
}