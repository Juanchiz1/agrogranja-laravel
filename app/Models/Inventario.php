<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventario extends Model
{
    protected $table = 'inventario';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'usuario_id', 'nombre', 'categoria',
        'cantidad_actual', 'stock_minimo', 'unidad',
        'precio_unitario', 'proveedor', 'fecha_vencimiento',
        'notas', 'foto', 'ubicacion', 'uso_principal',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'cantidad_actual'   => 'decimal:2',
        'stock_minimo'      => 'decimal:2',
        'precio_unitario'   => 'decimal:2',
    ];

    // ── Relaciones ────────────────────────────────────────────

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(InventarioMovimiento::class, 'inventario_id');
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeDelUsuario($query, int $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopeConStockBajo($query)
    {
        return $query->whereRaw('cantidad_actual <= stock_minimo');
    }

    public function scopePorVencer($query, int $dias = 30)
    {
        return $query->whereNotNull('fecha_vencimiento')
                     ->whereDate('fecha_vencimiento', '>=', now()->toDateString())
                     ->whereDate('fecha_vencimiento', '<=', now()->addDays($dias)->toDateString());
    }
}