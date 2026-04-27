<?php

namespace App\Http\Controllers;

use App\Traits\ManejadorImagenes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnimalController extends Controller
{
    use ManejadorImagenes;

    private function emojis(): array
    {
        return [
            'Ganado bovino'=>'🐄','Cerdos'=>'🐷','Gallinas'=>'🐔','Conejos'=>'🐰',
            'Cabras'=>'🐐','Ovejas'=>'🐑','Caballos'=>'🐴','Peces'=>'🐟',
            'Patos'=>'🦆','Pavos'=>'🦃','Cerdas de cría'=>'🐷','Terneros'=>'🐮','Otro'=>'🐾',
        ];
    }

    private function especies(): array
    {
        return [
            'Ganado bovino','Terneros','Cerdos','Cerdas de cría','Gallinas',
            'Patos','Pavos','Conejos','Cabras','Ovejas','Caballos','Peces','Otro',
        ];
    }

    /** Determina si la especie se vende por kilo. */
    private function vendePorKilo(string $especie): bool
    {
        return in_array($especie, ['Ganado bovino','Terneros','Cerdos','Cerdas de cría','Cabras','Ovejas','Caballos']);
    }

    /**
     * Muestra el listado de animales con filtros y estadísticas.
     */
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

        $statsPorEspecie = DB::table('animales')->where('usuario_id',$uid)->where('estado','activo')
            ->selectRaw('especie, SUM(cantidad) as total')->groupBy('especie')->orderByDesc('total')->get();
        $totalActivos = $statsPorEspecie->sum('total');

        $ubicaciones = DB::table('animales')->where('usuario_id',$uid)
            ->whereNotNull('ubicacion')->distinct()->pluck('ubicacion');

        $atencion = DB::table('animales')->where('usuario_id',$uid)
            ->where('atencion_especial',1)->where('estado','activo')->count();

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

    /**
     * Muestra el detalle de un animal con timeline unificado.
     */
    public function show($id)
    {
        $uid    = session('usuario_id');
        $animal = DB::table('animales')->where('id',$id)->where('usuario_id',$uid)->first();
        if (!$animal) abort(404);

        $gastos    = DB::table('gastos')->where('animal_id',$id)->where('usuario_id',$uid)->orderBy('fecha','desc')->get();
        $ingresos  = DB::table('ingresos')->where('animal_id',$id)->where('usuario_id',$uid)->orderBy('fecha','desc')->get();
        $totalGastos   = $gastos->sum('valor');
        $totalIngresos = $ingresos->sum('valor_total');

        try { $fotos       = DB::table('animal_fotos')->where('animal_id',$id)->where('usuario_id',$uid)->orderBy('creado_en','desc')->get(); } catch (\Exception $e) { $fotos = collect(); }
        try { $pesos       = DB::table('animal_pesos')->where('animal_id',$id)->where('usuario_id',$uid)->orderBy('fecha','desc')->get(); } catch (\Exception $e) { $pesos = collect(); }
        try { $eventos     = DB::table('animal_eventos')->where('animal_id',$id)->where('usuario_id',$uid)->orderBy('fecha','desc')->get(); } catch (\Exception $e) { $eventos = collect(); }
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

    /**
     * Registra un nuevo animal o lote.
     */
    public function store(Request $request)
    {
        $request->validate(['especie'=>'required']);
        $uid  = session('usuario_id');
        $foto = null;
        if ($request->hasFile('foto')) {
            $foto = $this->guardarImagen($request->file('foto'), 'animales');
        }

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

        try {
            DB::table('animal_eventos')->insert([
                'animal_id'=>$id,'usuario_id'=>$uid,'tipo'=>'nota',
                'titulo'=>'🐾 Animal registrado',
                'descripcion'=>'Ingreso de '.$request->especie.($request->nombre_lote ? ' - '.$request->nombre_lote : ''),
                'fecha'=>$request->fecha_ingreso ?? now()->toDateString(),
                'creado_en'=>now()->toDateTimeString(),
            ]);
        } catch (\Exception $e) {}

        return redirect()->route('animales.index')->with('msg','Animal registrado.')->with('msgType','success');
    }

    /**
     * Actualiza los datos de un animal. Crea ingreso automático si pasa a "vendido".
     */
    public function update(Request $request, $id)
    {
        $request->validate(['especie'=>'required']);
        $uid    = session('usuario_id');
        $animal = DB::table('animales')->where('id',$id)->where('usuario_id',$uid)->first();
        if (!$animal) abort(404);

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
            $data['foto'] = $this->guardarImagen($request->file('foto'), 'animales');
        }

        DB::table('animales')->where('id',$id)->where('usuario_id',$uid)->update($data);

        // Crear ingreso automático cuando el animal pasa a estado "vendido"
        if ($estadoAnterior === 'activo' && $request->estado === 'vendido') {
            $valorVenta = $request->valor_venta;
            if (!$valorVenta) {
                if ($request->vende_por_kilo && $request->precio_kilo && $request->peso_promedio) {
                    $valorVenta = $request->precio_kilo * $request->peso_promedio * ($request->cantidad ?? 1);
                } elseif ($request->precio_unidad) {
                    $valorVenta = $request->precio_unidad * ($request->cantidad ?? 1);
                }
            }

            if ($valorVenta && $valorVenta > 0) {
                $yaExiste = DB::table('ingresos')
                    ->where('usuario_id', $uid)
                    ->where('animal_id', $animal->id)
                    ->where('tipo', 'animal')
                    ->exists();

                if (!$yaExiste) {
                    DB::table('ingresos')->insert([
                        'usuario_id'     => $uid,
                        'descripcion'    => 'Venta de '.$animal->especie.($animal->nombre_lote ? ' — '.$animal->nombre_lote : ''),
                        'valor_total'    => $valorVenta,
                        'categoria'      => 'Venta de animales',
                        'fecha'          => $request->fecha_venta ?? now()->toDateString(),
                        'tipo'           => 'animal',
                        'animal_id'      => $animal->id,
                        'comprador'      => $request->comprador,
                        'notas'          => $request->notas,
                        'creado_en'      => now(),
                        'actualizado_en' => now(),
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

    /**
     * Elimina un animal y su foto principal.
     */
    public function destroy($id)
    {
        $uid    = session('usuario_id');
        $animal = DB::table('animales')->where('id',$id)->where('usuario_id',$uid)->first();
        if ($animal) $this->eliminarImagen($animal->foto ?? null);
        DB::table('animales')->where('id',$id)->where('usuario_id',$uid)->delete();
        return redirect()->route('animales.index')->with('msg','Animal eliminado.')->with('msgType','warning');
    }

    /**
     * Alterna el estado de favorito de un animal.
     */
    public function toggleFavorito($id)
    {
        $uid    = session('usuario_id');
        $animal = DB::table('animales')->where('id',$id)->where('usuario_id',$uid)->first();
        if ($animal) DB::table('animales')->where('id',$id)->update(['favorito'=>$animal->favorito?0:1]);
        return back()->with('msg', $animal->favorito ? 'Quitado de favoritos.' : 'Marcado como favorito ⭐')->with('msgType','success');
    }

    /**
     * Alterna la bandera de atención especial.
     */
    public function toggleAtencion(Request $request, $id)
    {
        $uid    = session('usuario_id');
        $animal = DB::table('animales')->where('id',$id)->where('usuario_id',$uid)->first();
        if ($animal) {
            DB::table('animales')->where('id',$id)->update([
                'atencion_especial'=>$animal->atencion_especial?0:1,
                'atencion_motivo'=>$request->motivo??$animal->atencion_motivo,
            ]);
        }
        return back()->with('msg','Estado de atención actualizado.')->with('msgType','success');
    }

    /**
     * Sube una foto al log fotográfico del animal.
     */
    public function uploadFoto(Request $request, $id)
    {
        $request->validate(['foto'=>'required|image|max:5120']);
        $uid    = session('usuario_id');
        $animal = DB::table('animales')->where('id',$id)->where('usuario_id',$uid)->first();
        if (!$animal) abort(404);
        $ruta   = $this->guardarImagen($request->file('foto'), 'animales/fotos');
        DB::table('animal_fotos')->insert([
            'animal_id'=>$id,'usuario_id'=>$uid,'ruta'=>$ruta,
            'titulo'=>$request->titulo,'descripcion'=>$request->descripcion,
            'creado_en'=>now()->toDateTimeString(),
        ]);
        return redirect()->route('animales.show',$id)->with('msg','Foto agregada.')->with('msgType','success');
    }

    /**
     * Elimina una foto del log fotográfico.
     */
    public function deleteFoto($animalId, $fotoId)
    {
        $uid  = session('usuario_id');
        $foto = DB::table('animal_fotos')->where('id',$fotoId)->where('animal_id',$animalId)->where('usuario_id',$uid)->first();
        if ($foto) {
            $this->eliminarImagen($foto->ruta);
            DB::table('animal_fotos')->where('id',$fotoId)->delete();
        }
        return redirect()->route('animales.show',$animalId)->with('msg','Foto eliminada.')->with('msgType','warning');
    }

    /**
     * Registra un pesaje para el animal.
     */
    public function storePeso(Request $request, $id)
    {
        $request->validate(['peso'=>'required|numeric','fecha'=>'required|date']);
        $uid = session('usuario_id');
        DB::table('animal_pesos')->insert([
            'animal_id'=>$id,'usuario_id'=>$uid,
            'peso'=>$request->peso,'unidad'=>$request->unidad??'kg',
            'fecha'=>$request->fecha,'notas'=>$request->notas,
            'creado_en'=>now()->toDateTimeString(),
        ]);
        DB::table('animales')->where('id',$id)->where('usuario_id',$uid)->update([
            'peso_promedio'=>$request->peso,'unidad_peso'=>$request->unidad??'kg',
            'actualizado_en'=>now()->toDateTimeString(),
        ]);
        return redirect()->route('animales.show',$id)->with('msg','Peso registrado.')->with('msgType','success');
    }

    /**
     * Registra un evento en el timeline del animal (vacuna, visita vet, etc.).
     */
    public function storeEvento(Request $request, $id)
    {
        $request->validate(['tipo'=>'required','titulo'=>'required','fecha'=>'required|date']);
        $uid      = session('usuario_id');
        $fotoRuta = null;
        if ($request->hasFile('foto')) {
            $fotoRuta = $this->guardarImagen($request->file('foto'), 'animales/eventos');
        }
        DB::table('animal_eventos')->insert([
            'animal_id'=>$id,'usuario_id'=>$uid,
            'tipo'=>$request->tipo,'titulo'=>$request->titulo,'descripcion'=>$request->descripcion,
            'foto_ruta'=>$fotoRuta,'fecha'=>$request->fecha,'dosis'=>$request->dosis,
            'proxima_dosis'=>$request->proxima_dosis?:null,
            'persona_id'=>$request->persona_id ?: null,
            'creado_en'=>now()->toDateTimeString(),
        ]);
        return redirect()->route('animales.show',$id)->with('msg','Evento registrado.')->with('msgType','success');
    }

    /**
     * Elimina un evento del timeline.
     */
    public function destroyEvento($animalId, $eventoId)
    {
        $uid    = session('usuario_id');
        $evento = DB::table('animal_eventos')->where('id',$eventoId)->where('animal_id',$animalId)->where('usuario_id',$uid)->first();
        if ($evento) $this->eliminarImagen($evento->foto_ruta??null);
        DB::table('animal_eventos')->where('id',$eventoId)->where('usuario_id',$uid)->delete();
        return redirect()->route('animales.show',$animalId)->with('msg','Evento eliminado.')->with('msgType','warning');
    }

    /**
     * Registra un propietario parcial del animal.
     */
    public function storePropietario(Request $request, $id)
    {
        $request->validate(['nombre'=>'required']);
        try {
            DB::table('animal_propietarios')->insert([
                'animal_id'=>$id,'usuario_id'=>session('usuario_id'),
                'nombre'=>$request->nombre,'porcentaje'=>$request->porcentaje??100,
                'telefono'=>$request->telefono,'notas'=>$request->notas,
                'creado_en'=>now()->toDateTimeString(),
            ]);
        } catch (\Exception $e) {}
        return redirect()->route('animales.show',$id)->with('msg','Propietario agregado.')->with('msgType','success');
    }

    /**
     * Elimina un propietario del animal.
     */
    public function destroyPropietario($animalId, $propId)
    {
        DB::table('animal_propietarios')->where('id',$propId)->where('animal_id',$animalId)->where('usuario_id',session('usuario_id'))->delete();
        return redirect()->route('animales.show',$animalId)->with('msg','Propietario eliminado.')->with('msgType','warning');
    }

    /**
     * Registra la salida definitiva de un animal (venta o sacrificio).
     */
    public function registrarSalida(Request $request, $id)
    {
        $request->validate(['tipo_salida'=>'required','fecha'=>'required']);
        $uid    = session('usuario_id');
        $animal = DB::table('animales')->where('id',$id)->where('usuario_id',$uid)->first();
        if (!$animal) abort(404);

        $nuevoEstado = $request->tipo_salida === 'sacrificio' ? 'muerte' : 'vendido';
        $valorVenta  = $request->valor_venta ?: null;

        if (!$valorVenta && $request->tipo_salida === 'venta') {
            if ($animal->vende_por_kilo && $animal->precio_kilo && $animal->peso_promedio) {
                $valorVenta = $animal->precio_kilo * $animal->peso_promedio * $animal->cantidad;
            } elseif ($animal->precio_unidad) {
                $valorVenta = $animal->precio_unidad * $animal->cantidad;
            }
        }

        DB::table('animales')->where('id',$id)->where('usuario_id',$uid)->update([
            'estado'           => $nuevoEstado,
            'fecha_venta'      => $request->tipo_salida==='venta' ? $request->fecha : null,
            'fecha_sacrificio'  => $request->tipo_salida==='sacrificio' ? $request->fecha : null,
            'valor_venta'      => $valorVenta,
            'actualizado_en'   => now()->toDateTimeString(),
        ]);

        try {
            DB::table('animal_eventos')->insert([
                'animal_id'=>$id,'usuario_id'=>$uid,
                'tipo'=>$request->tipo_salida==='venta'?'venta':'sacrificio',
                'titulo'=>$request->tipo_salida==='venta'?'💰 Vendido':'🔪 Sacrificado',
                'descripcion'=>$valorVenta?'Valor: $'.number_format($valorVenta,0,',','.').' · '.($request->comprador??''):$request->notas_salida,
                'fecha'=>$request->fecha,'creado_en'=>now()->toDateTimeString(),
            ]);
        } catch (\Exception $e) {}

        if ($request->tipo_salida==='venta' && $valorVenta) {
            DB::table('ingresos')->insert([
                'usuario_id'  => $uid,
                'descripcion' => 'Venta de '.$animal->especie.($animal->nombre_lote?' - '.$animal->nombre_lote:''),
                'valor_total' => $valorVenta,'fecha'=>$request->fecha,
                'comprador'   => $request->comprador,'tipo'=>'animal','animal_id'=>$id,
                'notas'       => $request->notas_salida,'creado_en'=>now()->toDateTimeString(),
            ]);
        }

        return redirect()->route('animales.show',$id)->with('msg','Salida registrada correctamente.')->with('msgType','success');
    }
}