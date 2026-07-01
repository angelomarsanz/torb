const mix = require('laravel-mix');

// Variables de entorno para control selectivo
const buildApp = process.env.BUILD_APP === 'true';
const buildAlojamiento = process.env.BUILD_ALOJAMIENTO === 'true';

/*
 |--------------------------------------------------------------------------
 | Compilación Selectiva
 |--------------------------------------------------------------------------
 */

// Módulo Principal (App)
if (buildApp) {
    console.log('🏗️ Compilando: APP Principal (JS/SASS)');
    mix.js('resources/js/app.js', 'public/js')
       .sass('resources/sass/app.scss', 'public/css')
       .js('resources/js/sign-up-login.js', 'public/js/sign-up-login.js');
}

// Módulo RedaAlojamiento
if (buildAlojamiento) {
    console.log('🏗️ Compilando: REDA Alojamiento');
    // Css Admin
    mix.sass('packages/Reda/RedaAlojamiento/resources/sass/admin/main.scss', 'public/css/reda/admin/general/reda-admin-general-main.min.css');

    // Css general
    mix.sass('packages/Reda/RedaAlojamiento/resources/sass/main.scss', 'public/css/reda/reda-general-main.min.css');

    // Js Admin para uso general

    mix.js('packages/Reda/RedaAlojamiento/resources/js/admin/general/main.js', 'public/js/reda/admin/general/reda-admin-general-main.min.js');

    // Js Admin por vistas

    mix.js('packages/Reda/RedaAlojamiento/resources/js/admin/vistas/experiencia/opcionesTipoDeNegocios.js',
        'public/js/reda/admin/vistas/experiencia/opcionesTipoDeNegocios.min.js');

    mix.js('packages/Reda/RedaAlojamiento/resources/js/admin/vistas/experiencia/configuracionPlanes.js',
        'public/js/reda/admin/vistas/experiencia/configuracionPlanes.min.js');

    // Js para uso general
    mix.js('packages/Reda/RedaAlojamiento/resources/js/general/main.js', 'public/js/reda/general/reda-general-main.min.js');

    mix.js('packages/Reda/RedaAlojamiento/resources/js/general/notificaciones.js', 'public/js/reda/general/notificaciones.min.js');

    mix.js('packages/Reda/RedaAlojamiento/resources/js/general/media.js', 'public/js/reda/general/reda-general-media.min.js');

    mix.js('packages/Reda/RedaAlojamiento/resources/js/admin/general/soporte_tecnico/indexSoporteTecnico.js', 'public/js/reda/admin/general/soporte_tecnico/indexSoporteTecnico.min.js');
    mix.js('packages/Reda/RedaAlojamiento/resources/js/admin/general/soporte_tecnico/showSoporteTecnico.js', 'public/js/reda/admin/general/soporte_tecnico/showSoporteTecnico.min.js');

    // Js por vistas
    mix.js('packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/createExperiencias.js',
        'public/js/reda/vistas/experiencia/createExperiencias.min.js');

    mix.js('packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/formularioDePasosExperiencias.js',
        'public/js/reda/vistas/experiencia/formularioDePasosExperiencias.min.js');

    mix.js('packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/indexExperiencias.js',
        'public/js/reda/vistas/experiencia/indexExperiencias.min.js');

    mix.js('packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/frontend/listadoExperiencias.js',
        'public/js/reda/vistas/experiencia/frontend/listadoExperiencias.min.js');

    mix.js('packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/frontend/listadoProductosServicios.js',
        'public/js/reda/vistas/experiencia/frontend/listadoProductosServicios.min.js');

    mix.js('packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/frontend/productosServiciosEncontrados.js',
        'public/js/reda/vistas/experiencia/frontend/productosServiciosEncontrados.min.js');

    mix.js('packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/calificacionExperiencia.js',
        'public/js/reda/vistas/experiencia/calificacionExperiencia.min.js');

    mix.js('packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/frontend/calificacionExperienciaFrontend.js', 'public/js/reda/vistas/experiencia/frontend/calificacionExperienciaFrontend.min.js');

    mix.js('packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/detalleCalificaciones.js',
        'public/js/reda/vistas/experiencia/detalleCalificaciones.min.js');

    mix.js('packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/listadoCalificaciones.js',
        'public/js/reda/vistas/experiencia/listadoCalificaciones.min.js');
    }


mix.options({
    manifest: false,
    processCssUrls: false
});
