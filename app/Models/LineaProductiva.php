<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Catálogo de líneas productivas (cultivos, bovino, avícola, etc.).
 *
 * Expone un helper estático `LineaProductiva::activa($codigo)` para que
 * vistas y controladores consulten rápidamente si el usuario actual
 * tiene esa línea activada. Cachea el resultado en memoria por request
 * para no consultar la BD múltiples veces.
 *
 * FASE 2: añade el mapa línea → especies y el helper `tieneAnimales()`,
 * usados por el layout, el dashboard y AnimalController.
 */
class LineaProductiva extends Model
{
    protected $table      = 'lineas_productivas';
    protected $primaryKey = 'codigo';
    public    $incrementing = false;
    protected $keyType    = 'string';
    public    $timestamps = false;

    const CREATED_AT = 'creado_en';

    protected $fillable = [
        'codigo', 'nombre', 'emoji', 'descripcion', 'orden', 'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden'  => 'integer',
    ];

    /** Cache en memoria por usuario_id durante el request. */
    private static array $cacheLineas = [];

    /**
     * Mapa de qué especies de la tabla `animales` corresponden a cada
     * línea productiva. Si una línea no aparece aquí (cultivos), no
     * tiene animales asociados.
     *
     * IMPORTANTE: las claves aquí deben coincidir EXACTAMENTE con los
     * valores del campo `especie` que usa AnimalController.
     */
    public const ESPECIES_POR_LINEA = [
        'bovino'        => ['Ganado bovino', 'Terneros'],
        'porcino'       => ['Cerdos', 'Cerdas de cría'],
        'avicola'       => ['Gallinas', 'Patos', 'Pavos'],
        'piscicola'     => ['Peces'],
        'caprino_ovino' => ['Cabras', 'Ovejas'],
        'apicola'       => [], // colmenas usan tabla aparte (Fase futura)
        'equino'        => ['Caballos'],
        'cunicola'      => ['Conejos'],
    ];

    /** Líneas que SÍ implican manejo de animales. */
    public const LINEAS_ANIMALES = [
        'bovino', 'porcino', 'avicola', 'piscicola',
        'caprino_ovino', 'apicola', 'equino', 'cunicola',
    ];

    // ── Relaciones ────────────────────────────────────────────

    public function usuarios(): HasMany
    {
        return $this->hasMany(UsuarioLinea::class, 'linea_codigo', 'codigo');
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeActivas($query)
    {
        return $query->where('activo', 1)->orderBy('orden');
    }

    // ── Helpers estáticos ────────────────────────────────────

    /**
     * Devuelve true si el usuario actual tiene activada la línea $codigo.
     * Usar en vistas: @if(LineaProductiva::activa('bovino')) ... @endif
     */
    public static function activa(string $codigo, ?int $usuarioId = null): bool
    {
        $uid = $usuarioId ?? session('usuario_id');
        if (!$uid) return false;

        return in_array($codigo, self::activasDelUsuario($uid), true);
    }

    /**
     * ¿El usuario tiene al menos una línea activa que implique animales?
     * Útil para mostrar/ocultar el módulo "Animales" entero.
     */
    public static function tieneAnimales(?int $usuarioId = null): bool
    {
        $uid = $usuarioId ?? session('usuario_id');
        if (!$uid) return false;

        $activas = self::activasDelUsuario($uid);
        return count(array_intersect($activas, self::LINEAS_ANIMALES)) > 0;
    }

    /**
     * ¿El usuario tiene cultivos activos?
     */
    public static function tieneCultivos(?int $usuarioId = null): bool
    {
        return self::activa('cultivos', $usuarioId);
    }

    /**
     * Devuelve las especies sugeridas (en formato array de strings) para
     * el usuario, basado en sus líneas productivas activas. Siempre
     * incluye 'Otro' al final como fallback.
     *
     * Si no tiene ninguna línea animal activa, devuelve la lista
     * completa (compatibilidad: no rompemos formularios existentes).
     */
    public static function especiesSugeridas(?int $usuarioId = null): array
    {
        $uid = $usuarioId ?? session('usuario_id');
        if (!$uid) return self::todasLasEspecies();

        $activas = self::activasDelUsuario($uid);
        $sugeridas = [];

        foreach ($activas as $codigo) {
            if (isset(self::ESPECIES_POR_LINEA[$codigo])) {
                $sugeridas = array_merge($sugeridas, self::ESPECIES_POR_LINEA[$codigo]);
            }
        }

        $sugeridas = array_values(array_unique($sugeridas));

        // Fallback: si no hay ninguna línea animal activa, devolver todas
        // (puede que el usuario aún no haya configurado o esté usando una
        // línea de cultivos pero quiere registrar un animal puntual).
        if (empty($sugeridas)) return self::todasLasEspecies();

        // Siempre permitir "Otro" como fallback.
        $sugeridas[] = 'Otro';
        return $sugeridas;
    }

    /**
     * Devuelve TODAS las especies (catálogo completo). Lo usa el filtro
     * cuando el usuario aún no ha configurado líneas.
     */
    public static function todasLasEspecies(): array
    {
        return [
            'Ganado bovino', 'Terneros', 'Cerdos', 'Cerdas de cría', 'Gallinas',
            'Patos', 'Pavos', 'Conejos', 'Cabras', 'Ovejas', 'Caballos', 'Peces', 'Otro',
        ];
    }

    /**
     * Devuelve un array con los códigos de líneas activas del usuario.
     * Cachea por usuario durante el request.
     */
    public static function activasDelUsuario(int $usuarioId): array
    {
        if (!isset(self::$cacheLineas[$usuarioId])) {
            try {
                self::$cacheLineas[$usuarioId] = DB::table('usuario_lineas')
                    ->where('usuario_id', $usuarioId)
                    ->where('activa', 1)
                    ->pluck('linea_codigo')
                    ->toArray();
            } catch (\Exception $e) {
                self::$cacheLineas[$usuarioId] = [];
            }
        }
        return self::$cacheLineas[$usuarioId];
    }

    /**
     * Limpia la caché (útil después de actualizar líneas).
     */
    public static function limpiarCache(?int $usuarioId = null): void
    {
        if ($usuarioId === null) {
            self::$cacheLineas = [];
        } else {
            unset(self::$cacheLineas[$usuarioId]);
        }
    }

    /**
     * Devuelve el catálogo completo (solo activos), ordenado.
     * Lo usa la vista de onboarding y la de perfil.
     */
    public static function catalogo()
    {
        return self::activas()->get();
    }

    /**
     * Devuelve un resumen tipo "Mixta: Bovino, Cultivos" para mostrar
     * en el chip del perfil. Útil para el campo legado `tipo_produccion`.
     */
    public static function resumenTexto(int $usuarioId): string
    {
        $codigos = self::activasDelUsuario($usuarioId);
        if (empty($codigos)) return '';

        $nombres = self::whereIn('codigo', $codigos)
            ->orderBy('orden')->pluck('nombre')->toArray();

        if (count($nombres) === 1)  return $nombres[0];
        if (count($nombres) <= 3)   return 'Mixta: ' . implode(', ', $nombres);
        return 'Mixta (' . count($nombres) . ' líneas)';
    }
}