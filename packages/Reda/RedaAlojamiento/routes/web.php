<?php

use Illuminate\Support\Facades\Route;
use Reda\RedaAlojamiento\Http\Controllers\General\RedaInboxController;
use Reda\RedaAlojamiento\Http\Controllers\General\RedaPaymentController;

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
use Reda\RedaAlojamiento\Http\Controllers\Admin\Disputa\DisputaController as AdminDisputaController;

// Controladores de Experiencia
use Reda\RedaAlojamiento\Http\Controllers\Experiencia\ExperienciaController;
use Reda\RedaAlojamiento\Http\Controllers\Experiencia\ActividadExperienciaController;
use Reda\RedaAlojamiento\Http\Controllers\Experiencia\HorarioExperienciaController;
use Reda\RedaAlojamiento\Http\Controllers\Experiencia\InformacionExperienciaController;
use Reda\RedaAlojamiento\Http\Controllers\Experiencia\ReservacionExperienciaController;
use Reda\RedaAlojamiento\Http\Controllers\Experiencia\AnfitrionExperienciaController;

/*
|--------------------------------------------------------------------------
| Assets del Plugin
|--------------------------------------------------------------------------
*/
Route::get('reda/assets/chat-injection.js', function() {
    $path = __DIR__.'/../resources/js/chat-injection.js';
    if (!file_exists($path)) abort(404);
    return response(file_get_contents($path), 200, ['Content-Type' => 'application/javascript']);
});

/*
|--------------------------------------------------------------------------
| Rutas Web del Paquete RedaAlojamiento
|--------------------------------------------------------------------------
|
| Estas rutas son cargadas por el Service Provider del paquete.
| Se agrupan bajo el prefijo 'reda' para mantener la estructura de URL original.
|
*/

// Sobrescribir Inbox original con la lógica de agrupación por participantes
Route::group(['middleware' => ['web', 'locale', 'auth']], function () {
    Route::match(['get', 'post'], 'inbox', [RedaInboxController::class, 'index'])->name('inbox');
    
    // Rutas exclusivas REDA para AJAX del Inbox
    Route::post('reda/messaging/booking', [RedaInboxController::class, 'message']);
    Route::post('reda/messaging/reply', [RedaInboxController::class, 'messageReply']);

    // Mantenemos las originales por si algún otro script las usa, 
    // pero nuestro nuevo JS usará las de arriba.
    Route::post('messaging/booking', [RedaInboxController::class, 'message']);
    Route::post('messaging/reply', [RedaInboxController::class, 'messageReply']);
});

