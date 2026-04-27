<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnimalController extends Controller
{
    private function emojis(): array {
        return ['Ganado bovino'=>'🐄','Cerdos'=>'🐷','Gallinas'=>'🐔','Conejos'=>'🐰',
            'Cabras'=>'🐐','Ovejas'=>'🐑','Caballos'=>'🐴','Peces'=>'🐟',
            'Patos'=>'🦆','Pavos'=>'🦃','Cerdas de cría'=>'🐷','Terneros'=>'🐮','Otro'=>'🐾'];
    }

    private function especies(): array {
        return ['Ganado bovino','Terneros','Cerdos','Cerdas de cría','Gallinas',
            'Patos','Pavos','Conejos','Cabras','Ovejas','Caballos','Peces','Otro'];
    }

    /** Especies que se venden por kilo */
    private function vendePorKilo(string $especie): bool {
        return in_array($especie, ['Ganado bovino','Terneros','Cerdos','Cerdas de cría','Cabras','Ovejas','Caballos']);
    }

    private function guardarImagen($file, string $sub = 'animales'): string {
        $dir = public_path("img/{$sub}");
        if (!file_exists($dir)) mkdir($dir, 0775, true);
        $nombre = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $file->move($dir, $nombre);
        return "img/{$sub}/{$nombre}";
    }

    private function eliminarImagen(?string $ruta): void {
        if ($ruta) { $f = public_path($ruta); if (file_exists($f)) unlink($f); }
    }

    /* ── LISTADO ── */
    public function index(Request $request)
    {
        $uid   = session('usuario_id');
        $query = DB::table('animales')->where('usuario_id', $uid);
        if ($request->especie)   $query->where('especie', $request->especie);
        if ($request->ubicacion) $query->where('ubicacion', $request->ubicacion);
        if ($request->estado)    $query->where('estado', $request->estado);
        if ($request->q)         $query->where('nombre_lote','like',"%{$request->q}%");

        $animales = $query->orderByRaw("FIELD(estado,'activo','vendido','muerte')")
                          ->orderBy('favorito','desc')->orderBy('especie')->orderBy('id','desc')->get();

        // Stats generales
        $statsPorEspecie = DB::table('animales')->where('usuario_id',$uid)->where('estado','activo')
            ->selectRaw('especie, SUM(cantidad) as total')->groupBy('especie')->orderByDesc('total')->get();
        $totalActivos = $statsPorEspecie->sum('total');

        // Ubicaciones únicas para filtro
        $ubicaciones = DB::table('animales')->where('usuario_id',$uid)
            ->whereNotNull('ubicacion')->distinct()->pluck('ubicacion');

        // Animales que necesitan atención
        $atencion = DB::table('animales')->where('usuario_id',$uid)
            ->where('atencion_especial',1)->where('estado','activo')->count();

        // Próximas dosis (eventos con proxima_dosis en los próximos 7 días)
        try {
            $proximasDosis = DB::table('animal_eventos as ae')
                ->join('animales as a','a.id','=','ae.animal_id')
                ->where('ae.usuario_id',$uid)
                ->whereNotNull('ae.proxima_dosis')
                ->whereBetween('ae.proxima_dosis',[now()->toDateString(), now()->addDays(7)->toDateString()])
                ->select('ae.*','a.nombre_lote','a.especie')
                ->orderBy('ae.proxima_dosis')->get();
        } catch (\Exception $e) { $proximasDosis = collect(); }

        $emojis  = $this->emojis();
        $especies = $this->especies();

        return view('pages.animales', compact(
            'animales','totalActivos','statsPorEspecie','ubicaciones',
            'atencion','proximasDosis','emojis','especies'
        ));
    }

    /* ── DETALLE ── */
    public function show($id)
    {
        $uid    = session('usuario_id');
        $animal = DB::table('animales')->where('id',$id)->where('usuario_id',$uid)->first();
        if (!$animal) abort(404);

        $gastos    = DB::table('gastos')->where('animal_id',$id)->where('usuario_id',$uid)->orderBy('fecha','desc')->get();
        $ingresos  = DB::table('ingresos')->where('animal_id',$id)->where('usuario_id',$uid)->orderBy('fecha','desc')->get();
        $totalGastos   = $gastos->sum('valor');
        $totalIngresos = $ingresos->sum('valor_total');

        try { $fotos      = DB::table('animal_fotos')->where('animal_id',$id)->where('usuario_id',$uid)->orderBy('creado_en','desc')->get(); } catch (\Exception $e) { $fotos = collect(); }
        try { $pesos      = DB::table('animal_pesos')->where('animal_id',$id)->where('usuario_id',$uid)->orderBy('fecha','desc')->get(); } catch (\Exception $e) { $pesos = collect(); }
        try { $eventos    = DB::table('animal_eventos')->where('animal_id',$id)->where('usuario_id',$uid)->orderBy('fecha','desc')->get(); } catch (\Exception $e) { $eventos = collect(); }
        try { $propietarios = DB::table('animal_propietarios')->where('animal_id',$id)->where('usuario_id',$uid)->get(); } catch (\Exception $e) { $propietarios = collect(); }

        // Timeline unificado
        $timeline = collect();
        foreach ($eventos as $ev) {
            $timeline->push(['tipo'=>$ev->tipo,'titulo'=>$ev->titulo,'descripcion'=>$ev->descripcion,
                'fecha'=>$ev->fecha,'foto'=>$ev->foto_ruta,'dosis'=>$ev->dosis,
                'proxima_dosis'=>$ev->proxima_dosis,'origen'=>'evento','id'=>$ev->id]);
        }
        foreach ($gastos as $g) {
            $timeline->push(['tipo'=>'gasto','titulo'=>'💰 '.$g->descripcion,
                'descripcion'=>'$'.number_format($g->valor,0,',','.').' · '.$g->categoria,
                'fecha'=>$g->fecha,'foto'=>null,'dosis'=>null,'proxima_dosis'=>null,'origen'=>'gasto','id'=>$g->id]);
        }
        foreach ($pesos as $p) {
            $timeline->push(['tipo'=>'peso','titulo'=>'⚖️ Pesaje: '.$p->peso.' '.$p->unidad,
                'descripcion'=>$p->notas,'fecha'=>$p->fecha,'foto'=>null,'dosis'=>null,
                'proxima_dosis'=>null,'origen'=>'peso','id'=>$p->id]);
        }
        $timeline = $timeline->sortByDesc('fecha')->values();

        // Cálculo de venta estimada
        $valorVentaEst = null;
        if ($animal->vende_por_kilo && $animal->precio_kilo && $animal->peso_promedio) {
            $valorVentaEst = $animal->precio_kilo * $animal->peso_promedio * $animal->cantidad;
        } elseif (!($animal->vende_por_kilo ?? true) && $animal->precio_unidad) {
            $valorVentaEst = $animal->precio_unidad * $animal->cantidad;
        }

        $emojis  = $this->emojis();
        $especies = $this->especies();
        $personas = DB::table('personas')->where('usuario_id',$uid)->where('activo',1)->orderBy('nombre')->get();
        return view('pages.animal-detalle', compact(
            'animal','gastos','ingresos','totalGastos','totalIngresos',
            'fotos','pesos','eventos','propietarios','timeline',
            'valorVentaEst','emojis','especies'
        ));
    }

    /* ── CREAR ── */
    public function store(Request $request)
    {
        $request->validate(['especie'=>'required']);
        $uid = session('usuario_id');
        $foto = null;
        if ($request->hasFile('foto')) $foto = $this->guardarImagen($request->file('foto'),'animales');

        $id = DB::table('animales')->insertGetId([
            'usuario_id'      => $uid,
            'especie'         => $request->especie,
            'nombre_lote'     => $request->nombre_lote,
            'cantidad'        => $request->cantidad ?? 1,
            'fecha_ingreso'   => $request->fecha_ingreso ?: null,
            'fecha_nacimiento'=> $request->fecha_nacimiento ?: null,
            'estado'          => $request->estado ?? 'activo',
            'peso_promedio'   => $request->peso_promedio ?: null,
            'unidad_peso'     => $request->unidad_peso ?? 'kg',
            'ubicacion'       => $request->ubicacion,
            'propietario'     => $request->propietario,
            'etapa_vida'      => $request->etapa_vida ?? 'adulto',
            'produccion'      => $request->produccion,
            'vende_por_kilo'  => $request->has('vende_por_kilo') ? 1 : 0,
            'precio_kilo'     => $request->precio_kilo ?: null,
            'precio_unidad'   => $request->precio_unidad ?: null,
            'favorito'        => 0,
            'atencion_especial'=>0,
            'notas'           => $request->notas,
            'foto'            => $foto,
            'creado_en'       => now()->toDateTimeString(),
            'actualizado_en'  => now()->toDateTimeString(),
        ]);

        // Evento inicial
        try {
            DB::table('animal_eventos')->insert([
                'animal_id'=>$id,'usuario_id'=>$uid,'tipo'=>'nota',
                'titulo'=>'🐾 Animal registrado','descripcion'=>'Ingreso de '.$request->especie.($request->nombre_lote ? ' - '.$request->nombre_lote : ''),
                'fecha'=>$request->fecha_ingreso ?? now()->toDateString(),'creado_en'=>now()->toDateTimeString(),
            ]);
        } catch (\Exception $e) {}

        return redirect()->route('animales.index')->with('msg','Animal registrado.')->with('msgType','success');
    }

    /* ── ACTUALIZAR ── */
   public function update(Request $request, $id)
{
    $request->validate(['especie'=>'required']);
    $uid    = session('usuario_id');
    $animal = DB::table('animales')->where('id',$id)->where('usuario_id',$uid)->first();
    if (!$animal) abort(404);

    // Guardar el estado ANTES de actualizar
    $estadoAnterior = $animal->estado;

    $data = [
        'especie'=>$request->especie,'nombre_lote'=>$request->nombre_lote,
        'cantidad'=>$request->cantidad??1,'fecha_ingreso'=>$request->fecha_ingreso?:null,
        'fecha_nacimiento'=>$request->fecha_nacimiento?:null,
        'estado'=>$request->estado,'peso_promedio'=>$request->peso_promedio?:null,
        'unidad_peso'=>$request->unidad_peso,'ubicacion'=>$request->ubicacion,
        'propietario'=>$request->propietario,'etapa_vida'=>$request->etapa_vida??'adulto',
        'produccion'=>$request->produccion,'vende_por_kilo'=>$request->has('vende_por_kilo')?1:0,
        'precio_kilo'=>$request->precio_kilo?:null,'precio_unidad'=>$request->precio_unidad?:null,
        'atencion_motivo'=>$request->atencion_motivo,'notas'=>$request->notas,
        'actualizado_en'=>now()->toDateTimeString(),
    ];

    if ($request->hasFile('foto')) {
        $this->eliminarImagen($animal->foto);
        $data['foto'] = $this->guardarImagen($request->file('foto'),'animales');
    }

    DB::table('animales')->where('id',$id)->where('usuario_id',$uid)->update($data);

    // ✅ =============================================================
    // ✅ CREAR INGRESO AUTOMÁTICO si el animal cambió de estado a "vendido"
    // ✅ =============================================================
    if ($estadoAnterior === 'activo' && $request->estado === 'vendido') {
        
        // Calcular valor de venta si no viene en el request
        $valorVenta = $request->valor_venta;
        if (!$valorVenta) {
            if ($request->vende_por_kilo && $request->precio_kilo && $request->peso_promedio) {
                $valorVenta = $request->precio_kilo * $request->peso_promedio * ($request->cantidad ?? 1);
            } elseif ($request->precio_unidad) {
                $valorVenta = $request->precio_unidad * ($request->cantidad ?? 1);
            }
        }

        // Verificar si ya existe un ingreso para este animal (evitar duplicados)
        if ($valorVenta && $valorVenta > 0) {
            $yaExiste = DB::table('ingresos')
                ->where('usuario_id', $uid)
                ->where('animal_id', $animal->id)
                ->where('tipo', 'animal')
                ->exists();

            if (!$yaExiste) {
                DB::table('ingresos')->insert([
                    'usuario_id'      => $uid,
                    'descripcion'     => 'Venta de ' . $animal->especie .
                                         ($animal->nombre_lote ? ' — ' . $animal->nombre_lote : ''),
                    'valor_total'     => $valorVenta,
                    'categoria'       => 'Venta de animales',
                    'fecha'           => $request->fecha_venta ?? now()->toDateString(),
                    'tipo'            => 'animal',
                    'animal_id'       => $animal->id,
                    'comprador'       => $request->comprador,
                    'notas'           => $request->notas,
                    'creado_en'       => now(),
                    'actualizado_en'  => now(),
                ]);
            }
        }
    }

    $back = $request->input('back','list');
    if ($back === 'detalle') {
        return redirect()->route('animales.show',$id)->with('msg','Animal actualizado.')->with('msgType','success');
    }
    return redirect()->route('animales.index')->with('msg','Animal actualizado.')->with('msgType','success');
}

    /* ── ELIMINAR ── */
    public function destroy($id)
    {
        $uid    = session('usuario_id');
        $animal = DB::table('animales')->where('id',$id)->where('usuario_id',$uid)->first();
        if ($animal) $this->eliminarImagen($animal->foto ?? null);
        DB::table('animales')->where('id',$id)->where('usuario_id',$uid)->delete();
        return redirect()->route('animales.index')->with('msg','Animal eliminado.')->with('msgType','warning');
    }

    /* ── TOGGLE FAVORITO ── */
    public function toggleFavorito($id)
    {
        $uid    = session('usuario_id');
        $animal = DB::table('animales')->where('id',$id)->where('usuario_id',$uid)->first();
        if ($animal) DB::table('animales')->where('id',$id)->update(['favorito'=>$animal->favorito?0:1]);
        return back()->with('msg', $animal->favorito ? 'Quitado de favoritos.' : 'Marcado como favorito ⭐')->with('msgType','success');
    }

    /* ── TOGGLE ATENCIÓN ── */
    public function toggleAtencion(Request $request, $id)
    {
        $uid    = session('usuario_id');
        $animal = DB::table('animales')->where('id',$id)->where('usuario_id',$uid)->first();
        if ($animal) DB::table('animales')->where('id',$id)->update([
            'atencion_especial'=>$animal->atencion_especial?0:1,
            'atencion_motivo'=>$request->motivo??$animal->atencion_motivo,
        ]);
        return back()->with('msg','Estado de atención actualizado.')->with('msgType','success');
    }

    /* ── SUBIR FOTO ── */
    public function uploadFoto(Request $request, $id)
    {
        $request->validate(['foto'=>'required|image|max:5120']);
        $uid    = session('usuario_id');
        $animal = DB::table('animales')->where('id',$id)->where('usuario_id',$uid)->first();
        if (!$animal) abort(404);
        $ruta   = $this->guardarImagen($request->file('foto'),'animales/fotos');
        DB::table('animal_fotos')->insert(['animal_id'=>$id,'usuario_id'=>$uid,'ruta'=>$ruta,
            'titulo'=>$request->titulo,'descripcion'=>$request->descripcion,'creado_en'=>now()->toDateTimeString()]);
        return redirect()->route('animales.show',$id)->with('msg','Foto agregada.')->with('msgType','success');
    }

    /* ── ELIMINAR FOTO ── */
    public function deleteFoto($animalId, $fotoId)
    {
        $uid  = session('usuario_id');
        $foto = DB::table('animal_fotos')->where('id',$fotoId)->where('animal_id',$animalId)->where('usuario_id',$uid)->first();
        if ($foto) { $this->eliminarImagen($foto->ruta); DB::table('animal_fotos')->where('id',$fotoId)->delete(); }
        return redirect()->route('animales.show',$animalId)->with('msg','Foto eliminada.')->with('msgType','warning');
    }

    /* ── REGISTRAR PESO ── */
    public function storePeso(Request $request, $id)
    {
        $request->validate(['peso'=>'required|numeric','fecha'=>'required|date']);
        $uid = session('usuario_id');
        DB::table('animal_pesos')->insert(['animal_id'=>$id,'usuario_id'=>$uid,
            'peso'=>$request->peso,'unidad'=>$request->unidad??'kg',
            'fecha'=>$request->fecha,'notas'=>$request->notas,'creado_en'=>now()->toDateTimeString()]);
        // Actualizar peso promedio en animales
        DB::table('animales')->where('id',$id)->where('usuario_id',$uid)->update([
            'peso_promedio'=>$request->peso,'unidad_peso'=>$request->unidad??'kg','actualizado_en'=>now()->toDateTimeString()
        ]);
        return redirect()->route('animales.show',$id)->with('msg','Peso registrado.')->with('msgType','success');
    }

    /* ── REGISTRAR EVENTO ── */
    public function storeEvento(Request $request, $id)
    {
        $request->validate(['tipo'=>'required','titulo'=>'required','fecha'=>'required|date']);
        $uid = session('usuario_id');
        $fotoRuta = null;
        if ($request->hasFile('foto')) $fotoRuta = $this->guardarImagen($request->file('foto'),'animales/eventos');
        DB::table('animal_eventos')->insert(['animal_id'=>$id,'usuario_id'=>$uid,
            'tipo'=>$request->tipo,'titulo'=>$request->titulo,'descripcion'=>$request->descripcion,
            'foto_ruta'=>$fotoRuta,'fecha'=>$request->fecha,'dosis'=>$request->dosis,
            'proxima_dosis'=>$request->proxima_dosis?:null,
            'persona_id'=>$request->persona_id ?: null,
            'creado_en'=>now()->toDateTimeString()]);
        return redirect()->route('animales.show',$id)->with('msg','Evento registrado.')->with('msgType','success');
    }

    /* ── ELIMINAR EVENTO ── */
    public function destroyEvento($animalId, $eventoId)
    {
        $uid    = session('usuario_id');
        $evento = DB::table('animal_eventos')->where('id',$eventoId)->where('animal_id',$animalId)->where('usuario_id',$uid)->first();
        if ($evento) $this->eliminarImagen($evento->foto_ruta??null);
        DB::table('animal_eventos')->where('id',$eventoId)->where('usuario_id',$uid)->delete();
        return redirect()->route('animales.show',$animalId)->with('msg','Evento eliminado.')->with('msgType','warning');
    }

    /* ── REGISTRAR PROPIETARIO ── */
    public function storePropietario(Request $request, $id)
    {
        $request->validate(['nombre'=>'required']);
        try {
            DB::table('animal_propietarios')->insert(['animal_id'=>$id,'usuario_id'=>session('usuario_id'),
                'nombre'=>$request->nombre,'porcentaje'=>$request->porcentaje??100,
                'telefono'=>$request->telefono,'notas'=>$request->notas,'creado_en'=>now()->toDateTimeString()]);
        } catch (\Exception $e) {}
        return redirect()->route('animales.show',$id)->with('msg','Propietario agregado.')->with('msgType','success');
    }

    /* ── ELIMINAR PROPIETARIO ── */
    public function destroyPropietario($animalId, $propId)
    {
        DB::table('animal_propietarios')->where('id',$propId)->where('animal_id',$animalId)->where('usuario_id',session('usuario_id'))->delete();
        return redirect()->route('animales.show',$animalId)->with('msg','Propietario eliminado.')->with('msgType','warning');
    }

    /* ── REGISTRAR VENTA/SACRIFICIO ── */
    public function registrarSalida(Request $request, $id)
    {
        $request->validate(['tipo_salida'=>'required','fecha'=>'required']);
        $uid    = session('usuario_id');
        $animal = DB::table('animales')->where('id',$id)->where('usuario_id',$uid)->first();
        if (!$animal) abort(404);

        $nuevoEstado = $request->tipo_salida === 'sacrificio' ? 'muerte' : 'vendido';
        $valorVenta  = $request->valor_venta ?: null;

        // Calcular valor si no se indicó
        if (!$valorVenta && $request->tipo_salida === 'venta') {
            if ($animal->vende_por_kilo && $animal->precio_kilo && $animal->peso_promedio) {
                $valorVenta = $animal->precio_kilo * $animal->peso_promedio * $animal->cantidad;
            } elseif ($animal->precio_unidad) {
                $valorVenta = $animal->precio_unidad * $animal->cantidad;
            }
        }

        DB::table('animales')->where('id',$id)->where('usuario_id',$uid)->update([
            'estado'      => $nuevoEstado,
            'fecha_venta' => $request->tipo_salida==='venta' ? $request->fecha : null,
            'fecha_sacrificio' => $request->tipo_salida==='sacrificio' ? $request->fecha : null,
            'valor_venta' => $valorVenta,
            'actualizado_en'=> now()->toDateTimeString(),
        ]);

        // Evento en timeline
        try {
            DB::table('animal_eventos')->insert(['animal_id'=>$id,'usuario_id'=>$uid,
                'tipo'=>$request->tipo_salida==='venta'?'venta':'sacrificio',
                'titulo'=>$request->tipo_salida==='venta'?'💰 Vendido':'🔪 Sacrificado',
                'descripcion'=>$valorVenta?'Valor: $'.number_format($valorVenta,0,',','.').' · '.($request->comprador??''):$request->notas_salida,
                'fecha'=>$request->fecha,'creado_en'=>now()->toDateTimeString()]);
        } catch (\Exception $e) {}

        // Crear ingreso automático si es venta
        if ($request->tipo_salida==='venta' && $valorVenta) {
            DB::table('ingresos')->insert(['usuario_id'=>$uid,
                'descripcion'=>'Venta de '.$animal->especie.($animal->nombre_lote?' - '.$animal->nombre_lote:''),
                'valor_total'=>$valorVenta,'fecha'=>$request->fecha,
                'comprador'=>$request->comprador,'tipo'=>'animal','animal_id'=>$id,
                'notas'=>$request->notas_salida,'creado_en'=>now()->toDateTimeString()]);
        }

        return redirect()->route('animales.show',$id)->with('msg','Salida registrada correctamente.')->with('msgType','success');
    }
}