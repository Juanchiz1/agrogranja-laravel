<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AnimalReproduccion extends Model
{
    protected $table    = 'animal_reproduccion';
    const CREATED_AT    = 'creado_en';
    const UPDATED_AT    = 'actualizado_en';

    protected $fillable = [
        'animal_id', 'usuario_id', 'tipo_servicio', 'fecha_servicio',
        'macho_descripcion', 'fecha_diagnostico_prenez', 'resultado_diagnostico',
        'fecha_probable_parto', 'fecha_parto_real',
        'num_crias_nacidas', 'num_crias_vivas', 'sexo_cria',
        'peso_cria_kg', 'observaciones',
    ];

    protected $casts = [
        'fecha_servicio'           => 'date',
        'fecha_diagnostico_prenez' => 'date',
        'fecha_probable_parto'     => 'date',
        'fecha_parto_real'         => 'date',
        'peso_cria_kg'             => 'decimal:2',
    ];

    // ── Relaciones ────────────────────────────────────────────

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    // ── Helpers ──────────────────────────────────────────────

    /**
     * Calcula la fecha probable de parto (gestación bovina ≈ 283 días).
     */
    public static function calcularFechaParto(string $fechaServicio): string
    {
        return Carbon::parse($fechaServicio)->addDays(283)->toDateString();
    }

    /**
     * Días que faltan para el parto (negativo = ya ocurrió).
     */
    public function diasParaParto(): ?int
    {
        if (!$this->fecha_probable_parto || $this->fecha_parto_real) return null;
        return now()->diffInDays($this->fecha_probable_parto, false);
    }

    /**
     * Etiqueta del estado reproductivo.
     */
    public function etiquetaEstado(): string
    {
        if ($this->fecha_parto_real) return 'Parida';
        return match($this->resultado_diagnostico) {
            'positivo' => 'Preñada',
            'negativo' => 'Vacía',
            default    => 'Servida',
        };
    }

    public function colorEstado(): string
    {
        if ($this->fecha_parto_real) return '#3b82f6';
        return match($this->resultado_diagnostico) {
            'positivo' => '#22c55e',
            'negativo' => '#ef4444',
            default    => '#f59e0b',
        };
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeDelUsuario($q, int $uid) { return $q->where('usuario_id', $uid); }
    public function scopePreñadas($q)  { return $q->where('resultado_diagnostico','positivo')->whereNull('fecha_parto_real'); }
    public function scopeProximoParto($q, int $dias = 15) {
        return $q->where('resultado_diagnostico','positivo')
                 ->whereNull('fecha_parto_real')
                 ->where('fecha_probable_parto','<=', now()->addDays($dias)->toDateString());
    }
}