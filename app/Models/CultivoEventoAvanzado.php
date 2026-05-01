<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CultivoEventoAvanzado extends Model
{
    protected $table = 'cultivo_eventos_avanzados';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'cultivo_id', 'usuario_id', 'fase_id', 'tipo', 'titulo', 'descripcion',
        'fecha', 'foto_ruta', 'persona_id',
        // agroquímico
        'producto_nombre', 'producto_registro_ica', 'dosis', 'dosis_unidad',
        'periodo_carencia_dias', 'fecha_minima_cosecha', 'alerta_cosecha_activa',
        // riego
        'volumen_agua_litros', 'metodo_riego', 'duracion_minutos',
        // fertilización
        'nitrogeno_n', 'fosforo_p', 'potasio_k', 'fuente_fertilizante',
        'metodo_aplicacion_fertilizante',
        // control fitosanitario
        'plaga_enfermedad', 'tipo_control', 'nivel_severidad',
        // deshierbe
        'metodo_deshierbe', 'area_deshierbada_ha',
    ];

    protected $casts = [
        'fecha'                   => 'date',
        'fecha_minima_cosecha'    => 'date',
        'alerta_cosecha_activa'   => 'boolean',
        'dosis'                   => 'decimal:4',
        'volumen_agua_litros'     => 'decimal:2',
        'nitrogeno_n'             => 'decimal:2',
        'fosforo_p'               => 'decimal:2',
        'potasio_k'               => 'decimal:2',
        'area_deshierbada_ha'     => 'decimal:4',
    ];

    // ── Relaciones ────────────────────────────────────────────

    public function cultivo(): BelongsTo
    {
        return $this->belongsTo(Cultivo::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function fase(): BelongsTo
    {
        return $this->belongsTo(CultivoFase::class, 'fase_id');
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    // ── Helpers ──────────────────────────────────────────────

    /**
     * Calcula fecha_minima_cosecha a partir de fecha + periodo_carencia_dias.
     */
    public static function calcularFechaMinimaCosecha(string $fecha, int $dias): string
    {
        return Carbon::parse($fecha)->addDays($dias)->toDateString();
    }

    /**
     * Verifica si hay una cosecha planeada antes de la fecha mínima de cosecha.
     * Retorna la cosecha conflictiva o null.
     */
    public function cosechaConflictiva()
    {
        if (!$this->fecha_minima_cosecha) return null;
        return \App\Models\Cosecha::where('cultivo_id', $this->cultivo_id)
            ->where('fecha_cosecha', '<', $this->fecha_minima_cosecha)
            ->where('fecha_cosecha', '>=', $this->fecha->toDateString())
            ->first();
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeDelUsuario($query, int $uid)
    {
        return $query->where('usuario_id', $uid);
    }

    public function scopeConAlertaCosecha($query)
    {
        return $query->where('alerta_cosecha_activa', true);
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Etiqueta legible del tipo de evento.
     */
    public function tipoLabel(): string
    {
        return match($this->tipo) {
            'aplicacion_agroquimico'  => '🧪 Agroquímico',
            'riego'                   => '💧 Riego',
            'fertilizacion'           => '🌿 Fertilización',
            'control_fitosanitario'   => '🐛 Control Fitosanitario',
            'deshierbe'               => '🪚 Deshierbe',
            default                   => '📝 Otro',
        };
    }

    /**
     * Color de badge según tipo.
     */
    public function tipoBadgeClass(): string
    {
        return match($this->tipo) {
            'aplicacion_agroquimico'  => 'badge-red',
            'riego'                   => 'badge-blue',
            'fertilizacion'           => 'badge-green',
            'control_fitosanitario'   => 'badge-orange',
            'deshierbe'               => 'badge-brown',
            default                   => 'badge-gray',
        };
    }
}
