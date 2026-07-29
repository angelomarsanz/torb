<?php

// CAMBIO 1: Nuevo namespace del paquete
namespace Reda\RedaAlojamiento;

use Illuminate\Support\ServiceProvider;

// CAMBIO 2: Nuevo nombre de la clase
class RedaAlojamientoServiceProvider extends ServiceProvider
{
    /**
     * Registra bindings en el contenedor.
     */
    public function register(): void
    {
        // Cargamos los helpers personalizados del paquete
        $helpersFile = __DIR__.'/Helpers/helpers.php';
        if (file_exists($helpersFile)) {
            require_once $helpersFile;
        }

        // CAMBIO 3: Nueva referencia al archivo de configuración
        $this->mergeConfigFrom(
            __DIR__.'/../config/reda-alojamiento.php', 'reda-alojamiento'
        );
    }

    /**
     * Bootstrap (arranque) de los servicios de la aplicación.
     */
    public function boot(): void
    {
        // Registrar observadores
        \App\Models\Messages::observe(\Reda\RedaAlojamiento\Observers\MensajeObserver::class);

        $router = $this->app['router'];

        // Esto registra tu middleware con una prioridad alta
        $router->aliasMiddleware('reda.auth', \Reda\RedaAlojamiento\Http\Middleware\CheckPluginAuth::class);

        // Carga las rutas
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Carga las vistas con el nuevo namespace 'reda-alojamiento-js'
        // Esto permite referencias como: 'reda-alojamiento-js::experiencia.index'
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'reda-alojamiento');

        // Colocamos el alias 'pasos' apuntando a la carpeta específica de los formularios
        $this->loadViewsFrom(__DIR__.'/../resources/views/experiencia/experiencias/formularios_de_pasos', 'pasos');

        // Carga las migraciones
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // PUBLICACIÓN DE CONFIGURACIÓN
        $this->publishes([
            __DIR__.'/../config/reda-alojamiento.php' => config_path('reda-alojamiento.php'),
        // Nueva etiqueta
        ], 'reda-alojamiento-config');

        // Traducciones
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'reda-alojamiento');

        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/reda-alojamiento'),
        ]);

        $this->loadJsonTranslationsFrom(__DIR__.'/../resources/lang');

        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/reda-alojamiento'),
        ], 'reda-alojamiento-translations'); // Añadido un tag para orden

        /**
         * GANCHO PARA RUTAS ORIGINALES
         * Una vez que Laravel ha cargado todas las rutas (incluyendo las del proyecto original),
         * buscamos las de login y les asignamos nombre para que los middleware funcionen
         * sin tener que modificar el archivo routes/web.php original.
         */
        $this->app->booted(function () {
            $router = $this->app['router'];
            $routes = $router->getRoutes();

            foreach ($routes as $route) {
                // Asignamos 'login' a la ruta de usuario (evita error 500)
                if ($route->uri() === 'login' && !$route->getName()) {
                    $route->name('login');
                }
                // Asignamos 'admin.login' a la ruta de admin (para uso futuro)
                if ($route->uri() === 'admin/login' && !$route->getName()) {
                    $route->name('admin.login');
                }
            }
        });

        /* PUBLICACIÓN DE ASSETS ESTÁTICOS
        // Ejemplo
        $this->publishes([
            __DIR__.'/../resources/img' => public_path('vendor/reda-alojamiento/img'),
        ], 'reda-alojamiento-static-assets');
        // El usuario ejecutaría: php artisan vendor:publish --tag=reda-alojamiento-static-assets
        */

    }
}
