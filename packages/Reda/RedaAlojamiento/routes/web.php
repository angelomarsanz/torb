<?php

use Illuminate\Support\Facades\Route;

// ----------------------------------------------------------------------
// IMPORTACIÓN DE CONTROLADORES
// Se importan todos los controladores del paquete con su FQCN
// ----------------------------------------------------------------------
use Reda\RedaAlojamiento\Http\Controllers\General\MediaController;
use Reda\RedaAlojamiento\Http\Controllers\Administrativo\AdministrativoController;
use Reda\RedaAlojamiento\Http\Controllers\BilleteraHuesped\BilleteraHuespedController;
use Reda\RedaAlojamiento\Http\Controllers\Disputa\DisputaController;

// Controladores del admin
use Reda\RedaAlojamiento\Http\Controllers\Admin\Experiencia\ExperienciaController as AdminExperienciaController;

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

// ----------------------------------------------------------------------
// RUTAS DE ADMINISTRACIÓN (PLUGIN REDA ALOJAMIENTO)
// ----------------------------------------------------------------------
// Grupo principal para el administrador: fusiona el prefijo 'admin/reda'
// y protege el acceso mediante el middleware 'admin' del proyecto original.
Route::group(['prefix' => 'admin/reda', 'middleware' => ['web', 'guest:admin']], function () {

    // Subgrupo exclusivo para ExperienciaController
    // Agrupa todas las rutas que apunten a este controlador usando la sintaxis de Laravel 8+
    Route::controller(AdminExperienciaController::class)->group(function () {

        // Ruta para listar y gestionar las Opciones de Tipos de Negocios
        // URL resultante: tu-dominio.com/admin/reda/opciones-tipos-de-negocios
        Route::match(['GET', 'POST'], 'opciones-tipos-de-negocios', 'opcionesTiposDeNegocios')
            ->name('reda.admin.opciones_tipos_de_negocios');

        // Ruta para guardar una nueva categoría vía Ajax
        Route::post('opciones-tipos-de-negocios/store', 'storeOpcionTipoNegocio')
            ->name('reda.admin.opciones_tipos_de_negocios.store');

        // Ruta para actualizar una categoría vía Ajax
        Route::post('opciones-tipos-de-negocios/update', 'updateOpcionTipoNegocio')
            ->name('reda.admin.opciones_tipos_de_negocios.update');

        // Ruta para eliminar una categoría vía Ajax
        Route::delete('opciones-tipos-de-negocios/destroy/{clave}', 'destroyOpcionTipoNegocio')
            ->name('reda.admin.opciones_tipos_de_negocios.destroy');

        // Aquí podrás añadir fácilmente las próximas rutas para este controlador en el futuro, por ejemplo:
        // Route::get('experiencias/listado', 'metodoListado')->name('reda.admin.experiencias.listado');
        // Route::post('experiencias/guardar', 'metodoGuardar')->name('reda.admin.experiencias.guardar');

    });

    // (Opcional) Si en el futuro creas otro controlador administrativo para el plugin, lo agrupas aquí abajo:
    // Route::controller(OtroAdminController::class)->group(function () { ... });

});

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
        Route::get('index-experiencias', [ExperienciaController::class, 'index'])->name('reda.experiencias.index');

        Route::match(['GET', 'POST'], 'crear-experiencia', [ExperienciaController::class, 'create'])
        ->name('reda.experiencias.create');

        Route::match(['GET', 'POST'], 'formulario-de-pasos-experiencias/{id}/{paso}', [ExperienciaController::class, 'formularioDePasosExperiencias'])
        ->name('reda.experiencias.pasos')
        ->where(['paso' => 'descripcion|fotos|actividades|ubicacion|horario|precio|informacion_adicional|anfitrion']);

        Route::post('upload-photo/{id}', [MediaController::class, 'uploadPhoto'])
        ->name('reda.upload_photo');

        Route::post('delete-photo', [MediaController::class, 'deletePhoto'])
        ->name('reda.delete_photo');

        Route::post('make-default-photo', [MediaController::class, 'makeDefaultPhoto'])
        ->name('reda.make_default_photo');

        Route::post('crop-photo', [MediaController::class, 'cropPhoto'])
        ->name('reda.crop_photo');

        Route::post('experiencias/{id}/agregar-actividad', [ExperienciaController::class, 'agregarActividad'])->name('reda.experiencias.actividades.add');

        Route::get('experiencias/actividades/get-form/{id}', [ExperienciaController::class, 'getActividadForm'])->name('reda.experiencias.actividades.get_form');

        Route::post('experiencias/actividades/reordenar', [ExperienciaController::class, 'reordenarActividades'])->name('reda.experiencias.actividades.reordenar');
        Route::post('experiencias/actividades/actualizar-precios-lote', [ExperienciaController::class, 'actualizarPreciosLote'])->name('reda.experiencias.actividades.actualizar_precios_lote');
        Route::delete('experiencias/actividades/delete/{id}', [ExperienciaController::class, 'deleteActividad'])->name('reda.experiencias.actividades.delete');

        // Rutas para Horarios (JSON en tabla experiencias)
        Route::post('experiencias/{id}/guardar-horario', [ExperienciaController::class, 'guardarHorario'])->name('reda.experiencias.horario.guardar');
        Route::delete('experiencias/{id}/eliminar-horario/{index}', [ExperienciaController::class, 'eliminarHorario'])->name('reda.experiencias.horario.eliminar');

        Route::delete('experiencias/eliminar-experiencia/{id}', [ExperienciaController::class, 'destroy'])->name('reda.experiencias.destroy');
    });
});
