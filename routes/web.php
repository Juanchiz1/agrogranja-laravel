<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
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
use App\Http\Controllers\CultivoFaseController;


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
});