<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cultivo extends Model
{
    protected $table = 'cultivos';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'usuario_id', 'tipo', 'nombre', 'fecha_siembra',
        'area', 'unidad', 'estado', 'notas', 'imagen', 'pendiente_sync',
    ];

    protected $casts = [
        'fecha_siembra'  => 'date',
        'area'           => 'decimal:2',
        'pendiente_sync' => 'boolean',
    ];

    // ── Relaciones ────────────────────────────────────────────

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function gastos(): HasMany
    {
        return $this->hasMany(Gasto::class, 'cultivo_id');
    }

    public function ingresos(): HasMany
    {
        return $this->hasMany(Ingreso::class, 'cultivo_id');
    }

    public function tareas(): HasMany
    {
        return $this->hasMany(Tarea::class, 'cultivo_id');
    }

    public function cosechas(): HasMany
    {
        return $this->hasMany(Cosecha::class, 'cultivo_id');
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