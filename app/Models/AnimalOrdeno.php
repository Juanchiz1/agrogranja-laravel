<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimalOrdeno extends Model
{
    protected $table    = 'animal_ordenos';
    public $timestamps  = false;
    const CREATED_AT    = 'creado_en';

    protected $fillable = [
        'animal_id', 'usuario_id', 'lactancia_id',
        'fecha', 'sesion', 'litros',
        'temperatura_leche', 'observaciones',
    ];

    protected $casts = [
        'fecha'             => 'date',
        'litros'            => 'decimal:2',
        'temperatura_leche' => 'decimal:2',
    ];

    // ── Relaciones ────────────────────────────────────────────

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function lactancia(): BelongsTo
    {
        return $this->belongsTo(AnimalLactancia::class, 'lactancia_id');
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeDelUsuario($q, int $uid) { return $q->where('usuario_id', $uid); }
    public function scopeDeHoy($q)      { return $q->whereDate('fecha', now()); }
    public function scopeSesionAm($q)   { return $q->where('sesion', 'am'); }
    public function scopeSesionPm($q)   { return $q->where('sesion', 'pm'); }
    public function scopeDeAnimal($q, int $animalId) { return $q->where('animal_id', $animalId); }
}