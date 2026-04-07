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


// ── Públicas ─────────────────────────────────────────────────
Route::get('/',         [AuthController::class, 'welcome'])->name('welcome');
Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',   [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register',[AuthController::class, 'register'])->name('register.post');
Route::post('/logout',  [AuthController::class, 'logout'])->name('logout');


// Exportaciones
Route::get('/exportar/cultivos/pdf',    [ExportController::class, 'cultivosPdf'])->name('exportar.cultivos.pdf');
Route::get('/exportar/cultivos/excel',  [ExportController::class, 'cultivosExcel'])->name('exportar.cultivos.excel');
Route::get('/exportar/gastos/pdf',      [ExportController::class, 'gastosPdf'])->name('exportar.gastos.pdf');
Route::get('/exportar/gastos/excel',    [ExportController::class, 'gastosExcel'])->name('exportar.gastos.excel');
Route::get('/exportar/cosechas/pdf',    [ExportController::class, 'cosechasPdf'])->name('exportar.cosechas.pdf');
Route::get('/exportar/cosechas/excel',  [ExportController::class, 'cosechasExcel'])->name('exportar.cosechas.excel');
Route::get('/exportar/reporte/pdf',     [ExportController::class, 'reporteGeneralPdf'])->name('exportar.reporte.pdf');

// ── Protegidas (requieren auth) ───────────────────────────────
Route::middleware('auth.session')->group(function () {

Route::get('/cosechas',           [CosechaController::class, 'index'])->name('cosechas.index');
Route::post('/cosechas',          [CosechaController::class, 'store'])->name('cosechas.store');
Route::post('/cosechas/{id}',     [CosechaController::class, 'update'])->name('cosechas.update');
Route::post('/cosechas/{id}/delete', [CosechaController::class, 'destroy'])->name('cosechas.destroy');

    Route::get('/onboarding',        [AuthController::class,  'onboarding'])->name('onboarding');
    Route::post('/onboarding',       [AuthController::class,  'onboardingComplete'])->name('onboarding.complete');

    Route::get('/dashboard',         [DashboardController::class, 'index'])->name('dashboard');

    // Cultivos
    Route::get('/cultivos',          [CultivoController::class, 'index'])->name('cultivos.index');
    Route::post('/cultivos',         [CultivoController::class, 'store'])->name('cultivos.store');
    Route::post('/cultivos/{id}',    [CultivoController::class, 'update'])->name('cultivos.update');
    Route::post('/cultivos/{id}/delete', [CultivoController::class, 'destroy'])->name('cultivos.destroy');

    // Gastos
    Route::get('/gastos',            [GastoController::class, 'index'])->name('gastos.index');
    Route::post('/gastos',           [GastoController::class, 'store'])->name('gastos.store');
    Route::post('/gastos/{id}',      [GastoController::class, 'update'])->name('gastos.update');
    Route::post('/gastos/{id}/delete', [GastoController::class, 'destroy'])->name('gastos.destroy');

    // Ingresos
    Route::get('/ingresos',          [IngresoController::class, 'index'])->name('ingresos.index');
    Route::post('/ingresos',         [IngresoController::class, 'store'])->name('ingresos.store');
    Route::post('/ingresos/{id}',    [IngresoController::class, 'update'])->name('ingresos.update');
    Route::post('/ingresos/{id}/delete', [IngresoController::class, 'destroy'])->name('ingresos.destroy');

    // Animales
    Route::get('/animales',          [AnimalController::class, 'index'])->name('animales.index');
    Route::post('/animales',         [AnimalController::class, 'store'])->name('animales.store');
    Route::post('/animales/{id}',    [AnimalController::class, 'update'])->name('animales.update');
    Route::post('/animales/{id}/delete', [AnimalController::class, 'destroy'])->name('animales.destroy');

    // Tareas / Calendario
    Route::get('/calendario',        [TareaController::class, 'index'])->name('calendario.index');
    Route::post('/tareas',           [TareaController::class, 'store'])->name('tareas.store');
    Route::post('/tareas/{id}',      [TareaController::class, 'update'])->name('tareas.update');
    Route::post('/tareas/{id}/completar', [TareaController::class, 'completar'])->name('tareas.completar');
    Route::post('/tareas/{id}/delete',    [TareaController::class, 'destroy'])->name('tareas.destroy');

    // Reportes
    Route::get('/reportes',          [ReporteController::class, 'index'])->name('reportes.index');

    // Perfil
    Route::get('/perfil',            [PerfilController::class, 'index'])->name('perfil.index');
    Route::post('/perfil',           [PerfilController::class, 'update'])->name('perfil.update');
    Route::post('/perfil/password',  [PerfilController::class, 'changePassword'])->name('perfil.password');
});
