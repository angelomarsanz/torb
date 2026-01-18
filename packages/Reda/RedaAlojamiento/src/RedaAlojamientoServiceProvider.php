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
        $router = $this->app['router'];
    
        // Esto registra tu middleware con una prioridad alta
        $router->aliasMiddleware('reda.auth', \Reda\RedaAlojamiento\Http\Middleware\CheckPluginAuth::class);

        // Carga las rutas
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        
        // Carga las vistas con el nuevo namespace 'reda-alojamiento-js'
        // Esto permite referencias como: 'reda-alojamiento-js::experiencia.index'
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'reda-alojamiento');

        // Carga las migraciones
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // PUBLICACIÓN DE CONFIGURACIÓN
        $this->publishes([
            __DIR__.'/../config/reda-alojamiento.php' => config_path('reda-alojamiento.php'),
        // Nueva etiqueta
        ], 'reda-alojamiento-config');

        /* PUBLICACIÓN DE ASSETS ESTÁTICOS
        // Ejemplo
        $this->publishes([
            __DIR__.'/../resources/img' => public_path('vendor/reda-alojamiento/img'),
        ], 'reda-alojamiento-static-assets');
        // El usuario ejecutaría: php artisan vendor:publish --tag=reda-alojamiento-static-assets
        */

    }
}