<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Historial de cambios de fase fenológica de un cultivo.
 * Tabla: cultivo_historial_fases
 * 
 * NOTA: Este modelo es necesario para que Cultivo::historialFases()
 * funcione correctamente como relación Eloquent HasMany.
 * (Corrección del Bug #1 de Fase 3)
 */
class CultivoHistorialFase extends Model
{
    protected $table      = 'cultivo_historial_fases';
    public    $timestamps = false;
    const CREATED_AT      = 'creado_en';

    protected $fillable = [
        'cultivo_id', 'usuario_id', 'fase_id',
        'fecha_inicio', 'fecha_fin', 'observaciones',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    public function cultivo(): BelongsTo
    {
        return $this->belongsTo(Cultivo::class);
    }

    public function fase(): BelongsTo
    {
        return $this->belongsTo(CultivoFase::class, 'fase_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    /** Duración de esta fase en días. */
    public function duracionDias(): ?int
    {
        if (!$this->fecha_inicio) return null;
        $fin = $this->fecha_fin ?? now();
        return (int) \Carbon\Carbon::parse($this->fecha_inicio)->diffInDays($fin);
    }
}