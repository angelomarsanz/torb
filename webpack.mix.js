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
    mix.js('packages/Reda/RedaAlojamiento/resources/js/main.js', 'public/js/reda-alojamiento.js')
       .sass('packages/Reda/RedaAlojamiento/resources/sass/main.scss', 'public/css/reda-alojamiento.css');
}

mix.options({ 
    manifest: false,
    processCssUrls: false 
});