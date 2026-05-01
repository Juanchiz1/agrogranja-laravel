<?php
// REEMPLAZA app/Models/Cultivo.php completo

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Cultivo extends Model
{
    protected $table = 'cultivos';
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'usuario_id', 'tipo', 'nombre', 'fecha_siembra',
        'area', 'unidad', 'estado', 'notas', 'imagen', 'pendiente_sync',
        'fase_actual_id', 'fecha_cambio_fase',
        'rendimiento_esperado_ha', 'rendimiento_real_ha',
    ];

    protected $casts = [
        'fecha_siembra'           => 'date',
        'fecha_cambio_fase'       => 'date',
        'area'                    => 'decimal:2',
        'rendimiento_esperado_ha' => 'decimal:2',
        'rendimiento_real_ha'     => 'decimal:2',
        'pendiente_sync'          => 'boolean',
    ];

    // ── Relaciones existentes ─────────────────────────────────

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function gastos(): HasMany
    {
        return $this->hasMany(Gasto::class, 'cultivo_id');
    }

    public function ingresos(): HasMany
    {
        return $this->hasMany(Ingreso::class, 'cultivo_id');
    }

    public function tareas(): HasMany
    {
        return $this->hasMany(Tarea::class, 'cultivo_id');
    }

    public function cosechas(): HasMany
    {
        return $this->hasMany(Cosecha::class, 'cultivo_id');
    }

    // ── Relaciones Fase 3 ─────────────────────────────────────

    public function faseActual(): BelongsTo
    {
        return $this->belongsTo(CultivoFase::class, 'fase_actual_id');
    }

    /**
     * CORRECCIÓN BUG #1: antes usaba DB::table() incorrectamente dentro de
     * hasMany(), lo que causaba "Too few arguments to Builder::__construct()".
     * Ahora usa el modelo CultivoHistorialFase con relación Eloquent real.
     */
    public function historialFases(): HasMany
    {
        return $this->hasMany(CultivoHistorialFase::class, 'cultivo_id');
    }

    public function eventosAvanzados(): HasMany
    {
        return $this->hasMany(CultivoEventoAvanzado::class, 'cultivo_id')
                    ->orderBy('fecha', 'desc');
    }

    // ── Helpers Fase 3 ────────────────────────────────────────

    public function fasesConProgreso(): \Illuminate\Support\Collection
    {
        return CultivoFase::parasCultivo($this->tipo)->map(function ($fase) {
            $fase->es_actual  = ($fase->id === $this->fase_actual_id);
            $fase->completada = false;
            if ($this->fase_actual_id) {
                $actual = CultivoFase::find($this->fase_actual_id);
                $fase->completada = $actual && ($fase->orden < $actual->orden);
            }
            return $fase;
        });
    }

    public function porcentajeFenologico(): int
    {
        $fases = CultivoFase::parasCultivo($this->tipo)->sortBy('orden');
        if ($fases->isEmpty() || !$this->fase_actual_id) return 0;
        $total  = $fases->count();
        $actual = $fases->search(fn($f) => $f->id === $this->fase_actual_id);
        if ($actual === false) return 0;
        return (int) round((($actual + 1) / $total) * 100);
    }

    /**
     * Comparativa de rendimiento vs promedio regional.
     *
     * CORRECCIÓN BUG #4: El llamador debe pasar el departamento desde la BD
     * del usuario, no desde session(). En CultivoFaseController::fenologia()
     * se corrigió para usar $user->departamento ?? 'Córdoba'.
     *
     * CORRECCIÓN BUG #5: Cuando tipo='sin_referencia', ahora se incluye
     * la clave 'unidad' para evitar el ?? 'ton/ha' como única defensa.
     */
    public function comparativaRendimiento(string $departamento = 'Córdoba'): array
    {
        if (!$this->rendimiento_real_ha) {
            return ['valor' => null, 'porcentaje' => null, 'tipo' => 'sin_datos', 'unidad' => 'ton/ha'];
        }

        $regional = DB::table('rendimiento_regional')
            ->where('tipo_cultivo', $this->tipo)
            ->where('departamento', $departamento)
            ->orderBy('anio', 'desc')
            ->first();

        if (!$regional) {
            return [
                'valor'      => $this->rendimiento_real_ha,
                'porcentaje' => null,
                'tipo'       => 'sin_referencia',
                'unidad'     => 'ton/ha',    // clave añadida para evitar Undefined index
            ];
        }

        $diff = $this->rendimiento_real_ha - $regional->rendimiento_promedio_ha;
        $pct  = round(($diff / $regional->rendimiento_promedio_ha) * 100, 1);
        $tipo = $pct >= 10 ? 'superior' : ($pct <= -10 ? 'inferior' : 'similar');

        return [
            'valor'             => $this->rendimiento_real_ha,
            'regional_promedio' => $regional->rendimiento_promedio_ha,
            'regional_min'      => $regional->rendimiento_min_ha,
            'regional_max'      => $regional->rendimiento_max_ha,
            'diferencia'        => round($diff, 2),
            'porcentaje'        => $pct,
            'tipo'              => $tipo,
            'fuente'            => $regional->fuente,
            'anio'              => $regional->anio,
            'unidad'            => $regional->unidad,
        ];
    }

    public function recalcularRendimientoReal(): void
    {
        if (!$this->area || $this->area <= 0) return;
        $totalKg = $this->cosechas()
            ->whereIn('unidad', ['kg', 'toneladas', 'ton'])
            ->get()
            ->sum(fn($c) => in_array($c->unidad, ['toneladas', 'ton']) ? $c->cantidad * 1000 : $c->cantidad);

        $areaHa = $this->unidad === 'metros2' ? $this->area / 10000 : $this->area;
        $tonHa  = $areaHa > 0 ? round(($totalKg / 1000) / $areaHa, 2) : 0;
        $this->update(['rendimiento_real_ha' => $tonHa]);
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeDelUsuario($query, int $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }
}