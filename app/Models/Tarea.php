<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tarea extends Model
{
    protected $table = 'tareas';

    // Solo tiene creado_en, sin actualizado_en
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;
    public $timestamps = false;

    protected $fillable = [
        'usuario_id', 'cultivo_id', 'animal_id',
        'titulo', 'tipo', 'fecha', 'hora',
        'completada', 'fecha_completada',
        'notas', 'responsable', 'notas_completada',
        'prioridad', 'pendiente_sync', 'persona_completada_id',
    ];

    protected $casts = [
        'fecha'           => 'date',
        'completada'      => 'boolean',
        'fecha_completada'=> 'datetime',
        'pendiente_sync'  => 'boolean',
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

    public function scopePendientes($query)
    {
        return $query->where('completada', false);
    }

    public function scopeDeHoy($query)
    {
        return $query->whereDate('fecha', now()->toDateString());
    }
}