<?php

namespace App\Http\Controllers;

use App\Traits\ManejadorImagenes;
use App\Http\Requests\IngresoRequest;
use App\Models\Ingreso;
use App\Models\Cultivo;
use App\Models\Animal;
use App\Models\Cosecha;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IngresoController extends Controller
{
    use ManejadorImagenes;

    private function tipos(): array
    {
        return [
            'venta'          => ['label' => 'Venta',                  'emoji' => '💰'],
            'cosecha_propia' => ['label' => 'Cosecha / Producción',   'emoji' => '🌾'],
            'animal'         => ['label' => 'Venta de animal',        'emoji' => '🐄'],
            'subproducto'    => ['label' => 'Subproducto',            'emoji' => '🥚'],
            'subsidio'       => ['label' => 'Subsidio / Ayuda',       'emoji' => '🏛️'],
            'servicio'       => ['label' => 'Servicio prestado',      'emoji' => '🔧'],
            'alquiler'       => ['label' => 'Alquiler de tierra/equipo', 'emoji' => '🏡'],
            'otro'           => ['label' => 'Otro',                   'emoji' => '📦'],
        ];
    }

    /**
     * Muestra el listado de ingresos con estadísticas del período.
     */
    public function index(Request $request)
    {
        $uid = session('usuario_id');

        // Mantiene JOINs en DB::table por la necesidad de columnas de múltiples tablas
        $query = DB::table('ingresos as i')
            ->leftJoin('cultivos as c',  'c.id',  '=', 'i.cultivo_id')
            ->leftJoin('animales as a',  'a.id',  '=', 'i.animal_id')
            ->leftJoin('cosechas as co', 'co.id', '=', 'i.cosecha_id')
            ->where('i.usuario_id', $uid)
            ->select(
                'i.*',
                'c.nombre as cultivo_nombre',
                'a.nombre_lote as animal_nombre', 'a.especie as animal_especie',
                'co.producto as cosecha_nombre'
            );

        if ($request->q) {
            $q = $request->q;
            $query->where(fn($w) => $w
                ->where('i.descripcion', 'like', "%$q%")
                ->orWhere('i.comprador',  'like', "%$q%"));
        }
        if ($request->mes)  $query->whereRaw("DATE_FORMAT(i.fecha,'%Y-%m') = ?", [$request->mes]);
        if ($request->tipo) $query->where('i.tipo', $request->tipo);
        if ($request->asociado) {
            match($request->asociado) {
                'cultivo' => $query->whereNotNull('i.cultivo_id'),
                'animal'  => $query->whereNotNull('i.animal_id'),
                'cosecha' => $query->whereNotNull('i.cosecha_id'),
                default   => null,
            };
        }

        $ingresos       = $query->orderBy('i.fecha', 'desc')->get();
        $totalMes       = Ingreso::delUsuario($uid)->delMes()->sum('valor_total');
        $totalAnio      = Ingreso::delUsuario($uid)->delAnio()->sum('valor_total');

        $statsTipo = Ingreso::delUsuario($uid)->delMes()
            ->selectRaw('tipo, SUM(valor_total) as total, COUNT(*) as cnt')
            ->groupBy('tipo')->orderByDesc('total')->get();

        $topCompradores = Ingreso::delUsuario($uid)->delAnio()
            ->whereNotNull('comprador')
            ->selectRaw('comprador, SUM(valor_total) as total, COUNT(*) as cnt')
            ->groupBy('comprador')->orderByDesc('total')->limit(5)->get();

        $cultivos = Cultivo::delUsuario($uid)->orderBy('nombre')->get();
        $animales = Animal::delUsuario($uid)->activos()->orderBy('nombre_lote')->get();
        $cosechas = Cosecha::delUsuario($uid)->orderBy('fecha_cosecha', 'desc')->limit(30)->get();
        $tipos    = $this->tipos();
        $clientes = Persona::delUsuario($uid)->activos()->compradores()->orderBy('nombre')->get();

        return view('pages.ingresos', compact(
            'ingresos','totalMes','totalAnio','statsTipo','topCompradores',
            'cultivos','animales','cosechas','tipos','clientes'
        ));
    }

    /**
     * Registra un nuevo ingreso.
     */
    public function store(IngresoRequest $request)
    {
        $uid   = session('usuario_id');
        $cant  = $request->cantidad;
        $pu    = $request->precio_unitario;
        $total = ($cant && $pu) ? $cant * $pu : $request->valor_total;

        $foto = null;
        if ($request->hasFile('foto_soporte')) {
            $foto = $this->guardarImagen($request->file('foto_soporte'), 'ingresos/soportes');
        }

        $comprador = $request->comprador;
        if ($request->persona_id) {
            $cli = Persona::find($request->persona_id);
            if ($cli) $comprador = $cli->nombre;
        }

        Ingreso::create([
            'usuario_id'     => $uid,
            'descripcion'    => $request->descripcion,
            'cantidad'       => $cant ?: null,
            'unidad'         => $request->unidad,
            'precio_unitario'=> $pu ?: null,
            'valor_total'    => $total,
            'fecha'          => $request->fecha ?? now()->toDateString(),
            'comprador'      => $comprador,
            'persona_id'     => $request->persona_id ?: null,
            'tipo'           => $request->tipo ?? 'venta',
            'cultivo_id'     => $request->cultivo_id ?: null,
            'animal_id'      => $request->animal_id ?: null,
            'cosecha_id'     => $request->cosecha_id ?: null,
            'notas'          => $request->notas,
            'foto_soporte'   => $foto,
        ]);

        return redirect()->route('ingresos.index')->with('msg','Ingreso registrado.')->with('msgType','success');
    }

    /**
     * Actualiza un ingreso existente.
     */
    public function update(IngresoRequest $request, $id)
    {
        $uid     = session('usuario_id');
        $ingreso = Ingreso::where('id',$id)->where('usuario_id',$uid)->firstOrFail();

        $data = [
            'descripcion'    => $request->descripcion,
            'cantidad'       => $request->cantidad ?: null,
            'unidad'         => $request->unidad,
            'precio_unitario'=> $request->precio_unitario ?: null,
            'valor_total'    => $request->valor_total,
            'fecha'          => $request->fecha,
            'comprador'      => $request->comprador,
            'persona_id'     => $request->persona_id ?: null,
            'tipo'           => $request->tipo ?? 'venta',
            'cultivo_id'     => $request->cultivo_id ?: null,
            'animal_id'      => $request->animal_id ?: null,
            'cosecha_id'     => $request->cosecha_id ?: null,
            'notas'          => $request->notas,
        ];

        if ($request->hasFile('foto_soporte')) {
            $this->eliminarImagen($ingreso->foto_soporte);
            $data['foto_soporte'] = $this->guardarImagen($request->file('foto_soporte'), 'ingresos/soportes');
        }

        $ingreso->update($data);

        return redirect()->route('ingresos.index')->with('msg','Ingreso actualizado.')->with('msgType','success');
    }

    /**
     * Elimina un ingreso y su foto de soporte si existe.
     */
    public function destroy($id)
    {
        $uid     = session('usuario_id');
        $ingreso = Ingreso::where('id',$id)->where('usuario_id',$uid)->firstOrFail();
        $this->eliminarImagen($ingreso->foto_soporte);
        $ingreso->delete();

        return redirect()->route('ingresos.index')->with('msg','Ingreso eliminado.')->with('msgType','warning');
    }

    /**
     * Guarda un nuevo cliente en la tabla personas.
     */
    public function storeCliente(Request $request)
    {
        $request->validate(['nombre'=>'required|string|max:150']);
        try {
            DB::table('personas')->insert([
                'usuario_id' => session('usuario_id'),
                'tipo'       => 'comprador',
                'nombre'     => $request->nombre,
                'telefono'   => $request->telefono,
                'email'      => $request->email,
                'direccion'  => $request->direccion,
                'notas'      => $request->notas,
                'activo'     => 1,
                'creado_en'  => now()->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            return redirect()->route('ingresos.index')
                ->with('msg','⚠️ Error al guardar el cliente.')
                ->with('msgType','error');
        }
        return redirect()->route('ingresos.index')->with('msg','Cliente guardado.')->with('msgType','success');
    }

    /**
     * Desactiva un cliente (soft delete).
     */
    public function destroyCliente($id)
    {
        Persona::where('id',$id)->where('tipo','comprador')->where('usuario_id',session('usuario_id'))->update(['activo'=>0]);
        return redirect()->route('ingresos.index')->with('msg','Cliente eliminado.')->with('msgType','warning');
    }
}