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
    // Css general
    mix.sass('packages/Reda/RedaAlojamiento/resources/sass/main.scss', 'public/css/reda/reda-general-main.min.css');

    // Js para uso general
    mix.js('packages/Reda/RedaAlojamiento/resources/js/general/main.js', 'public/js/reda/general/reda-general-main.min.js');

    mix.js('packages/Reda/RedaAlojamiento/resources/js/general/media.js', 'public/js/reda/general/reda-general-media.min.js');

    // Js por vistas
    mix.js('packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/createExperiencias.js', 
        'public/js/reda/vistas/experiencia/createExperiencias.min.js');

    mix.js('packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/formularioDePasosExperiencias.js', 
        'public/js/reda/vistas/experiencia/formularioDePasoExperiencias.min.js');
}

mix.options({ 
    manifest: false,
    processCssUrls: false 
});