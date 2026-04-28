<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Animal extends Model
{
    protected $table = 'animales';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'usuario_id', 'especie', 'nombre_lote', 'cantidad',
        'fecha_ingreso', 'estado', 'peso_promedio', 'unidad_peso',
        'notas', 'foto', 'ubicacion',
        'fecha_nacimiento', 'fecha_sacrificio', 'fecha_venta', 'valor_venta',
        'precio_kilo', 'precio_unidad', 'vende_por_kilo',
        'propietario', 'favorito', 'atencion_especial', 'atencion_motivo',
        'etapa_vida', 'produccion',
    ];

    protected $casts = [
        'fecha_ingreso'     => 'date',
        'fecha_nacimiento'  => 'date',
        'fecha_sacrificio'  => 'date',
        'fecha_venta'       => 'date',
        'peso_promedio'     => 'decimal:2',
        'valor_venta'       => 'decimal:2',
        'precio_kilo'       => 'decimal:2',
        'precio_unidad'     => 'decimal:2',
        'vende_por_kilo'    => 'boolean',
        'favorito'          => 'boolean',
        'atencion_especial' => 'boolean',
    ];

    // ── Relaciones ────────────────────────────────────────────

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
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