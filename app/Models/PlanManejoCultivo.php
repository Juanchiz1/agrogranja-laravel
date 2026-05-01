<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanManejoCultivo extends Model
{
    protected $table = 'plan_manejo_cultivo';
    public    $timestamps = false;

    protected $fillable = [
        'cultivo_fase_id', 'actividad', 'tipo_actividad',
        'descripcion', 'producto_sugerido', 'dosis_sugerida', 'obligatoria',
    ];

    protected $casts = [
        'obligatoria' => 'boolean',
    ];

    public function fase(): BelongsTo
    {
        return $this->belongsTo(CultivoFase::class, 'cultivo_fase_id');
    }
}
