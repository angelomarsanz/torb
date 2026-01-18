const mix = require('laravel-mix');

// Carga las variables de entorno del archivo .env para usarlas aquí
require('dotenv').config();

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 */

mix.js('resources/js/app.js', 'js')
    .sass('resources/sass/app.scss', 'css')
    .js('resources/js/sign-up-login.js', 'public/js/sign-up-login.js')
    .options({ manifest: false });

// ----------------------------------------------------------------------
// CAMBIO 1: Compilación del paquete RedaAlojamiento (Rutas y Nombres OK)
// ----------------------------------------------------------------------
mix.js(
    // Origen: El archivo JS dentro de la nueva ruta del paquete
    'packages/Reda/RedaAlojamiento/resources/js/main.js', 
    // Destino: El nuevo nombre del archivo JS compilado
    'public/js/reda-alojamiento.js' 
)
   .sass(
    // Origen: El archivo SASS dentro de la nueva ruta del paquete
    'packages/Reda/RedaAlojamiento/resources/sass/main.scss', 
    // Destino: El nuevo nombre del archivo CSS compilado
    'public/css/reda-alojamiento.css'
);
// ----------------------------------------------------------------------

/*
 |--------------------------------------------------------------------------
 | Despliegue automático a Producción (FTP)
 |--------------------------------------------------------------------------
 */
mix.then(() => {
    // Verificamos si estamos en modo producción
    if (mix.inProduction()) {
        console.log('Compilación de producción finalizada. Subiendo archivos a producción...');

        const { execSync } = require('child_process');
        const path = require('path');

        // Lee las credenciales y la ruta del servidor desde el archivo .env
        const ftpUser = process.env.FTP_USER;
        const ftpPassword = process.env.FTP_PASSWORD;
        const ftpHost = process.env.FTP_HOST;
        const remotePathOld = process.env.FTP_REMOTE_PATH_OLD;
        const remotePathJs = process.env.FTP_REMOTE_PATH_JS;
        const remotePathCss = process.env.FTP_REMOTE_PATH_CSS;
        
        // --- Función para respaldar y subir un archivo individual ---
        const uploadPuntual = (localFile, remoteFile, prefix) => {
            const remoteDir = path.dirname(remoteFile);
            const fileName = path.basename(remoteFile);
            const backupFileName = `${prefix}${fileName}`;

            // 1. Respaldar
            try {
                console.log(`  -> Respaldando ${fileName}...`);
                // Uso del comando 'RNFR' (Rename From) y 'RNTO' (Rename To) para el respaldo
                execSync(`curl -u ${ftpUser}:${ftpPassword} -Q "RNFR ${remoteFile}" -Q "RNTO ${remotePathOld}/${backupFileName}" ${ftpHost}`);
                console.log(`  ✅ Respaldo de ${fileName} completado.`);
            } catch (e) {
                console.log(`  ⚠️ No se pudo respaldar ${fileName} (probablemente no existía).`);
            }
            // 2. Subir
            // Usamos la opción '--ftp-create-dirs' para asegurar que el directorio remoto exista
            execSync(`curl --ftp-create-dirs -T ${localFile} -u ${ftpUser}:${ftpPassword} ${ftpHost}${remoteDir}/`);
            console.log(`✅ ${fileName} subido exitosamente.`);
        };

        // --------------------------------------------------------------------------
        // CAMBIO 2: SUBIDA EXPLÍCITA para el módulo REDA Alojamiento
        // --------------------------------------------------------------------------
        console.log('Subiendo archivos del paquete REDA Alojamiento (Nuevos Nombres)...');
        
        try {
            // 1. Subida de JavaScript: reda-alojamiento.js
            uploadPuntual('public/js/reda-alojamiento.js', `${remotePathJs}/reda-alojamiento.js`, 'js_');

            // 2. Subida de CSS: reda-alojamiento.css
            uploadPuntual('public/css/reda-alojamiento.css', `${remotePathCss}/reda-alojamiento.css`, 'css_');

        } catch (error) {
            console.error('❌ Error al subir archivos del módulo REDA:', error.message);
        }

        // --------------------------------------------------------------------------
        // --- MODELO PARA SUBIDAS PUNTUALES (Si necesitas subir otros archivos) ---
        // Se mantiene el bloque original de subidas puntuales (ej. app.js) si fuera necesario.
        console.log('Subiendo archivos de frontend específicos (si están descomentados)...');
        try {
            // Ejemplo para subir app.js
            // uploadPuntual('public/js/app.js', `${remotePathJs}/app.js`, 'js_');

            // Ejemplo para subir app.css
            // uploadPuntual('public/css/app.css', `${remotePathCss}/app.css`, 'css_');

        } catch (error) {
            console.error('❌ Error al subir archivos de frontend específicos:', error.message);
        }
    }
});