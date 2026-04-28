<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gasto extends Model
{
    protected $table = 'gastos';

    // Solo tiene creado_en, sin actualizado_en
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;
    public $timestamps = false;

    protected $fillable = [
        'usuario_id', 'cultivo_id', 'animal_id', 'persona_id',
        'cosecha_id', 'tarea_id',
        'categoria', 'descripcion', 'cantidad', 'unidad_cantidad',
        'valor', 'fecha', 'proveedor', 'proveedor_id', 'factura_numero',
        'notas', 'foto_factura', 'es_recurrente', 'recurrente_id', 'pendiente_sync',
    ];

    protected $casts = [
        'fecha'         => 'date',
        'valor'         => 'decimal:2',
        'cantidad'      => 'decimal:2',
        'es_recurrente' => 'boolean',
        'pendiente_sync'=> 'boolean',
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

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
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