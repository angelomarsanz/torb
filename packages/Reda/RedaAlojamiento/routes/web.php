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

    // Subgrupo para Negocios (Admin)
    Route::prefix('negocios')->as('reda.admin.negocios.')->group(function () {
        Route::controller(AdminExperienciaController::class)->group(function () {

            // Ruta para listar y gestionar las Opciones de Tipos de Negocios
            // URL resultante: tu-dominio.com/admin/reda/negocios/opciones-tipos-de-negocios
            Route::match(['GET', 'POST'], 'opciones-tipos-de-negocios', 'opcionesTiposDeNegocios')
                ->name('opciones_tipos_de_negocios');

            // Ruta para guardar una nueva categoría vía Ajax
            Route::post('opciones-tipos-de-negocios/store', 'storeOpcionTipoNegocio')
                ->name('opciones_tipos_de_negocios.store');

            // Ruta para actualizar una categoría vía Ajax
            Route::post('opciones-tipos-de-negocios/update', 'updateOpcionTipoNegocio')
                ->name('opciones_tipos_de_negocios.update');

            // Ruta para eliminar una categoría vía Ajax
            Route::delete('opciones-tipos-de-negocios/destroy/{clave}', 'destroyOpcionTipoNegocio')
                ->name('opciones_tipos_de_negocios.destroy');

        });
    });

});

Route::prefix('reda')->middleware(['web', 'locale'])->group(function () {

    // ----------------------------------------------------------------------
    // 1. Rutas Sin Prefijo 'negocios' (Administrativos, Billetera, Disputas)
    // ----------------------------------------------------------------------

    // Administrativo
    Route::get('administrativos', [AdministrativoController::class, 'index'])->name('reda.administrativos.index');

    // Billetera
    Route::get('billetera-huespedes', [BilleteraHuespedController::class, 'index'])->name('reda.billeteras_huespedes.index');

    // Disputa
    Route::get('disputas', [DisputaController::class, 'index'])->name('reda.disputas.index');


    // ----------------------------------------------------------------------
    // 2. Rutas de Negocios Sin Login Requerido
    // ----------------------------------------------------------------------
    Route::prefix('negocios')->as('reda.negocios.')->group(function () {

        // Experiencia - Módulos principales y sub-módulos
        Route::get('actividades-experiencias', [ActividadExperienciaController::class, 'index'])->name('actividades_experiencias.index');
        Route::get('anfitrion-experiencias', [AnfitrionExperienciaController::class, 'index'])->name('anfitriones_experiencias.index');
        Route::get('experiencias', [ExperienciaController::class, 'index'])->name('experiencias.index');
        Route::get('listado-negocios', [ExperienciaController::class, 'listadoFrontend'])->name('experiencias.listado_frontend');
        Route::get('listado-productos-servicios/{id}', [ExperienciaController::class, 'listadoProductosServicios'])->name('experiencias.listado_productos_servicios');
        Route::get('experiencias/actividades/paginadas/{id}', [ExperienciaController::class, 'obtenerActividadesPaginadas'])->name('experiencias.actividades.paginadas');
        Route::get('experiencias/actividades/detalle/{id}', [ExperienciaController::class, 'getActividadDetalle'])->name('experiencias.actividades.detalle');
        Route::get('horarios-experiencias', [HorarioExperienciaController::class, 'index'])->name('horarios_experiencias.index');
        Route::get('informacion-experiencias', [InformacionExperienciaController::class, 'index'])->name('informaciones_experiencias.index');
        Route::get('reservacion-experiencias', [ReservacionExperienciaController::class, 'index'])->name('reservaciones_experiencias.index');

    });

    // ----------------------------------------------------------------------
    // 3. Rutas que requieren login de usuario
    // ----------------------------------------------------------------------
    Route::group(['middleware' => ['reda.auth']], function () {

        // Media (Se mantienen sin el prefijo 'negocios' en URL y nombre)
        Route::post('upload-photo/{id}', [MediaController::class, 'uploadPhoto'])->name('reda.upload_photo');
        Route::post('delete-photo', [MediaController::class, 'deletePhoto'])->name('reda.delete_photo');
        Route::post('make-default-photo', [MediaController::class, 'makeDefaultPhoto'])->name('reda.make_default_photo');
        Route::post('crop-photo', [MediaController::class, 'cropPhoto'])->name('reda.crop_photo');

        // Rutas de Negocios (Con login)
        Route::prefix('negocios')->as('reda.negocios.')->group(function () {
            Route::get('index-experiencias', [ExperienciaController::class, 'index'])->name('experiencias.index');

            Route::match(['GET', 'POST'], 'crear-experiencia', [ExperienciaController::class, 'create'])
                ->name('experiencias.create');

            Route::match(['GET', 'POST'], 'formulario-de-pasos-experiencias/{id}/{paso}', [ExperienciaController::class, 'formularioDePasosExperiencias'])
                ->name('experiencias.pasos')
                ->where(['paso' => 'descripcion|fotos|actividades|ubicacion|horario|anfitrion|informacion_adicional|precio']);

            Route::post('experiencias/{id}/agregar-actividad', [ExperienciaController::class, 'agregarActividad'])->name('experiencias.actividades.add');

            Route::get('experiencias/actividades/get-form/{id}', [ExperienciaController::class, 'getActividadForm'])->name('experiencias.actividades.get_form');

            Route::post('experiencias/actividades/reordenar', [ExperienciaController::class, 'reordenarActividades'])->name('experiencias.actividades.reordenar');
            Route::post('experiencias/actividades/actualizar-precios-lote', [ExperienciaController::class, 'actualizarPreciosLote'])->name('experiencias.actividades.actualizar_precios_lote');
            Route::delete('experiencias/actividades/delete/{id}', [ExperienciaController::class, 'deleteActividad'])->name('experiencias.actividades.delete');

            // Rutas para Horarios (JSON en tabla experiencias)
            Route::post('experiencias/{id}/guardar-horario', [ExperienciaController::class, 'guardarHorario'])->name('experiencias.horario.guardar');
            Route::delete('experiencias/{id}/eliminar-horario/{index}', [ExperienciaController::class, 'eliminarHorario'])->name('experiencias.horario.eliminar');

            // Rutas para Calificaciones
            Route::get('calificar/{id}', [\Reda\RedaAlojamiento\Http\Controllers\Experiencia\CalificacionController::class, 'calificacionExperienciaFrontend'])
                ->name('experiencias.calificar');
            Route::post('guardar-calificacion', [\Reda\RedaAlojamiento\Http\Controllers\Experiencia\CalificacionController::class, 'guardarCalificacion'])
                ->name('experiencias.guardar_calificacion');

            // Rutas para Gestión de QR
            Route::get('mis-calificaciones/qr', [\Reda\RedaAlojamiento\Http\Controllers\Experiencia\CalificacionController::class, 'indexQR'])
                ->name('experiencias.qr_index');
            Route::get('mis-calificaciones/descargar-cartel/{id}', [\Reda\RedaAlojamiento\Http\Controllers\Experiencia\CalificacionController::class, 'descargarCartel'])
                ->name('experiencias.descargar_cartel');

            // Nueva Ruta: Listado de Calificaciones para el dueño
            Route::get('mis-calificaciones/listado', [\Reda\RedaAlojamiento\Http\Controllers\Experiencia\CalificacionController::class, 'listadoDuenio'])
                ->name('experiencias.calificaciones_listado');

            Route::delete('experiencias/eliminar-experiencia/{id}', [ExperienciaController::class, 'destroy'])->name('experiencias.destroy');
        });
    });
});
