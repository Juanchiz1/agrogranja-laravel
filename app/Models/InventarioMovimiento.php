<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioMovimiento extends Model
{
    protected $table = 'inventario_movimientos';

    // Solo tiene creado_en
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;
    public $timestamps = false;

    protected $fillable = [
        'inventario_id', 'usuario_id', 'cultivo_id', 'animal_id', 'persona_id',
        'tipo', 'cantidad', 'precio_unitario',
        'motivo', 'persona', 'foto_soporte', 'fecha',
    ];

    protected $casts = [
        'fecha'          => 'date',
        'cantidad'       => 'decimal:2',
        'precio_unitario'=> 'decimal:2',
    ];

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Inventario::class, 'inventario_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}