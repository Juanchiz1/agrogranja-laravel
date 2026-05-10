<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrogranja — Migración completa
 * Versión 2.0 · Mayo 2026
 * Cubre las 51 tablas del sistema actual.
 *
 * Grupos:
 *  1. Core / Autenticación       → usuarios, sesiones
 *  2. Configuración de finca     → lineas_productivas, usuario_lineas
 *  3. Cultivos                   → 8 tablas
 *  4. Animales (general)         → 10 tablas
 *  5. Módulo Avícola             → 6 tablas
 *  6. Módulo Porcícola           → 6 tablas
 *  7. Módulo Piscícola           → 7 tablas
 *  8. Finanzas                   → cosechas, gastos, gastos_recurrentes,
 *                                   ingresos, produccion_costos_periodo
 *  9. Inventario                 → inventario, inventario_movimientos
 * 10. Personas / Nómina          → personas, persona_labores, persona_pagos
 * 11. Agenda / Alertas           → tareas, dashboard_alertas_log
 */
return new class extends Migration
{
    // ─────────────────────────────────────────────────────────────────
    // UP
    // ─────────────────────────────────────────────────────────────────
    public function up(): void
    {
        // ══════════════════════════════════════════════════════════════
        // 1. CORE / AUTENTICACIÓN
        // ══════════════════════════════════════════════════════════════

        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            // Datos personales
            $table->string('nombre', 100);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->string('telefono', 20)->nullable();
            $table->string('rut', 30)->nullable()->comment('NIT o cédula del productor');
            $table->string('foto_perfil')->nullable();
            // Datos de la finca
            $table->string('nombre_finca', 150)->nullable();
            $table->string('departamento', 100)->nullable();
            $table->string('municipio', 100)->nullable();
            $table->string('foto_finca')->nullable();
            $table->decimal('hectareas_total', 10, 2)->nullable();
            $table->string('tipo_produccion', 255)->nullable()->comment('Ej: Agrícola, Ganadera, Mixta');
            $table->text('descripcion_finca')->nullable();
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            // Datos financieros / bancarios
            $table->string('entidad_bancaria', 100)->nullable();
            $table->string('num_cuenta', 50)->nullable();
            $table->string('tipo_cuenta', 30)->nullable();
            // Preferencias
            $table->boolean('notif_tareas')->default(true);
            $table->boolean('notif_stock')->default(true);
            $table->string('moneda', 10)->default('COP');
            $table->string('tema', 20)->default('auto');
            // Control de flujo
            $table->boolean('onboarding_completado')->default(false);
            $table->boolean('diagnostico_completado')->default(false)
                  ->comment('Diagnóstico inicial del estudio de impacto');
            $table->string('ultima_metrica_enviada', 7)->nullable()
                  ->comment('Último período enviado a Supabase. Ej: 2026-04');
            $table->boolean('activo')->default(true);
            $table->datetime('creado_en')->useCurrent();
            $table->datetime('actualizado_en')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('sesiones', function (Blueprint $table) {
            $table->string('id', 128)->primary();
            $table->unsignedBigInteger('usuario_id');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->datetime('ultimo_acceso')->useCurrent()->useCurrentOnUpdate();
            $table->datetime('creado_en')->useCurrent();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        // ══════════════════════════════════════════════════════════════
        // 2. CONFIGURACIÓN DE LÍNEAS PRODUCTIVAS
        // ══════════════════════════════════════════════════════════════

        Schema::create('lineas_productivas', function (Blueprint $table) {
            $table->string('codigo', 30)->primary();
            $table->string('nombre', 100);
            $table->string('emoji', 10)->nullable();
            $table->string('descripcion', 255)->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->datetime('creado_en')->useCurrent();
        });

        Schema::create('usuario_lineas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->string('linea_codigo', 30);
            $table->integer('cantidad_aprox')->nullable()
                  ->comment('Vacas, gallinas, hectáreas, estanques...');
            $table->enum('escala', ['pequena', 'mediana', 'grande'])->default('pequena');
            $table->text('metadata')->nullable()
                  ->comment('JSON con configuración por línea');
            $table->string('notas', 255)->nullable();
            $table->boolean('activa')->default(true);
            $table->datetime('creado_en')->useCurrent();
            $table->datetime('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('linea_codigo')->references('codigo')->on('lineas_productivas')->onDelete('cascade');
        });

        // ══════════════════════════════════════════════════════════════
        // 3. MÓDULO CULTIVOS
        // ══════════════════════════════════════════════════════════════

        Schema::create('cultivo_fases', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_cultivo', 100)->comment('Maíz, Yuca, Plátano, Hortalizas...');
            $table->string('nombre', 100);
            $table->integer('orden')->comment('Secuencia 1-N');
            $table->integer('duracion_dias_min')->nullable();
            $table->integer('duracion_dias_max')->nullable();
            $table->string('color_hex', 7)->default('#4CAF50');
            $table->string('icono', 10)->default('🌱');
            $table->text('descripcion')->nullable();
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::create('cultivos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->string('tipo', 100);
            $table->string('nombre', 150);
            $table->date('fecha_siembra');
            $table->decimal('area', 10, 2)->nullable();
            $table->enum('unidad', ['hectareas', 'metros2', 'fanegadas', 'lotes'])->default('hectareas');
            $table->enum('estado', ['activo', 'cosechado', 'vendido'])->default('activo');
            $table->unsignedBigInteger('fase_actual_id')->nullable();
            $table->date('fecha_cambio_fase')->nullable();
            $table->decimal('rendimiento_esperado_ha', 10, 2)->nullable();
            $table->decimal('rendimiento_real_ha', 10, 2)->nullable();
            $table->text('notas')->nullable();
            $table->string('imagen')->nullable();
            $table->boolean('pendiente_sync')->default(false);
            $table->datetime('creado_en')->useCurrent();
            $table->datetime('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('fase_actual_id')->references('id')->on('cultivo_fases')->onDelete('set null');
        });

        Schema::create('cultivo_historial_fases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cultivo_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('fase_id');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('cultivo_id')->references('id')->on('cultivos')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('fase_id')->references('id')->on('cultivo_fases')->onDelete('cascade');
        });

        Schema::create('cultivo_eventos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cultivo_id');
            $table->unsignedBigInteger('usuario_id');
            $table->enum('tipo', [
                'nota', 'aplicacion', 'riego', 'poda', 'cambio_estado',
                'foto', 'gasto', 'cosecha', 'tarea_completada', 'otro',
            ])->default('nota');
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->string('foto_ruta')->nullable();
            $table->date('fecha');
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->unsignedBigInteger('persona_id')->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->foreign('cultivo_id')->references('id')->on('cultivos')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('cultivo_eventos_avanzados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cultivo_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('fase_id')->nullable();
            $table->enum('tipo', [
                'aplicacion_agroquimico', 'riego', 'fertilizacion',
                'control_fitosanitario', 'deshierbe', 'otro',
            ]);
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->date('fecha');
            $table->string('foto_ruta')->nullable();
            $table->unsignedBigInteger('persona_id')->nullable();
            // Datos de producto
            $table->string('producto_nombre', 150)->nullable();
            $table->string('producto_registro_ica', 50)->nullable();
            $table->decimal('dosis', 10, 4)->nullable();
            $table->string('dosis_unidad', 50)->nullable();
            $table->integer('periodo_carencia_dias')->nullable();
            $table->date('fecha_minima_cosecha')->nullable();
            $table->boolean('alerta_cosecha_activa')->default(false);
            // Datos de riego
            $table->decimal('volumen_agua_litros', 10, 2)->nullable();
            $table->enum('metodo_riego', ['goteo', 'aspersion', 'surco', 'inundacion', 'manual', 'otro'])->nullable();
            $table->integer('duracion_minutos')->nullable();
            // Datos de fertilización
            $table->decimal('nitrogeno_n', 8, 2)->nullable();
            $table->decimal('fosforo_p', 8, 2)->nullable();
            $table->decimal('potasio_k', 8, 2)->nullable();
            $table->string('fuente_fertilizante', 150)->nullable();
            $table->enum('metodo_aplicacion_fertilizante', ['foliar', 'edafico', 'fertiriego', 'otro'])->nullable();
            // Datos de control fitosanitario
            $table->string('plaga_enfermedad', 150)->nullable();
            $table->enum('tipo_control', ['quimico', 'biologico', 'cultural', 'mecanico', 'otro'])->nullable();
            $table->enum('nivel_severidad', ['bajo', 'medio', 'alto', 'critico'])->nullable();
            // Datos de deshierbe
            $table->enum('metodo_deshierbe', ['manual', 'mecanico', 'quimico', 'otro'])->nullable();
            $table->decimal('area_deshierbada_ha', 8, 4)->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('cultivo_id')->references('id')->on('cultivos')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('cultivo_fotos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cultivo_id');
            $table->unsignedBigInteger('usuario_id');
            $table->string('ruta');
            $table->string('titulo', 150)->nullable();
            $table->text('descripcion')->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->foreign('cultivo_id')->references('id')->on('cultivos')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('plan_manejo_cultivo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cultivo_fase_id');
            $table->string('actividad', 200);
            $table->enum('tipo_actividad', [
                'fertilizacion', 'riego', 'control_fitosanitario',
                'aplicacion_agroquimico', 'deshierbe', 'poda', 'monitoreo', 'otro',
            ]);
            $table->text('descripcion')->nullable();
            $table->string('producto_sugerido', 150)->nullable();
            $table->string('dosis_sugerida', 100)->nullable();
            $table->boolean('obligatoria')->default(false);
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('cultivo_fase_id')->references('id')->on('cultivo_fases')->onDelete('cascade');
        });

        Schema::create('rendimiento_regional', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_cultivo', 100);
            $table->string('departamento', 100);
            $table->integer('anio');
            $table->decimal('rendimiento_promedio_ha', 10, 2);
            $table->decimal('rendimiento_min_ha', 10, 2)->nullable();
            $table->decimal('rendimiento_max_ha', 10, 2)->nullable();
            $table->string('unidad', 50)->default('ton/ha');
            $table->string('fuente', 200)->nullable();
            $table->timestamp('creado_en')->useCurrent();
        });

        // ══════════════════════════════════════════════════════════════
        // 4. MÓDULO ANIMALES — NÚCLEO COMPARTIDO
        // ══════════════════════════════════════════════════════════════

        Schema::create('animales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            // Datos generales
            $table->string('especie', 100);
            $table->string('nombre_lote', 150)->nullable();
            $table->integer('cantidad')->default(1);
            $table->date('fecha_ingreso')->nullable();
            $table->enum('estado', ['activo', 'vendido', 'muerte'])->default('activo');
            $table->decimal('peso_promedio', 8, 2)->nullable();
            $table->enum('unidad_peso', ['kg', 'lb'])->default('kg');
            $table->string('ubicacion', 150)->nullable()->comment('Corral, potrero, estanque...');
            $table->date('fecha_nacimiento')->nullable();
            $table->date('fecha_sacrificio')->nullable();
            $table->date('fecha_venta')->nullable();
            $table->decimal('valor_venta', 12, 2)->nullable();
            $table->decimal('precio_kilo', 10, 2)->nullable();
            $table->decimal('precio_unidad', 10, 2)->nullable();
            $table->boolean('vende_por_kilo')->default(true);
            $table->string('propietario', 150)->nullable();
            $table->boolean('favorito')->default(false);
            $table->boolean('atencion_especial')->default(false);
            $table->string('atencion_motivo', 255)->nullable();
            $table->string('etapa_vida', 20)->default('adulto');
            $table->string('produccion', 255)->nullable()->comment('Leche, Huevos, Lana, Cría');
            $table->text('notas')->nullable();
            $table->string('foto')->nullable();
            $table->boolean('pendiente_sync')->default(false);
            // ── Campos bovinos ──────────────────────────────────────────
            $table->string('raza', 80)->nullable()->comment('Brahman, Holstein, Cebú...');
            $table->enum('categoria_bovina', [
                'vaca_lechera', 'vaca_carne', 'novilla', 'ternero', 'toro', 'buey',
            ])->nullable();
            $table->decimal('peso_meta_kg', 8, 2)->nullable();
            $table->unsignedBigInteger('madre_id')->nullable()->comment('Genealogía');
            $table->string('padre_descripcion', 150)->nullable();
            $table->date('fecha_ultimo_parto')->nullable();
            $table->tinyInteger('num_partos')->default(0);
            // ── Campos avícolas ─────────────────────────────────────────
            $table->enum('tipo_ave', [
                'ponedora', 'engorde', 'doble_proposito', 'reproductora', 'pato', 'pavo', 'otro',
            ])->nullable();
            $table->string('linea_ave', 80)->nullable()->comment('Isa Brown, Ross 308, Cobb 500...');
            $table->integer('capacidad_galpon')->nullable();
            $table->date('fecha_nacimiento_lote')->nullable();
            $table->integer('semana_actual')->nullable();
            // ── Campos porcícolas ───────────────────────────────────────
            $table->enum('categoria_porcina', [
                'lechon', 'levante', 'ceba', 'hembra_cria', 'verraco', 'vientre_descarte', 'otro',
            ])->nullable();
            $table->string('raza_porcina', 80)->nullable();
            $table->decimal('peso_entrada_kg', 8, 2)->nullable();
            $table->decimal('peso_meta_sacrificio_kg', 8, 2)->default(100.00);
            $table->datetime('creado_en')->useCurrent();
            $table->datetime('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('animal_eventos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('animal_id');
            $table->unsignedBigInteger('usuario_id');
            $table->string('tipo', 30)->default('nota');
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->string('foto_ruta')->nullable();
            $table->date('fecha');
            $table->string('dosis', 100)->nullable()->comment('Para medicamentos');
            $table->date('proxima_dosis')->nullable()->comment('Para vacunas/medicamentos repetibles');
            $table->unsignedBigInteger('persona_id')->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->datetime('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('animal_id')->references('id')->on('animales')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('animal_fotos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('animal_id');
            $table->unsignedBigInteger('usuario_id');
            $table->string('ruta');
            $table->string('titulo', 150)->nullable();
            $table->text('descripcion')->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->datetime('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('animal_id')->references('id')->on('animales')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('animal_pesos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('animal_id');
            $table->unsignedBigInteger('usuario_id');
            $table->decimal('peso', 8, 2);
            $table->enum('unidad', ['kg', 'lb'])->default('kg');
            $table->date('fecha');
            $table->string('notas', 255)->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->datetime('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('animal_id')->references('id')->on('animales')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('animal_produccion', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('animal_id');
            $table->unsignedBigInteger('usuario_id');
            $table->date('fecha');
            $table->string('tipo_produccion', 50)->comment('leche, huevos, lana, miel...');
            $table->decimal('cantidad', 10, 2);
            $table->string('unidad', 20)->comment('litros, unidades, kg');
            $table->decimal('precio_unitario', 12, 2)->nullable();
            $table->decimal('valor_total', 14, 2)->nullable();
            $table->boolean('vendido')->default(false);
            $table->string('comprador', 150)->nullable();
            $table->boolean('ingreso_creado')->default(false);
            $table->enum('sesion', ['am', 'pm', 'noche', 'manana', 'tarde', 'unica', 'general'])->default('unica');
            $table->enum('destino', [
                'consumo_familiar', 'venta_directa', 'transformacion', 'inventario', 'desperdicio',
            ])->default('venta_directa');
            $table->string('transformacion_tipo', 80)->nullable()->comment('queso, yogur, mantequilla...');
            $table->decimal('costo_estimado', 12, 2)->nullable();
            $table->unsignedBigInteger('inventario_id')->nullable();
            $table->string('periodo', 20)->nullable()->comment('dia, semana, mes');
            $table->string('notas', 255)->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->foreign('animal_id')->references('id')->on('animales')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('animal_propietarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('animal_id');
            $table->unsignedBigInteger('usuario_id');
            $table->string('nombre', 150);
            $table->decimal('porcentaje', 5, 2)->default(100.00);
            $table->string('telefono', 30)->nullable();
            $table->string('notas', 255)->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->datetime('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('animal_id')->references('id')->on('animales')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('animal_sanidad_programada', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->string('protocolo', 60)->comment('aftosa|brucelosis|desparasitacion|vitaminas|carbunco|personalizado_xxx');
            $table->string('nombre_protocolo', 120);
            $table->string('especie_aplicacion', 100)->default('Ganado bovino');
            $table->integer('frecuencia_dias')->comment('Cada cuántos días se repite');
            $table->date('ultima_aplicacion')->nullable();
            $table->date('proxima_aplicacion')->nullable();
            $table->string('producto_usado', 150)->nullable();
            $table->string('dosis', 80)->nullable();
            $table->enum('via_administracion', [
                'subcutanea', 'intramuscular', 'oral', 'intranasal', 'topica', 'otra',
            ])->default('intramuscular');
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->datetime('creado_en')->useCurrent();
            $table->datetime('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        // ── Bovino: reproducción, lactancia, ordeños ────────────────

        Schema::create('animal_reproduccion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('animal_id')->comment('Hembra bovina');
            $table->unsignedBigInteger('usuario_id');
            $table->enum('tipo_servicio', ['monta_natural', 'inseminacion_artificial', 'monta_controlada'])
                  ->default('monta_natural');
            $table->date('fecha_servicio');
            $table->string('macho_descripcion', 150)->nullable()->comment('Nombre del toro o código del semen');
            $table->date('fecha_diagnostico_prenez')->nullable();
            $table->enum('resultado_diagnostico', ['positivo', 'negativo', 'pendiente'])->default('pendiente');
            $table->date('fecha_probable_parto')->nullable()->comment('fecha_servicio + 283 días');
            $table->date('fecha_parto_real')->nullable();
            $table->tinyInteger('num_crias_nacidas')->nullable();
            $table->tinyInteger('num_crias_vivas')->nullable();
            $table->enum('sexo_cria', ['macho', 'hembra', 'mixto'])->nullable();
            $table->decimal('peso_cria_kg', 6, 2)->nullable();
            $table->text('observaciones')->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->datetime('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('animal_id')->references('id')->on('animales')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('animal_lactancia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('animal_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('parto_id')->nullable()->comment('FK a animal_reproduccion');
            $table->tinyInteger('numero_lactancia')->default(1);
            $table->date('fecha_inicio');
            $table->date('fecha_secado')->nullable()->comment('NULL = en producción actualmente');
            $table->decimal('produccion_pico_litros', 8, 2)->nullable();
            $table->date('fecha_pico')->nullable();
            $table->decimal('produccion_acumulada_litros', 12, 2)->default(0.00);
            $table->text('observaciones')->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->datetime('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('animal_id')->references('id')->on('animales')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('animal_ordenos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('animal_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('lactancia_id')->nullable();
            $table->date('fecha');
            $table->enum('sesion', ['am', 'pm', 'unica'])->default('am');
            $table->decimal('litros', 8, 2);
            $table->decimal('temperatura_leche', 5, 2)->nullable()->comment('Celsius');
            $table->string('observaciones', 255)->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->foreign('animal_id')->references('id')->on('animales')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        // ══════════════════════════════════════════════════════════════
        // 5. MÓDULO AVÍCOLA
        // ══════════════════════════════════════════════════════════════

        Schema::create('avicola_postura', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('animal_id')->comment('ID del lote en animales');
            $table->unsignedBigInteger('usuario_id');
            $table->date('fecha');
            $table->integer('huevos_total')->default(0);
            $table->integer('huevos_aa')->default(0)->comment('Extra-grandes');
            $table->integer('huevos_a')->default(0)->comment('Grandes');
            $table->integer('huevos_b')->default(0)->comment('Medianos');
            $table->integer('huevos_sucios')->default(0);
            $table->integer('huevos_rotos')->default(0);
            $table->integer('aves_presentes')->nullable()->comment('Aves en producción ese día');
            $table->decimal('porcentaje_postura', 5, 2)->nullable()->comment('(huevos / aves) × 100');
            $table->decimal('alimento_kg', 8, 2)->nullable();
            $table->decimal('agua_litros', 8, 2)->nullable();
            $table->text('observaciones')->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->foreign('animal_id')->references('id')->on('animales')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('avicola_mortalidad', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('animal_id');
            $table->unsignedBigInteger('usuario_id');
            $table->date('fecha');
            $table->integer('cantidad')->default(1);
            $table->string('causa', 80)->nullable();
            $table->string('descripcion', 255)->nullable();
            $table->integer('descartadas')->default(0)->comment('Aves retiradas vivas por baja condición');
            $table->datetime('creado_en')->useCurrent();
            $table->foreign('animal_id')->references('id')->on('animales')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('avicola_pesos_engorde', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('animal_id');
            $table->unsignedBigInteger('usuario_id');
            $table->integer('semana')->comment('Semana de vida');
            $table->date('fecha');
            $table->decimal('peso_promedio_g', 8, 2)->comment('Gramos');
            $table->integer('aves_pesadas')->nullable();
            $table->decimal('gpd_g', 6, 2)->nullable()->comment('Ganancia diaria en gramos');
            $table->decimal('peso_meta_g', 8, 2)->nullable();
            $table->decimal('uniformidad_pct', 5, 2)->nullable()->comment('% aves ±10% del promedio');
            $table->text('observaciones')->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->foreign('animal_id')->references('id')->on('animales')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('avicola_conversion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('animal_id');
            $table->unsignedBigInteger('usuario_id');
            $table->integer('semana');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->decimal('alimento_consumido_kg', 10, 2);
            $table->decimal('produccion_kg', 10, 2)->comment('kg huevo o ganancia de peso');
            $table->decimal('conversion_alimenticia', 6, 3)->nullable()->comment('menor = mejor');
            $table->enum('tipo', ['postura', 'engorde'])->default('postura');
            $table->text('observaciones')->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->foreign('animal_id')->references('id')->on('animales')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('avicola_vacunacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('animal_id')->nullable()->comment('NULL = todos los lotes del usuario');
            $table->unsignedBigInteger('usuario_id');
            $table->string('protocolo', 60)->comment('newcastle|gumboro|marek|bronquitis|coriza|personalizado_xxx');
            $table->string('nombre_vacuna', 120);
            $table->enum('via_administracion', ['ocular', 'nasal', 'agua', 'inyectable', 'aspersion', 'ala_web'])
                  ->default('ocular');
            $table->integer('dia_vida')->nullable()->comment('Día de vida en que se aplica');
            $table->date('fecha_programada')->nullable();
            $table->date('fecha_aplicada')->nullable();
            $table->string('dosis', 80)->nullable();
            $table->string('producto_comercial', 150)->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->datetime('creado_en')->useCurrent();
            $table->datetime('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('avicola_tabla_peso_std', function (Blueprint $table) {
            $table->id();
            $table->string('linea_ave', 80)->comment('Ross 308, Cobb 500, Arbor Acres');
            $table->integer('semana');
            $table->decimal('peso_meta_g', 8, 2);
            $table->decimal('gpd_meta_g', 6, 2)->nullable();
            $table->decimal('ca_meta', 5, 3)->nullable()->comment('Conversión alimenticia meta');
        });

        // ══════════════════════════════════════════════════════════════
        // 6. MÓDULO PORCÍCOLA
        // ══════════════════════════════════════════════════════════════

        Schema::create('porcicola_camadas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cerda_id')->comment('ID hembra en animales');
            $table->unsignedBigInteger('usuario_id');
            $table->tinyInteger('numero_camada')->default(1);
            $table->date('fecha_servicio');
            $table->enum('tipo_servicio', ['monta_natural', 'inseminacion_artificial'])->default('monta_natural');
            $table->string('verraco_descripcion', 150)->nullable();
            $table->date('fecha_diagnostico')->nullable();
            $table->enum('resultado_diagnostico', ['positivo', 'negativo', 'pendiente'])->default('pendiente');
            $table->date('fecha_probable_parto')->nullable()->comment('fecha_servicio + 114 días');
            $table->date('fecha_parto_real')->nullable();
            $table->tinyInteger('lechones_nacidos_vivos')->nullable();
            $table->tinyInteger('lechones_nacidos_muertos')->nullable();
            $table->tinyInteger('lechones_momificados')->default(0);
            $table->decimal('peso_camada_nacer_kg', 6, 2)->nullable();
            $table->decimal('peso_promedio_nacer_kg', 5, 2)->nullable();
            $table->date('fecha_destete')->nullable();
            $table->tinyInteger('lechones_destetados')->nullable();
            $table->decimal('peso_camada_destete_kg', 6, 2)->nullable();
            $table->decimal('peso_promedio_destete_kg', 5, 2)->nullable();
            $table->tinyInteger('muertes_pre_destete')->default(0);
            $table->string('causa_mortalidad', 150)->nullable();
            $table->text('observaciones')->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->datetime('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('cerda_id')->references('id')->on('animales')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('porcicola_celo_servicio', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cerda_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('camada_id')->nullable();
            $table->string('fase', 50)->comment('calor_detectado|servicio_realizado|prenez_confirmada|parto|lactancia|destete|intervalo_destete_servicio');
            $table->date('fecha');
            $table->string('notas', 255)->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->foreign('cerda_id')->references('id')->on('animales')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('porcicola_pesos_ceba', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('animal_id')->comment('Lote o individuo en animales');
            $table->unsignedBigInteger('usuario_id');
            $table->integer('semana')->comment('Semana en ceba (1 = ingreso)');
            $table->date('fecha');
            $table->decimal('peso_promedio_kg', 8, 2);
            $table->integer('animales_pesados')->nullable();
            $table->decimal('gpd_kg', 5, 3)->nullable()->comment('Ganancia diaria en kg');
            $table->decimal('peso_meta_kg', 8, 2)->nullable();
            $table->decimal('uniformidad_pct', 5, 2)->nullable();
            $table->text('observaciones')->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->foreign('animal_id')->references('id')->on('animales')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('porcicola_conversion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('animal_id');
            $table->unsignedBigInteger('usuario_id');
            $table->integer('semana');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->decimal('alimento_consumido_kg', 10, 2);
            $table->decimal('ganancia_peso_kg', 10, 2);
            $table->decimal('conversion_alimenticia', 5, 3)->nullable()->comment('Ideal: 2.5-3.0 en ceba');
            $table->string('tipo_alimento', 80)->nullable()->comment('Iniciacion, crecimiento, finalizacion');
            $table->text('observaciones')->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->foreign('animal_id')->references('id')->on('animales')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('porcicola_sanidad', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('animal_id')->nullable()->comment('NULL = toda la piara');
            $table->unsignedBigInteger('usuario_id');
            $table->string('protocolo', 60);
            $table->string('nombre_protocolo', 120);
            $table->enum('tipo', ['vacuna', 'desparasitante', 'antibiotico', 'vitamina', 'otro'])->default('vacuna');
            $table->enum('via_administracion', ['intramuscular', 'subcutanea', 'oral', 'topica', 'agua', 'otra'])
                  ->default('intramuscular');
            $table->integer('frecuencia_dias')->nullable();
            $table->date('fecha_programada')->nullable();
            $table->date('fecha_aplicada')->nullable();
            $table->string('producto_usado', 150)->nullable();
            $table->string('dosis', 80)->nullable();
            $table->date('proxima_aplicacion')->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->datetime('creado_en')->useCurrent();
            $table->datetime('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('porcicola_tabla_peso_std', function (Blueprint $table) {
            $table->id();
            $table->string('categoria', 40)->comment('ceba_comercial, ceba_pietrain...');
            $table->integer('semana_ceba')->comment('Semana 1 = 20kg');
            $table->decimal('peso_meta_kg', 6, 2);
            $table->decimal('gpd_meta_kg', 5, 3)->nullable();
            $table->decimal('ca_meta', 4, 3)->nullable();
        });

        // ══════════════════════════════════════════════════════════════
        // 7. MÓDULO PISCÍCOLA
        // ══════════════════════════════════════════════════════════════

        Schema::create('piscicola_estanques', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->string('nombre', 120);
            $table->enum('tipo', ['tierra', 'plastico', 'concreto', 'geomembrana', 'jaula_flotante'])->default('tierra');
            $table->string('especie_cultivada', 80)->default('Cachama');
            $table->decimal('area_m2', 10, 2);
            $table->decimal('profundidad_m', 5, 2)->nullable();
            $table->decimal('volumen_m3', 10, 2)->nullable();
            $table->string('ubicacion', 150)->nullable();
            $table->string('foto')->nullable();
            $table->enum('estado', ['activo', 'vacio', 'mantenimiento', 'cosechado'])->default('vacio');
            $table->text('notas')->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->datetime('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('piscicola_siembras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('estanque_id');
            $table->unsignedBigInteger('usuario_id');
            $table->date('fecha_siembra');
            $table->string('especie', 80);
            $table->integer('cantidad_alevinos');
            $table->decimal('peso_promedio_inicial_g', 8, 3);
            $table->decimal('biomasa_inicial_kg', 10, 3)->nullable();
            $table->decimal('densidad_peces_m2', 8, 3)->nullable();
            $table->string('proveedor', 120)->nullable();
            $table->decimal('costo_alevinos', 12, 2)->nullable();
            $table->decimal('temperatura_recepcion', 5, 2)->nullable();
            $table->text('observaciones')->nullable();
            $table->integer('cantidad_actual')->nullable();
            $table->decimal('peso_promedio_actual_g', 8, 3)->nullable();
            $table->decimal('biomasa_actual_kg', 10, 3)->nullable();
            $table->decimal('alimento_acumulado_kg', 10, 3)->default(0.000);
            $table->boolean('activo')->default(true);
            $table->datetime('creado_en')->useCurrent();
            $table->datetime('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('estanque_id')->references('id')->on('piscicola_estanques')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('piscicola_alimentacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siembra_id');
            $table->unsignedBigInteger('estanque_id');
            $table->unsignedBigInteger('usuario_id');
            $table->date('fecha');
            $table->string('tipo_alimento', 100)->nullable();
            $table->decimal('cantidad_kg', 10, 3);
            $table->decimal('tasa_alimentacion_pct', 5, 2)->nullable();
            $table->tinyInteger('num_raciones')->default(2);
            $table->decimal('biomasa_referencia_kg', 10, 3)->nullable();
            $table->decimal('costo_alimento', 10, 2)->nullable();
            $table->text('observaciones')->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->foreign('siembra_id')->references('id')->on('piscicola_siembras')->onDelete('cascade');
            $table->foreign('estanque_id')->references('id')->on('piscicola_estanques')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('piscicola_calidad_agua', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('estanque_id');
            $table->unsignedBigInteger('usuario_id');
            $table->date('fecha');
            $table->time('hora')->nullable();
            $table->decimal('oxigeno_mgl', 6, 2)->nullable();
            $table->decimal('ph', 5, 2)->nullable();
            $table->decimal('temperatura_c', 5, 2)->nullable();
            $table->decimal('amonio_mgl', 6, 3)->nullable();
            $table->decimal('nitrito_mgl', 6, 3)->nullable();
            $table->integer('transparencia_cm')->nullable();
            $table->boolean('alerta')->default(false);
            $table->text('observaciones')->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->foreign('estanque_id')->references('id')->on('piscicola_estanques')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('piscicola_mortalidad', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siembra_id');
            $table->unsignedBigInteger('estanque_id');
            $table->unsignedBigInteger('usuario_id');
            $table->date('fecha');
            $table->integer('cantidad')->default(1);
            $table->string('causa', 80)->nullable();
            $table->string('descripcion', 255)->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->foreign('siembra_id')->references('id')->on('piscicola_siembras')->onDelete('cascade');
            $table->foreign('estanque_id')->references('id')->on('piscicola_estanques')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('piscicola_muestreos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siembra_id');
            $table->unsignedBigInteger('estanque_id');
            $table->unsignedBigInteger('usuario_id');
            $table->date('fecha');
            $table->integer('semana_cultivo')->nullable();
            $table->integer('peces_muestreados');
            $table->decimal('peso_promedio_g', 8, 3);
            $table->integer('cantidad_estimada')->nullable();
            $table->decimal('biomasa_estimada_kg', 10, 3)->nullable();
            $table->decimal('ganancia_diaria_g', 6, 3)->nullable();
            $table->text('observaciones')->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->foreign('siembra_id')->references('id')->on('piscicola_siembras')->onDelete('cascade');
            $table->foreign('estanque_id')->references('id')->on('piscicola_estanques')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('piscicola_cosechas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siembra_id');
            $table->unsignedBigInteger('estanque_id');
            $table->unsignedBigInteger('usuario_id');
            $table->date('fecha_cosecha');
            $table->integer('dias_cultivo')->nullable();
            $table->integer('cantidad_cosechada');
            $table->decimal('peso_promedio_final_g', 8, 3);
            $table->decimal('biomasa_cosechada_kg', 10, 3)->nullable();
            $table->integer('cantidad_sembrada')->nullable();
            $table->decimal('sobrevivencia_pct', 5, 2)->nullable();
            $table->decimal('alimento_total_kg', 10, 3)->nullable();
            $table->decimal('conversion_alimenticia', 6, 3)->nullable();
            $table->decimal('rendimiento_kg_m2', 8, 3)->nullable();
            $table->decimal('precio_kg_cop', 12, 2)->nullable();
            $table->decimal('valor_total_cop', 14, 2)->nullable();
            $table->string('comprador', 120)->nullable();
            $table->enum('destino', ['venta_directa', 'intermediario', 'cooperativa', 'consumo_propio', 'otro'])
                  ->default('venta_directa');
            $table->text('observaciones')->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->foreign('siembra_id')->references('id')->on('piscicola_siembras')->onDelete('cascade');
            $table->foreign('estanque_id')->references('id')->on('piscicola_estanques')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        // ══════════════════════════════════════════════════════════════
        // 8. FINANZAS (Cosechas · Gastos · Ingresos · Costos)
        // ══════════════════════════════════════════════════════════════

        Schema::create('cosechas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('cultivo_id')->nullable();
            $table->string('producto', 150);
            $table->decimal('cantidad', 10, 2);
            $table->string('unidad', 50);
            $table->decimal('precio_unitario', 12, 2)->nullable();
            $table->decimal('valor_estimado', 12, 2)->nullable();
            $table->date('fecha_cosecha');
            $table->string('calidad', 20)->default('buena');
            $table->string('destino', 60)->nullable()->comment('autoconsumo, venta, almacenaje');
            $table->string('comprador', 150)->nullable();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->boolean('ingreso_creado')->default(false)->comment('1 si ya se generó el ingreso automático');
            $table->text('observaciones')->nullable();
            $table->string('foto')->nullable();
            $table->decimal('merma_porcentaje', 5, 2)->nullable()->comment('% de pérdida estimada');
            $table->string('almacen_ubicacion', 150)->nullable();
            $table->date('almacen_hasta')->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->datetime('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('cultivo_id')->references('id')->on('cultivos')->onDelete('set null');
        });

        Schema::create('gastos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('cultivo_id')->nullable();
            $table->unsignedBigInteger('animal_id')->nullable();
            $table->unsignedBigInteger('persona_id')->nullable();
            $table->unsignedBigInteger('cosecha_id')->nullable();
            $table->unsignedBigInteger('tarea_id')->nullable();
            $table->string('categoria', 100);
            $table->string('descripcion', 255);
            $table->decimal('cantidad', 10, 2)->nullable();
            $table->string('unidad_cantidad', 50)->nullable();
            $table->decimal('valor', 12, 2);
            $table->date('fecha');
            $table->string('proveedor', 150)->nullable();
            $table->unsignedBigInteger('proveedor_id')->nullable();
            $table->string('factura_numero', 100)->nullable();
            $table->text('notas')->nullable();
            $table->string('foto_factura')->nullable();
            $table->boolean('es_recurrente')->default(false);
            $table->unsignedBigInteger('recurrente_id')->nullable();
            $table->boolean('pendiente_sync')->default(false);
            $table->datetime('creado_en')->useCurrent();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('cultivo_id')->references('id')->on('cultivos')->onDelete('set null');
            $table->foreign('animal_id')->references('id')->on('animales')->onDelete('set null');
        });

        Schema::create('gastos_recurrentes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->string('categoria', 100);
            $table->string('descripcion', 255);
            $table->decimal('valor', 12, 2);
            $table->string('proveedor', 150)->nullable();
            $table->unsignedBigInteger('proveedor_id')->nullable();
            $table->unsignedBigInteger('cultivo_id')->nullable();
            $table->unsignedBigInteger('animal_id')->nullable();
            $table->enum('frecuencia', ['semanal', 'quincenal', 'mensual', 'bimestral', 'trimestral', 'anual'])
                  ->default('mensual');
            $table->tinyInteger('dia_del_mes')->default(1)->comment('Día del mes en que se genera');
            $table->boolean('activo')->default(true);
            $table->date('ultimo_generado')->nullable();
            $table->date('proximo_vencimiento')->nullable();
            $table->text('notas')->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->datetime('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('ingresos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('cultivo_id')->nullable();
            $table->unsignedBigInteger('animal_id')->nullable();
            $table->unsignedBigInteger('cosecha_id')->nullable();
            $table->string('tipo', 50)->default('venta');
            $table->string('descripcion', 255);
            $table->decimal('cantidad', 10, 2)->nullable();
            $table->string('unidad', 50)->nullable();
            $table->decimal('precio_unitario', 12, 2)->nullable();
            $table->decimal('valor_total', 12, 2);
            $table->date('fecha');
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->string('referencia_tipo', 30)->nullable();
            $table->string('comprador', 150)->nullable();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->unsignedBigInteger('persona_id')->nullable();
            $table->text('notas')->nullable();
            $table->string('foto_soporte')->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('cultivo_id')->references('id')->on('cultivos')->onDelete('set null');
            $table->foreign('animal_id')->references('id')->on('animales')->onDelete('set null');
        });

        Schema::create('produccion_costos_periodo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('animal_id');
            $table->unsignedBigInteger('usuario_id');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->decimal('costo_alimentacion', 12, 2)->default(0.00);
            $table->decimal('costo_sanidad', 12, 2)->default(0.00);
            $table->decimal('costo_mano_obra', 12, 2)->default(0.00);
            $table->decimal('costo_otros', 12, 2)->default(0.00);
            $table->decimal('costo_total', 12, 2)->default(0.00);
            $table->decimal('unidades_producidas', 12, 3)->default(0.000);
            $table->string('unidad', 20)->nullable();
            $table->string('tipo_produccion', 50)->nullable();
            $table->decimal('costo_por_unidad', 12, 4)->nullable();
            $table->decimal('precio_venta_promedio', 12, 2)->nullable();
            $table->decimal('margen_unitario', 12, 4)->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->datetime('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('animal_id')->references('id')->on('animales')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        // ══════════════════════════════════════════════════════════════
        // 9. INVENTARIO
        // ══════════════════════════════════════════════════════════════

        Schema::create('inventario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->string('nombre', 150);
            $table->string('categoria', 100);
            $table->decimal('cantidad_actual', 10, 2)->default(0.00);
            $table->decimal('stock_minimo', 10, 2)->default(0.00);
            $table->string('unidad', 50);
            $table->decimal('precio_unitario', 12, 2)->nullable();
            $table->string('proveedor', 150)->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->text('notas')->nullable();
            $table->string('foto')->nullable();
            $table->string('ubicacion', 150)->nullable()->comment('Bodega, estante, nevera...');
            $table->string('uso_principal', 50)->default('cultivo')->comment('cultivo, animal, general');
            $table->datetime('creado_en')->useCurrent();
            $table->datetime('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('inventario_movimientos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventario_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('cultivo_id')->nullable();
            $table->unsignedBigInteger('animal_id')->nullable();
            $table->unsignedBigInteger('persona_id')->nullable();
            $table->enum('tipo', ['entrada', 'salida', 'ajuste', 'en_uso', 'devolucion']);
            $table->decimal('cantidad', 10, 2);
            $table->decimal('precio_unitario', 12, 2)->nullable();
            $table->string('motivo', 200)->nullable();
            $table->string('persona', 100)->nullable();
            $table->string('foto_soporte')->nullable();
            $table->date('fecha');
            $table->datetime('creado_en')->useCurrent();
            $table->foreign('inventario_id')->references('id')->on('inventario')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('cultivo_id')->references('id')->on('cultivos')->onDelete('set null');
            $table->foreign('animal_id')->references('id')->on('animales')->onDelete('set null');
        });

        // ══════════════════════════════════════════════════════════════
        // 10. PERSONAS / NÓMINA
        // ══════════════════════════════════════════════════════════════

        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->enum('tipo', ['trabajador', 'proveedor', 'comprador', 'vecino', 'familiar', 'contacto', 'otro'])
                  ->default('contacto');
            $table->string('nombre', 150);
            $table->string('telefono', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('documento', 30)->nullable()->comment('Cédula o NIT');
            $table->string('direccion', 255)->nullable();
            $table->string('foto')->nullable();
            $table->string('cargo', 100)->nullable()->comment('Jornalero, Administrador, Ordeñador...');
            $table->enum('tipo_contrato', ['jornal', 'mensual', 'destajo', 'temporal', 'otro'])->nullable();
            $table->decimal('valor_jornal', 12, 2)->nullable();
            $table->decimal('valor_mensual', 12, 2)->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->string('labores', 255)->nullable()->comment('Qué labores realiza');
            $table->boolean('activo')->default(true);
            $table->text('notas')->nullable();
            $table->boolean('favorito')->default(false);
            $table->datetime('creado_en')->useCurrent();
            $table->datetime('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('persona_labores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('persona_id');
            $table->unsignedBigInteger('usuario_id');
            $table->date('fecha');
            $table->string('descripcion', 255);
            $table->unsignedBigInteger('cultivo_id')->nullable();
            $table->unsignedBigInteger('animal_id')->nullable();
            $table->decimal('horas', 5, 2)->nullable();
            $table->string('insumos_usados', 255)->nullable();
            $table->text('notas')->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->foreign('persona_id')->references('id')->on('personas')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('persona_pagos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('persona_id');
            $table->unsignedBigInteger('usuario_id');
            $table->enum('tipo_pago', ['jornal', 'mensual', 'bono', 'anticipo', 'otro'])->default('jornal');
            $table->decimal('dias', 5, 2)->nullable()->comment('Días trabajados (para jornal)');
            $table->decimal('valor', 12, 2);
            $table->date('fecha');
            $table->unsignedBigInteger('cultivo_id')->nullable();
            $table->unsignedBigInteger('animal_id')->nullable();
            $table->string('concepto', 255)->nullable();
            $table->string('notas', 255)->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->foreign('persona_id')->references('id')->on('personas')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        // ══════════════════════════════════════════════════════════════
        // 11. AGENDA / ALERTAS DEL DASHBOARD
        // ══════════════════════════════════════════════════════════════

        Schema::create('tareas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('cultivo_id')->nullable();
            $table->unsignedBigInteger('animal_id')->nullable();
            $table->string('titulo', 200);
            $table->string('tipo', 60)->default('otro');
            $table->date('fecha');
            $table->time('hora')->nullable();
            $table->boolean('completada')->default(false);
            $table->datetime('fecha_completada')->nullable();
            $table->text('notas')->nullable();
            $table->string('responsable', 100)->nullable();
            $table->text('notas_completada')->nullable();
            $table->enum('prioridad', ['baja', 'media', 'alta'])->default('media');
            $table->boolean('pendiente_sync')->default(false);
            $table->unsignedBigInteger('persona_completada_id')->nullable();
            $table->datetime('creado_en')->useCurrent();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('cultivo_id')->references('id')->on('cultivos')->onDelete('set null');
            $table->foreign('animal_id')->references('id')->on('animales')->onDelete('set null');
        });

        Schema::create('dashboard_alertas_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->string('tipo_alerta', 80)->comment('vacuna_bovino, tarea_vencida, stock_bajo...');
            $table->string('referencia', 150)->nullable()->comment('ID del registro que generó la alerta');
            $table->boolean('visto')->default(false);
            $table->boolean('descartado')->default(false);
            $table->date('fecha');
            $table->datetime('creado_en')->useCurrent();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });
    }

    // ─────────────────────────────────────────────────────────────────
    // DOWN — orden inverso al de creación para respetar FK
    // ─────────────────────────────────────────────────────────────────
    public function down(): void
    {
        // 11. Agenda
        Schema::dropIfExists('dashboard_alertas_log');
        Schema::dropIfExists('tareas');
        // 10. Personas
        Schema::dropIfExists('persona_pagos');
        Schema::dropIfExists('persona_labores');
        Schema::dropIfExists('personas');
        // 9. Inventario
        Schema::dropIfExists('inventario_movimientos');
        Schema::dropIfExists('inventario');
        // 8. Finanzas
        Schema::dropIfExists('produccion_costos_periodo');
        Schema::dropIfExists('ingresos');
        Schema::dropIfExists('gastos_recurrentes');
        Schema::dropIfExists('gastos');
        Schema::dropIfExists('cosechas');
        // 7. Piscícola
        Schema::dropIfExists('piscicola_cosechas');
        Schema::dropIfExists('piscicola_muestreos');
        Schema::dropIfExists('piscicola_mortalidad');
        Schema::dropIfExists('piscicola_calidad_agua');
        Schema::dropIfExists('piscicola_alimentacion');
        Schema::dropIfExists('piscicola_siembras');
        Schema::dropIfExists('piscicola_estanques');
        // 6. Porcícola
        Schema::dropIfExists('porcicola_tabla_peso_std');
        Schema::dropIfExists('porcicola_sanidad');
        Schema::dropIfExists('porcicola_conversion');
        Schema::dropIfExists('porcicola_pesos_ceba');
        Schema::dropIfExists('porcicola_celo_servicio');
        Schema::dropIfExists('porcicola_camadas');
        // 5. Avícola
        Schema::dropIfExists('avicola_tabla_peso_std');
        Schema::dropIfExists('avicola_vacunacion');
        Schema::dropIfExists('avicola_conversion');
        Schema::dropIfExists('avicola_pesos_engorde');
        Schema::dropIfExists('avicola_mortalidad');
        Schema::dropIfExists('avicola_postura');
        // 4. Animales
        Schema::dropIfExists('animal_ordenos');
        Schema::dropIfExists('animal_lactancia');
        Schema::dropIfExists('animal_reproduccion');
        Schema::dropIfExists('animal_sanidad_programada');
        Schema::dropIfExists('animal_propietarios');
        Schema::dropIfExists('animal_produccion');
        Schema::dropIfExists('animal_pesos');
        Schema::dropIfExists('animal_fotos');
        Schema::dropIfExists('animal_eventos');
        Schema::dropIfExists('animales');
        // 3. Cultivos
        Schema::dropIfExists('rendimiento_regional');
        Schema::dropIfExists('plan_manejo_cultivo');
        Schema::dropIfExists('cultivo_fotos');
        Schema::dropIfExists('cultivo_eventos_avanzados');
        Schema::dropIfExists('cultivo_eventos');
        Schema::dropIfExists('cultivo_historial_fases');
        Schema::dropIfExists('cultivos');
        Schema::dropIfExists('cultivo_fases');
        // 2. Config
        Schema::dropIfExists('usuario_lineas');
        Schema::dropIfExists('lineas_productivas');
        // 1. Core
        Schema::dropIfExists('sesiones');
        Schema::dropIfExists('usuarios');
    }
};