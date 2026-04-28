<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Persona extends Model
{
    protected $table = 'personas';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'usuario_id', 'tipo', 'nombre', 'telefono', 'email',
        'documento', 'direccion', 'foto', 'cargo',
        'tipo_contrato', 'valor_jornal', 'valor_mensual',
        'fecha_ingreso', 'labores', 'activo', 'notas', 'favorito',
    ];

    protected $casts = [
        'activo'        => 'boolean',
        'favorito'      => 'boolean',
        'fecha_ingreso' => 'date',
        'valor_jornal'  => 'decimal:2',
        'valor_mensual' => 'decimal:2',
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
        return $query->where('activo', true);
    }

    public function scopeTrabajadores($query)
    {
        return $query->where('tipo', 'trabajador');
    }

    public function scopeProveedores($query)
    {
        return $query->where('tipo', 'proveedor');
    }

    public function scopeCompradores($query)
    {
        return $query->where('tipo', 'comprador');
    }
}