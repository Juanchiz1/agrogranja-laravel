<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cosecha extends Model
{
    protected $table = 'cosechas';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'usuario_id', 'cultivo_id',
        'producto', 'cantidad', 'unidad',
        'precio_unitario', 'valor_estimado', 'fecha_cosecha',
        'calidad', 'destino', 'comprador', 'cliente_id',
        'ingreso_creado', 'observaciones', 'foto',
        'merma_porcentaje', 'almacen_ubicacion', 'almacen_hasta',
    ];

    protected $casts = [
        'fecha_cosecha'   => 'date',
        'almacen_hasta'   => 'date',
        'cantidad'        => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'valor_estimado'  => 'decimal:2',
        'merma_porcentaje'=> 'decimal:2',
        'ingreso_creado'  => 'boolean',
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
        return $query->whereMonth('fecha_cosecha', now()->month)
                     ->whereYear('fecha_cosecha', now()->year);
    }
}