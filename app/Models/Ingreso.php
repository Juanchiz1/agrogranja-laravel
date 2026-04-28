<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ingreso extends Model
{
    protected $table = 'ingresos';

    // Solo tiene creado_en, sin actualizado_en
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;
    public $timestamps = false;

    protected $fillable = [
        'usuario_id', 'cultivo_id', 'animal_id', 'cosecha_id',
        'tipo', 'descripcion', 'cantidad', 'unidad',
        'precio_unitario', 'valor_total', 'fecha',
        'referencia_id', 'referencia_tipo',
        'comprador', 'cliente_id', 'persona_id',
        'notas', 'foto_soporte',
    ];

    protected $casts = [
        'fecha'          => 'date',
        'valor_total'    => 'decimal:2',
        'precio_unitario'=> 'decimal:2',
        'cantidad'       => 'decimal:2',
    ];

    // ── Relaciones ────────────────────────────────────────────

    public function cultivo(): BelongsTo
    {
        return $this->belongsTo(Cultivo::class, 'cultivo_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeDelUsuario($query, int $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopeDelMes($query)
    {
        return $query->whereMonth('fecha', now()->month)
                     ->whereYear('fecha', now()->year);
    }

    public function scopeDelAnio($query, int $anio = null)
    {
        return $query->whereYear('fecha', $anio ?? now()->year);
    }
}