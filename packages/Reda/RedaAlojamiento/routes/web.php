<?php

use Illuminate\Support\Facades\Route;

// ----------------------------------------------------------------------
// IMPORTACIÓN DE CONTROLADORES
// Se importan todos los controladores del paquete con su FQCN
// ----------------------------------------------------------------------
use Reda\RedaAlojamiento\Http\Controllers\Administrativo\AdministrativoController;
use Reda\RedaAlojamiento\Http\Controllers\BilleteraHuesped\BilleteraHuespedController;
use Reda\RedaAlojamiento\Http\Controllers\Disputa\DisputaController;

// Controladores de Experiencia
use Reda\RedaAlojamiento\Http\Controllers\Experiencia\ExperienciaController;
use Reda\RedaAlojamiento\Http\Controllers\Experiencia\ActividadExperienciaController;
use Reda\RedaAlojamiento\Http\Controllers\Experiencia\HorarioExperienciaController;
use Reda\RedaAlojamiento\Http\Controllers\Experiencia\InformacionExperienciaController;
use Reda\RedaAlojamiento\Http\Controllers\Experiencia\ReservacionExperienciaController;
use Reda\RedaAlojamiento\Http\Controllers\Experiencia\AnfitrionExperienciaController;


/*
|--------------------------------------------------------------------------
| Rutas Web del Paquete RedaAlojamiento
|--------------------------------------------------------------------------
|
| Estas rutas son cargadas por el Service Provider del paquete. 
| Se agrupan bajo el prefijo 'reda' para mantener la estructura de URL original.
|
*/

Route::prefix('reda')->group(function () {

    // ----------------------------------------------------------------------
    // 1. Rutas Sin Login Requerido
    // ----------------------------------------------------------------------

    // Administrativo
    Route::get('administrativos', [AdministrativoController::class, 'index'])->name('reda.administrativos.index');
    
    // Billetera
    Route::get('billetera-huespedes', [BilleteraHuespedController::class, 'index'])->name('reda.billeteras_huespedes.index');
    
    // Disputa
    Route::get('disputas', [DisputaController::class, 'index'])->name('reda.disputas.index');
    
    // Experiencia - Módulos principales y sub-módulos
    Route::get('actividades-experiencias', [ActividadExperienciaController::class, 'index'])->name('reda.actividades_experiencias.index');
    Route::get('anfitrion-experiencias', [AnfitrionExperienciaController::class, 'index'])->name('reda.anfitriones_experiencias.index');
    Route::get('experiencias', [ExperienciaController::class, 'index'])->name('reda.experiencias.index');
    Route::get('horarios-experiencias', [HorarioExperienciaController::class, 'index'])->name('reda.horarios_experiencias.index');
    Route::get('informacion-experiencias', [InformacionExperienciaController::class, 'index'])->name('reda.informaciones_experiencias.index');
    Route::get('reservacion-experiencias', [ReservacionExperienciaController::class, 'index'])->name('reda.reservaciones_experiencias.index');

    // ----------------------------------------------------------------------
    // 2. Rutas que requieren login de usuario (Grupo original con middleware)
    // ----------------------------------------------------------------------
    Route::group(['middleware' => ['web', 'reda.auth', 'locale']], function () {
        Route::get('crear-experiencia', [ExperienciaController::class, 'create'])->name('reda.experiencias.create');

        Route::match(['GET', 'POST'], 'formulario-de-pasos/{id}/{paso}', [ExperienciaController::class, 'formularioDePasos'])
        ->name('reda.experiencias.pasos')
        ->where(['id' => '[0-6]+', 'paso' => 'experiencia|fotos|ubicacion|actividad|horario|informacion|anfitrion']);
    }); 
});