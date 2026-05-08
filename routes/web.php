<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CultivoFaseController;
use App\Http\Controllers\CultivoController;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\IngresoController;
use App\Http\Controllers\AnimalController;
use App\Http\Controllers\TareaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\CosechaController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\RentabilidadController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\EncuestaController;
use App\Http\Controllers\ProduccionAnimalController;
use App\Http\Controllers\BovinoController;                 // ← Fase 4
use App\Http\Controllers\AvicolaController;
use App\Http\Controllers\PorcicolaController;
use App\Http\Controllers\PiscicolaController;
use App\Http\Controllers\ReportesController;


// ── Públicas ─────────────────────────────────────────────────
Route::get('/',         [AuthController::class, 'welcome'])->name('welcome');
Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',   [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register',[AuthController::class, 'register'])->name('register.post');
Route::post('/logout',  [AuthController::class, 'logout'])->name('logout');


// ── Protegidas (requieren auth) ────────────────────────────────
Route::middleware('auth.session')->group(function () {

    Route::get('/onboarding',  [AuthController::class, 'onboarding'])->name('onboarding');
    Route::post('/onboarding', [AuthController::class, 'onboardingComplete'])->name('onboarding.complete');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Exportaciones
    Route::get('/exportar/cultivos/pdf',     [ExportController::class, 'cultivosPdf'])->name('exportar.cultivos.pdf');
    Route::get('/exportar/cultivos/excel',   [ExportController::class, 'cultivosExcel'])->name('exportar.cultivos.excel');
    Route::get('/exportar/gastos/pdf',       [ExportController::class, 'gastosPdf'])->name('exportar.gastos.pdf');
    Route::get('/exportar/gastos/excel',     [ExportController::class, 'gastosExcel'])->name('exportar.gastos.excel');
    Route::get('/exportar/cosechas/pdf',     [ExportController::class, 'cosechasPdf'])->name('exportar.cosechas.pdf');
    Route::get('/exportar/cosechas/excel',   [ExportController::class, 'cosechasExcel'])->name('exportar.cosechas.excel');
    Route::get('/exportar/reporte/pdf',      [ExportController::class, 'reporteGeneralPdf'])->name('exportar.reporte.pdf');
    Route::get('/exportar/animales/pdf',     [ExportController::class, 'animalesPdf'])->name('exportar.animales.pdf');
    Route::get('/exportar/nomina/pdf',       [ExportController::class, 'nominaPdf'])->name('exportar.nomina.pdf');
    Route::get('/exportar/inventario/pdf',   [ExportController::class, 'inventarioPdf'])->name('exportar.inventario.pdf');
    Route::get('/exportar/rentabilidad/pdf', [ExportController::class, 'rentabilidadPdf'])->name('exportar.rentabilidad.pdf');
    Route::get('/exportar/produccion/pdf',   [ExportController::class, 'produccionPdf'])->name('exportar.produccion.pdf');

    // Inventario
    Route::get('/inventario',                  [InventarioController::class, 'index'])->name('inventario.index');
    Route::post('/inventario',                 [InventarioController::class, 'store'])->name('inventario.store');
    Route::post('/inventario/{id}',            [InventarioController::class, 'update'])->name('inventario.update');
    Route::post('/inventario/{id}/delete',     [InventarioController::class, 'destroy'])->name('inventario.destroy');
    Route::post('/inventario/{id}/movimiento', [InventarioController::class, 'movimiento'])->name('inventario.movimiento');
    Route::get('/inventario/alertas',          [InventarioController::class, 'alertas'])->name('inventario.alertas');

    // Rentabilidad
    Route::get('/rentabilidad',      [RentabilidadController::class, 'index'])->name('rentabilidad.index');
    Route::get('/rentabilidad/{id}', [RentabilidadController::class, 'detalle'])->name('rentabilidad.detalle');

    // Cultivos
    Route::get('/cultivos',                                          [CultivoController::class, 'index'])->name('cultivos.index');
    Route::post('/cultivos',                                         [CultivoController::class, 'store'])->name('cultivos.store');
    Route::get('/cultivos/{id}',                                     [CultivoController::class, 'show'])->name('cultivos.show');
    Route::post('/cultivos/{id}',                                    [CultivoController::class, 'update'])->name('cultivos.update');
    Route::post('/cultivos/{id}/delete',                             [CultivoController::class, 'destroy'])->name('cultivos.destroy');
    Route::post('/cultivos/{id}/fotos',                              [CultivoController::class, 'uploadFoto'])->name('cultivos.fotos.upload');
    Route::post('/cultivos/{cultivoId}/fotos/{fotoId}/delete',       [CultivoController::class, 'deleteFoto'])->name('cultivos.fotos.delete');
    Route::post('/cultivos/{id}/eventos',                            [CultivoController::class, 'storeEvento'])->name('cultivos.eventos.store');
    Route::post('/cultivos/{cultivoId}/eventos/{eventoId}/delete',   [CultivoController::class, 'destroyEvento'])->name('cultivos.eventos.delete');
    // ── Fase 3: Cultivos Avanzados ─────────────────────────────────────────────
    Route::post('/cultivos/{id}/fase',                                         [CultivoFaseController::class, 'cambiarFase'])->name('cultivos.fase.cambiar');
    Route::get('/cultivos/{id}/fenologia',                                     [CultivoFaseController::class, 'fenologia'])->name('cultivos.fenologia');
    Route::get('/cultivos/{id}/fenologia/data',                                [CultivoFaseController::class, 'fenologiaData'])->name('cultivos.fenologia.data');
    Route::post('/cultivos/{id}/rendimiento',                                  [CultivoFaseController::class, 'actualizarRendimiento'])->name('cultivos.rendimiento.update');
    Route::post('/cultivos/{id}/eventos-avanzados',                            [CultivoFaseController::class, 'storeEventoAvanzado'])->name('cultivos.eventos-avanzados.store');
    Route::post('/cultivos/{cultivoId}/eventos-avanzados/{eventoId}/delete',   [CultivoFaseController::class, 'destroyEventoAvanzado'])->name('cultivos.eventos-avanzados.delete');

    // Cosechas
    Route::get('/cosechas',              [CosechaController::class, 'index'])->name('cosechas.index');
    Route::post('/cosechas',             [CosechaController::class, 'store'])->name('cosechas.store');
    Route::post('/cosechas/{id}',        [CosechaController::class, 'update'])->name('cosechas.update');
    Route::post('/cosechas/{id}/delete', [CosechaController::class, 'destroy'])->name('cosechas.destroy');

    // Gastos — rutas estáticas primero, dinámicas al final
    Route::get('/gastos',                             [GastoController::class, 'index'])->name('gastos.index');
    Route::post('/gastos',                            [GastoController::class, 'store'])->name('gastos.store');
    Route::post('/gastos/proveedores',                [GastoController::class, 'storeProveedor'])->name('gastos.proveedor.store');
    Route::post('/gastos/proveedores/{id}/delete',    [GastoController::class, 'destroyProveedor'])->name('gastos.proveedor.destroy');
    Route::post('/gastos/recurrentes',                [GastoController::class, 'storeRecurrente'])->name('gastos.recurrente.store');
    Route::post('/gastos/recurrentes/{id}/generar',   [GastoController::class, 'generarRecurrente'])->name('gastos.recurrente.generar');
    Route::post('/gastos/recurrentes/{id}/delete',    [GastoController::class, 'destroyRecurrente'])->name('gastos.recurrente.destroy');
    Route::post('/gastos/{id}',                       [GastoController::class, 'update'])->name('gastos.update');
    Route::post('/gastos/{id}/delete',                [GastoController::class, 'destroy'])->name('gastos.destroy');

    // Ingresos
    Route::get('/ingresos',                         [IngresoController::class, 'index'])->name('ingresos.index');
    Route::post('/ingresos',                        [IngresoController::class, 'store'])->name('ingresos.store');
    Route::post('/ingresos/clientes',               [IngresoController::class, 'storeCliente'])->name('ingresos.cliente.store');
    Route::post('/ingresos/clientes/{id}/delete',   [IngresoController::class, 'destroyCliente'])->name('ingresos.cliente.destroy');
    Route::post('/ingresos/{id}',                   [IngresoController::class, 'update'])->name('ingresos.update');
    Route::post('/ingresos/{id}/delete',            [IngresoController::class, 'destroy'])->name('ingresos.destroy');

    // Animales
    Route::get('/animales',                                    [AnimalController::class, 'index'])->name('animales.index');
    Route::post('/animales',                                   [AnimalController::class, 'store'])->name('animales.store');
    Route::get('/animales/{id}',                               [AnimalController::class, 'show'])->name('animales.show');
    Route::post('/animales/{id}/fotos',                        [AnimalController::class, 'uploadFoto'])->name('animales.fotos.upload');
    Route::post('/animales/{aid}/fotos/{fid}/delete',          [AnimalController::class, 'deleteFoto'])->name('animales.fotos.delete');
    Route::post('/animales/{id}/pesos',                        [AnimalController::class, 'storePeso'])->name('animales.pesos.store');
    Route::post('/animales/{id}/eventos',                      [AnimalController::class, 'storeEvento'])->name('animales.eventos.store');
    Route::post('/animales/{aid}/eventos/{eid}/delete',        [AnimalController::class, 'destroyEvento'])->name('animales.eventos.delete');
    Route::post('/animales/{id}/propietarios',                 [AnimalController::class, 'storePropietario'])->name('animales.propietario.store');
    Route::post('/animales/{aid}/propietarios/{pid}/delete',   [AnimalController::class, 'destroyPropietario'])->name('animales.propietario.delete');
    Route::post('/animales/{id}/favorito',                     [AnimalController::class, 'toggleFavorito'])->name('animales.favorito');
    Route::post('/animales/{id}/atencion',                     [AnimalController::class, 'toggleAtencion'])->name('animales.atencion');
    Route::post('/animales/{id}/salida',                       [AnimalController::class, 'registrarSalida'])->name('animales.salida');
    Route::post('/animales/{id}',                              [AnimalController::class, 'update'])->name('animales.update');
    Route::post('/animales/{id}/delete',                       [AnimalController::class, 'destroy'])->name('animales.destroy');

    // Producción animal
    Route::get('/produccion-animal',          [ProduccionAnimalController::class, 'index'])->name('produccion-animal.index');
    Route::post('/produccion-animal',         [ProduccionAnimalController::class, 'store'])->name('produccion-animal.store');
    Route::delete('/produccion-animal/{id}',  [ProduccionAnimalController::class, 'destroy'])->name('produccion-animal.destroy');

    // Tareas / Calendario
    Route::get('/calendario',              [TareaController::class, 'index'])->name('calendario.index');
    Route::post('/tareas',                 [TareaController::class, 'store'])->name('tareas.store');
    Route::post('/tareas/{id}',            [TareaController::class, 'update'])->name('tareas.update');
    Route::post('/tareas/{id}/completar',  [TareaController::class, 'completar'])->name('tareas.completar');
    Route::post('/tareas/{id}/delete',     [TareaController::class, 'destroy'])->name('tareas.destroy');

    // Reportes
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');

    // Perfil
    Route::get('/perfil',                [PerfilController::class, 'index'])->name('perfil.index');
    Route::post('/perfil',               [PerfilController::class, 'update'])->name('perfil.update');
    Route::post('/perfil/password',      [PerfilController::class, 'changePassword'])->name('perfil.password');
    Route::post('/perfil/preferencias',  [PerfilController::class, 'updateNotificaciones'])->name('perfil.notificaciones');
    Route::post('/perfil/lineas',        [PerfilController::class, 'updateLineas'])->name('perfil.lineas');

    // Personas
    Route::get('/personas',                                  [PersonaController::class, 'index'])->name('personas.index');
    Route::post('/personas',                                 [PersonaController::class, 'store'])->name('personas.store');
    Route::get('/personas/{id}',                             [PersonaController::class, 'show'])->name('personas.show');
    Route::post('/personas/{id}',                            [PersonaController::class, 'update'])->name('personas.update');
    Route::post('/personas/{id}/delete',                     [PersonaController::class, 'destroy'])->name('personas.destroy');
    Route::post('/personas/{id}/favorito',                   [PersonaController::class, 'toggleFavorito'])->name('personas.favorito');
    Route::post('/personas/{id}/pagos',                      [PersonaController::class, 'storePago'])->name('personas.pago.store');
    Route::post('/personas/{pid}/pagos/{lid}/delete',        [PersonaController::class, 'destroyPago'])->name('personas.pago.delete');
    Route::post('/personas/{id}/labores',                    [PersonaController::class, 'storeLabor'])->name('personas.labor.store');
    Route::post('/personas/{pid}/labores/{lid}/delete',      [PersonaController::class, 'destroyLabor'])->name('personas.labor.delete');

    // Encuesta de impacto
    Route::get('/encuesta',          [EncuestaController::class, 'show'])->name('encuesta.show');
    Route::post('/encuesta',         [EncuestaController::class, 'store'])->name('encuesta.store');
    Route::post('/encuesta/ignorar', [EncuestaController::class, 'ignorar'])->name('encuesta.ignorar');

    // ── FASE 4: BOVINO ESPECIALIZADO ──────────────────────────────────

    // Hato — Dashboard bovino
    Route::get('/bovino', [BovinoController::class, 'hato'])->name('bovino.hato');

    // Ordeños
    Route::get( '/bovino/ordenos',              [BovinoController::class, 'ordenos'])->name('bovino.ordenos');
    Route::post('/bovino/ordenos',              [BovinoController::class, 'storeOrdeno'])->name('bovino.ordenos.store');
    Route::post('/bovino/ordenos/{id}/delete',  [BovinoController::class, 'destroyOrdeno'])->name('bovino.ordenos.delete');

    // Lactancias
    Route::post('/bovino/ordenos/lactancia',    [BovinoController::class, 'storeLactancia'])->name('bovino.ordenos.lactancia');
    Route::post('/bovino/ordenos/secar/{id}',   [BovinoController::class, 'secarVaca'])->name('bovino.ordenos.secar');

    // Reproducción
    Route::get( '/bovino/reproduccion',                    [BovinoController::class, 'reproduccion'])->name('bovino.reproduccion');
    Route::post('/bovino/reproduccion/servicio',           [BovinoController::class, 'storeServicio'])->name('bovino.reproduccion.servicio');
    Route::post('/bovino/reproduccion/{id}/prenez',        [BovinoController::class, 'confirmarPrenez'])->name('bovino.reproduccion.prenez');
    Route::post('/bovino/reproduccion/parto',              [BovinoController::class, 'storeParto'])->name('bovino.reproduccion.parto');

    // Sanidad
    Route::get( '/bovino/sanidad',                         [BovinoController::class, 'sanidad'])->name('bovino.sanidad');
    Route::post('/bovino/sanidad/{id}/aplicar',            [BovinoController::class, 'aplicarSanidad'])->name('bovino.sanidad.aplicar');
    Route::post('/bovino/sanidad/personalizado',           [BovinoController::class, 'storeSanidadPersonalizado'])->name('bovino.sanidad.personalizado');

    // Pesaje
    Route::get( '/bovino/pesaje',                          [BovinoController::class, 'pesaje'])->name('bovino.pesaje');
    Route::post('/bovino/pesaje',                          [BovinoController::class, 'storePeso'])->name('bovino.pesaje.store');

    // Reportes bovinos
    // ── Venta de producción de leche
    Route::post('/bovino/produccion/vender',
        [BovinoController::class, 'venderProduccion'])->name('bovino.produccion.vender');

    Route::get('/bovino/reportes',                         [BovinoController::class, 'reportes'])->name('bovino.reportes');

    // ── AVÍCOLA (Fase 5) ──────────────────────────────────────────────
Route::get('/avicola', [AvicolaController::class, 'galpon'])->name('avicola.galpon');
Route::get('/avicola/postura', [AvicolaController::class, 'postura'])->name('avicola.postura');
Route::post('/avicola/postura', [AvicolaController::class, 'storePostura'])->name('avicola.postura.store');
Route::post('/avicola/postura/{id}/delete', [AvicolaController::class, 'destroyPostura'])->name('avicola.postura.delete');
Route::get('/avicola/engorde', [AvicolaController::class, 'engorde'])->name('avicola.engorde');
Route::post('/avicola/engorde/peso', [AvicolaController::class, 'storePesoEngorde'])->name('avicola.engorde.peso');
Route::get('/avicola/mortalidad', [AvicolaController::class, 'mortalidad'])->name('avicola.mortalidad');
Route::post('/avicola/mortalidad', [AvicolaController::class, 'storeMortalidad'])->name('avicola.mortalidad.store');
Route::get('/avicola/vacunacion', [AvicolaController::class, 'vacunacion'])->name('avicola.vacunacion');
Route::post('/avicola/vacunacion/{id}/aplicar', [AvicolaController::class, 'aplicarVacuna'])->name('avicola.vacunacion.aplicar');
Route::post('/avicola/vacunacion/personalizada', [AvicolaController::class, 'storeVacunaPersonalizada'])->name('avicola.vacunacion.personalizada');
Route::get('/avicola/conversion', [AvicolaController::class, 'conversion'])->name('avicola.conversion');
Route::post('/avicola/conversion', [AvicolaController::class, 'storeConversion'])->name('avicola.conversion.store');
Route::get('/avicola/reportes', [AvicolaController::class, 'reportes'])->name('avicola.reportes');

// ── PORCÍCOLA (Fase 6) ────────────────────────────────────────────
Route::get('/porcicola', [PorcicolaController::class, 'piara'])->name('porcicola.piara');
Route::get('/porcicola/reproductivo', [PorcicolaController::class, 'reproductivo'])->name('porcicola.reproductivo');
Route::post('/porcicola/reproductivo/servicio', [PorcicolaController::class, 'storeServicio'])->name('porcicola.reproductivo.servicio');
Route::post('/porcicola/reproductivo/{id}/diagnostico', [PorcicolaController::class, 'confirmarPrenez'])->name('porcicola.reproductivo.diagnostico');
Route::post('/porcicola/reproductivo/parto', [PorcicolaController::class, 'storeParto'])->name('porcicola.reproductivo.parto');
Route::post('/porcicola/reproductivo/destete', [PorcicolaController::class, 'storeDestete'])->name('porcicola.reproductivo.destete');
Route::get('/porcicola/ceba', [PorcicolaController::class, 'ceba'])->name('porcicola.ceba');
Route::post('/porcicola/ceba/peso', [PorcicolaController::class, 'storePesoCeba'])->name('porcicola.ceba.peso');
Route::post('/porcicola/ceba/conversion', [PorcicolaController::class, 'storeConversion'])->name('porcicola.ceba.conversion');
Route::get('/porcicola/sanidad', [PorcicolaController::class, 'sanidad'])->name('porcicola.sanidad');
Route::post('/porcicola/sanidad/{id}/aplicar', [PorcicolaController::class, 'aplicarSanidad'])->name('porcicola.sanidad.aplicar');
Route::post('/porcicola/sanidad/personalizado', [PorcicolaController::class, 'storeSanidadPersonalizada'])->name('porcicola.sanidad.personalizado');
Route::get('/porcicola/reportes', [PorcicolaController::class, 'reportes'])->name('porcicola.reportes');

// Estanques — Dashboard
Route::get('/piscicola',
        [PiscicolaController::class, 'estanques'])->name('piscicola.estanques');
Route::post('/piscicola/estanques',
        [PiscicolaController::class, 'storeEstanque'])->name('piscicola.estanques.store');
Route::post('/piscicola/estanques/{id}/update',
        [PiscicolaController::class, 'updateEstanque'])->name('piscicola.estanques.update');
 
    // Siembra
Route::get('/piscicola/siembra',
        [PiscicolaController::class, 'siembra'])->name('piscicola.siembra');
Route::post('/piscicola/siembra',
        [PiscicolaController::class, 'storeSiembra'])->name('piscicola.siembra.store');
 
    // Muestreos de biomasa
Route::get('/piscicola/muestreo',
        [PiscicolaController::class, 'muestreo'])->name('piscicola.muestreo');
Route::post('/piscicola/muestreo',
        [PiscicolaController::class, 'storeMuestreo'])->name('piscicola.muestreo.store');
 
    // Alimentación
Route::get('/piscicola/alimentacion',
        [PiscicolaController::class, 'alimentacion'])->name('piscicola.alimentacion');
Route::post('/piscicola/alimentacion',
        [PiscicolaController::class, 'storeAlimentacion'])->name('piscicola.alimentacion.store');
 
    // Mortalidad (solo POST — se registra desde otras vistas)
Route::post('/piscicola/mortalidad',
        [PiscicolaController::class, 'storeMortalidad'])->name('piscicola.mortalidad.store');
 
    // Calidad del agua
Route::get('/piscicola/calidad-agua',
        [PiscicolaController::class, 'calidadAgua'])->name('piscicola.calidad_agua');
Route::post('/piscicola/calidad-agua',
        [PiscicolaController::class, 'storeCalidadAgua'])->name('piscicola.calidad_agua.store');
 
    // Cosecha
Route::get('/piscicola/cosecha',
        [PiscicolaController::class, 'cosecha'])->name('piscicola.cosecha');
Route::post('/piscicola/cosecha',
        [PiscicolaController::class, 'storeCosecha'])->name('piscicola.cosecha.store');
 
    // Reportes
Route::get('/piscicola/reportes',
        [PiscicolaController::class, 'reportes'])->name('piscicola.reportes');

   // Dashboard de sesiones diarias
Route::get('/produccion-animal',
        [ProduccionAnimalController::class, 'index'])
        ->name('produccion-animal.index');
 
    // Registrar sesión de producción (leche AM/PM, huevos, etc.)
Route::post('/produccion-animal',
        [ProduccionAnimalController::class, 'store'])
        ->name('produccion-animal.store');
 
    // Análisis de productividad por animal individual
Route::get('/produccion-animal/productividad',
        [ProduccionAnimalController::class, 'productividad'])
        ->name('produccion-animal.productividad');
 
    // Calcular y guardar costos por período
Route::post('/produccion-animal/calcular-costos',
        [ProduccionAnimalController::class, 'calcularCostos'])
        ->name('produccion-animal.calcularCostos');
 
    // Eliminar registro
Route::post('/produccion-animal/{id}/delete',
        [ProduccionAnimalController::class, 'destroy'])
        ->name('produccion-animal.destroy');    
     
Route::get('/reportes',
        [ReportesController::class, 'index'])->name('reportes.index');      
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/inicio', [DashboardController::class, 'index'])->name('inicio');          

});