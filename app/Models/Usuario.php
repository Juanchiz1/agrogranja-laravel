<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usuario extends Model
{
    protected $table = 'usuarios';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre', 'email', 'password',
        'nombre_finca', 'departamento', 'municipio', 'telefono',
        'foto_perfil', 'foto_finca', 'hectareas_total',
        'tipo_produccion', 'descripcion_finca',
        'latitud', 'longitud', 'rut',
        'entidad_bancaria', 'num_cuenta', 'tipo_cuenta',
        'notif_tareas', 'notif_stock', 'moneda', 'tema',
        'onboarding_completado', 'activo',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'onboarding_completado' => 'boolean',
        'activo'                => 'boolean',
        'notif_tareas'          => 'boolean',
        'notif_stock'           => 'boolean',
        'latitud'               => 'decimal:7',
        'longitud'              => 'decimal:7',
        'hectareas_total'       => 'decimal:2',
    ];

    public function cultivos(): HasMany
    {
        return $this->hasMany(Cultivo::class, 'usuario_id');
    }

    public function gastos(): HasMany
    {
        return $this->hasMany(Gasto::class, 'usuario_id');
    }

    public function ingresos(): HasMany
    {
        return $this->hasMany(Ingreso::class, 'usuario_id');
    }

    public function animales(): HasMany
    {
        return $this->hasMany(Animal::class, 'usuario_id');
    }

    public function tareas(): HasMany
    {
        return $this->hasMany(Tarea::class, 'usuario_id');
    }

    public function cosechas(): HasMany
    {
        return $this->hasMany(Cosecha::class, 'usuario_id');
    }

    public function personas(): HasMany
    {
        return $this->hasMany(Persona::class, 'usuario_id');
    }

    public function inventario(): HasMany
    {
        return $this->hasMany(Inventario::class, 'usuario_id');
    }
}