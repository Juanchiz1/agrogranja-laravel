<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnimalLactancia extends Model
{
    protected $table = 'animal_lactancia';
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'animal_id', 'usuario_id', 'parto_id', 'numero_lactancia',
        'fecha_inicio', 'fecha_secado',
        'produccion_pico_litros', 'fecha_pico',
        'produccion_acumulada_litros', 'observaciones',
    ];

    protected $casts = [
        'fecha_inicio'               => 'date',
        'fecha_secado'               => 'date',
        'fecha_pico'                 => 'date',
        'produccion_pico_litros'     => 'decimal:2',
        'produccion_acumulada_litros'=> 'decimal:2',
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

    public function ordenos(): HasMany
    {
        return $this->hasMany(AnimalOrdeno::class, 'lactancia_id');
    }

    // ── Helpers ──────────────────────────────────────────────

    /** ¿Sigue en producción? */
    public function activa(): bool
    {
        return is_null($this->fecha_secado);
    }

    /** Días en lactancia hasta hoy (o hasta el secado). */
    public function diasEnLactancia(): int
    {
        $fin = $this->fecha_secado ?? now()->toDateString();
        return (int) \Carbon\Carbon::parse($this->fecha_inicio)->diffInDays($fin);
    }

    /** Promedio diario de litros durante esta lactancia. */
    public function promedioDiario(): float
    {
        $dias = $this->diasEnLactancia();
        return $dias > 0
            ? round($this->produccion_acumulada_litros / $dias, 2)
            : 0;
    }

    /**
     * Recalcula y guarda produccion_acumulada_litros desde animal_ordenos.
     */
    public function recalcularAcumulado(): void
    {
        $total = AnimalOrdeno::where('lactancia_id', $this->id)->sum('litros');
        $this->update(['produccion_acumulada_litros' => $total]);
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeDelUsuario($q, int $uid) { return $q->where('usuario_id', $uid); }
    public function scopeActivas($q)   { return $q->whereNull('fecha_secado'); }
    public function scopeSecadas($q)   { return $q->whereNotNull('fecha_secado'); }
}