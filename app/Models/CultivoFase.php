<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CultivoFase extends Model
{
    protected $table = 'cultivo_fases';
    public    $timestamps = false;

    protected $fillable = [
        'tipo_cultivo', 'nombre', 'orden',
        'duracion_dias_min', 'duracion_dias_max',
        'color_hex', 'icono', 'descripcion',
    ];

    // ── Relaciones ────────────────────────────────────────────

    public function planManejo(): HasMany
    {
        return $this->hasMany(PlanManejoCultivo::class, 'cultivo_fase_id');
    }

    public function cultivos(): HasMany
    {
        return $this->hasMany(Cultivo::class, 'fase_actual_id');
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_cultivo', $tipo)->orWhere('tipo_cultivo', 'General');
    }

    public function scopeOrdenado($query)
    {
        return $query->orderBy('orden');
    }

    // ── Helpers ──────────────────────────────────────────────

    /**
     * Devuelve las fases para un tipo de cultivo dado.
     * Si no hay fases específicas, retorna las generales.
     */
    public static function parasCultivo(string $tipoCultivo): \Illuminate\Support\Collection
    {
        $especificas = self::where('tipo_cultivo', $tipoCultivo)->orderBy('orden')->get();
        if ($especificas->isNotEmpty()) {
            return $especificas;
        }
        return self::where('tipo_cultivo', 'General')->orderBy('orden')->get();
    }
}