// ----------------------------------------------------------------------
// RUTAS DE ADMINISTRACIÓN (PLUGIN REDA ALOJAMIENTO)
// ----------------------------------------------------------------------
// Grupo principal para el administrador: fusiona el prefijo 'admin/reda'
// y protege el acceso mediante el middleware 'admin' del proyecto original.
Route::group(['prefix' => 'admin/reda', 'middleware' => ['web', 'guest:admin']], function () {

    // Subgrupo para Disputas (Admin)
    Route::prefix('disputas')->as('reda.admin.disputas.')->group(function () {
        Route::controller(AdminDisputaController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('get-listado', 'obtenerDisputasPaginadas')->name('paginadas');
            Route::get('get-detail-modal/{id}', 'getDetailModal')->name('get_detail_modal');
        });

        // Subgrupo para Mensajes de Disputas (Admin)
        Route::prefix('mensajes')->as('mensajes.')->group(function () {
            Route::controller(\Reda\RedaAlojamiento\Http\Controllers\Admin\Disputa\MensajeController::class)->group(function () {
                Route::get('{booking_id}', 'getMessages')->name('get');
                Route::post('store', 'store')->name('store');
            });
        });
    });

    // Subgrupo para Negocios (Admin)
    Route::prefix('negocios')->as('reda.admin.negocios.')->group(function () {
        Route::controller(AdminExperienciaController::class)->group(function () {

            // Ruta para listar y gestionar las Opciones de Tipos de Negocios
            // URL resultante: tu-dominio.com/admin/reda/negocios/opciones-tipos-de-negocios
            Route::match(['GET', 'POST'], 'opciones-tipos-de-negocios', 'opcionesTiposDeNegocios')
                ->name('opciones_tipos_de_negocios');

            // Ruta para la configuración de Planes de Negocios
            // URL resultante: tu-dominio.com/admin/reda/negocios/configuracion-planes
            Route::get('configuracion-planes', 'configuracionPlanes')->name('configuracion_planes');

            // Ruta para guardar la configuración general de planes vía Ajax
            Route::post('configuracion-planes/store', 'storeConfiguracionPlanes')->name('configuracion_planes.store');

            // Rutas para la gestión de Planes de Negocios
            Route::get('configuracion-planes/listado', 'indexPlanes')->name('configuracion_planes.index_planes');
            Route::get('configuracion-planes/get/{id}', 'getPlan')->name('configuracion_planes.get_plan');
            Route::post('configuracion-planes/store-plan', 'storePlan')->name('configuracion_planes.store_plan');
            Route::post('configuracion-planes/update-plan', 'updatePlan')->name('configuracion_planes.update_plan');
            Route::delete('configuracion-planes/destroy-plan/{id}', 'destroyPlan')->name('configuracion_planes.destroy_plan');

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

    // Subgrupo para General (Admin)
    Route::prefix('general')->as('reda.admin.general.')->group(function () {
        Route::controller(\Reda\RedaAlojamiento\Http\Controllers\Admin\General\SoporteTecnicoController::class)->group(function () {
            // Ruta para el index de Soporte Técnico
            Route::get('soporte-tecnico', 'index')->name('soporte_tecnico.index');
            // Ruta para ver el detalle de un ticket
            Route::get('soporte-tecnico/ver/{id}', 'show')->name('soporte_tecnico.show');
            // Ruta para cerrar un ticket manualmente (ej: Mantener reseña)
            Route::post('soporte-tecnico/cerrar/{id}', 'cerrarTicket')->name('soporte_tecnico.cerrar');
        });

        // Ruta para eliminar una calificación desde el admin
        Route::delete('eliminar-calificacion/{id}', [\Reda\RedaAlojamiento\Http\Controllers\Experiencia\CalificacionController::class, 'destroy'])
            ->name('soporte_tecnico.eliminar_calificacion');
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

    // Pago / Reserva (Sobrescritura para evitar pérdida de datos pre-login)
    Route::match(['get', 'post'], 'payments/book/{id?}', [RedaPaymentController::class, 'index']);

    // Ruta para redirección de reserva con login
    Route::get('auth-reserve/{slug}', [RedaPaymentController::class, 'redirectReservar'])->name('reda.auth_reserve');


    // ----------------------------------------------------------------------
    // 2. Rutas de Negocios Sin Login Requerido
    // ----------------------------------------------------------------------
    Route::prefix('negocios')->as('reda.negocios.')->group(function () {

        // Experiencia - Módulos principales y sub-módulos
        Route::get('actividades-experiencias', [ActividadExperienciaController::class, 'index'])->name('actividades_experiencias.index');
        Route::get('anfitrion-experiencias', [AnfitrionExperienciaController::class, 'index'])->name('anfitriones_experiencias.index');
        Route::get('experiencias', [ExperienciaController::class, 'index'])->name('experiencias.index');
        Route::get('listado-negocios', [ExperienciaController::class, 'listadoFrontend'])->name('experiencias.listado_frontend');
        Route::get('listado-negocios/paginados', [ExperienciaController::class, 'obtenerNegociosPaginados'])->name('experiencias.listado_paginado');
        Route::get('listado-productos-servicios/{id}/{actividad_id?}', [ExperienciaController::class, 'listadoProductosServicios'])->name('experiencias.listado_productos_servicios');
        Route::get('experiencias/actividades/paginadas/{id}', [ExperienciaController::class, 'obtenerActividadesPaginadas'])->name('experiencias.actividades.paginadas');
        Route::get('experiencias/actividades/detalle/{id}', [ExperienciaController::class, 'getActividadDetalle'])->name('experiencias.actividades.detalle');
        Route::get('productos-servicios-encontrados', [ExperienciaController::class, 'productosServiciosEncontrados'])->name('experiencias.productos_servicios_encontrados');
        Route::get('horarios-experiencias', [HorarioExperienciaController::class, 'index'])->name('horarios_experiencias.index');
        Route::get('informacion-experiencias', [InformacionExperienciaController::class, 'index'])->name('informaciones_experiencias.index');
        Route::get('reservacion-experiencias', [ReservacionExperienciaController::class, 'index'])->name('reservaciones_experiencias.index');

    });

    // ----------------------------------------------------------------------
    // 3. Rutas que requieren login de usuario
    // ----------------------------------------------------------------------
    Route::group(['middleware' => ['reda.auth']], function () {

        // Disputas
        Route::get('disputas/paginadas', [DisputaController::class, 'obtenerDisputasPaginadas'])->name('reda.disputas.paginadas');
        Route::get('disputas/get-modal', [DisputaController::class, 'getModal'])->name('reda.disputas.get_modal');
        Route::get('disputas/get-detail-modal/{id}', [DisputaController::class, 'getDetailModal'])->name('reda.disputas.get_detail_modal');
        Route::get('disputas/check/{booking_id}', [DisputaController::class, 'checkDispute'])->name('reda.disputas.check');
        Route::get('disputas/ver/{id}', [DisputaController::class, 'show'])->name('reda.disputas.show');
        Route::post('disputas/store', [DisputaController::class, 'store'])->name('reda.disputas.store');

        // Subgrupo para Mensajes de Disputas (Frontend)
        Route::prefix('disputas/mensajes')->as('reda.disputas.mensajes.')->group(function () {
            Route::controller(\Reda\RedaAlojamiento\Http\Controllers\Disputa\MensajeController::class)->group(function () {
                Route::get('{booking_id}', 'getMessages')->name('get');
                Route::post('store', 'store')->name('store');
            });
        });

        // Media (Se mantienen sin el prefijo 'negocios' en URL y nombre)
        Route::post('upload-photo/{id}', [MediaController::class, 'uploadPhoto'])->name('reda.upload_photo');
        Route::post('delete-photo', [MediaController::class, 'deletePhoto'])->name('reda.delete_photo');
        Route::post('make-default-photo', [MediaController::class, 'makeDefaultPhoto'])->name('reda.make_default_photo');
        Route::post('crop-photo', [MediaController::class, 'cropPhoto'])->name('reda.crop_photo');

        // Chat
        Route::get('pago/iniciar-chat/{property_id}', [\Reda\RedaAlojamiento\Http\Controllers\General\ChatController::class, 'iniciarChat'])->name('reda.chat.iniciar');

        // Rutas de Negocios (Con login)
        Route::prefix('negocios')->as('reda.negocios.')->group(function () {
            // Soporte Técnico
            Route::post('soporte-tecnico/store', [\Reda\RedaAlojamiento\Http\Controllers\General\SoporteTecnicoController::class, 'store'])
                ->name('soporte_tecnico.store');

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

            Route::get('mis-calificaciones/get-nombres-comercios', [\Reda\RedaAlojamiento\Http\Controllers\Experiencia\CalificacionController::class, 'getNombresComercios'])
                ->name('experiencias.get_nombres_comercios');

            Route::get('mis-calificaciones/detalle/{id}', [\Reda\RedaAlojamiento\Http\Controllers\Experiencia\CalificacionController::class, 'detalleCalificacionesDuenio'])
                ->name('experiencias.detalle_calificaciones');

            // Favoritos
            Route::post('experiencias/toggle-favorito/{id}', [\Reda\RedaAlojamiento\Http\Controllers\Experiencia\FavoritoController::class, 'toggleFavoritoComercio'])
                ->name('experiencias.toggle_favorito');
            Route::get('experiencias/favoritos', [\Reda\RedaAlojamiento\Http\Controllers\Experiencia\FavoritoController::class, 'getFavoritosComercios'])
                ->name('experiencias.get_favoritos');

            Route::delete('experiencias/eliminar-experiencia/{id}', [ExperienciaController::class, 'destroy'])->name('experiencias.destroy');
        });
    });
});
