<?php

// CAMBIO 1: Nuevo namespace del paquete
namespace Reda\RedaAlojamiento;

use Illuminate\Support\ServiceProvider;
use Reda\RedaAlojamiento\Http\Controllers\General\RedaInboxController;
use Illuminate\Support\Facades\Route;

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

        // Inyectar assets del plugin en todas las páginas web
        if (!$this->app->runningInConsole()) {
            $this->app->make(\Illuminate\Contracts\Http\Kernel::class)->prependMiddlewareToGroup('web', \Reda\RedaAlojamiento\Http\Middleware\InjectPluginAssets::class);
        }

        // Carga las rutas base
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Carga las vistas con el nuevo namespace 'reda-alojamiento'
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'reda-alojamiento');

        // Colocamos el alias 'pasos' apuntando a la carpeta específica de los formularios
        $this->loadViewsFrom(__DIR__.'/../resources/views/experiencia/experiencias/formularios_de_pasos', 'pasos');

        // Carga las migraciones
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // PUBLICACIÓN DE CONFIGURACIÓN
        $this->publishes([
            __DIR__.'/../config/reda-alojamiento.php' => config_path('reda-alojamiento.php'),
        ], 'reda-alojamiento-config');

        // Traducciones
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'reda-alojamiento');

        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/reda-alojamiento'),
        ]);

        $this->loadJsonTranslationsFrom(__DIR__.'/../resources/lang');

        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/reda-alojamiento'),
        ], 'reda-alojamiento-translations');

        /**
         * SOBRESCRITURA DE RUTAS ORIGINALES
         * Usamos el evento 'booted' para asegurar que nuestras rutas se registren AL FINAL,
         * o mejor aún, modificamos las existentes para asegurar prioridad.
         */
        $this->app->booted(function () {
            $router = $this->app['router'];
            $routes = $router->getRoutes();

            // 1. Sobrescribir Inbox (Búsqueda por URI exacta)
            foreach ($routes->get('GET') as $route) {
                if ($route->uri() === 'inbox') {
                    $route->uses([RedaInboxController::class, 'index']);
                }
            }
            foreach ($routes->get('POST') as $route) {
                if ($route->uri() === 'inbox') {
                    $route->uses([RedaInboxController::class, 'index']);
                }
                if ($route->uri() === 'messaging/booking') {
                    $route->uses([RedaInboxController::class, 'message']);
                }
                if ($route->uri() === 'messaging/reply') {
                    $route->uses([RedaInboxController::class, 'messageReply']);
                }
            }

            // 2. Sobrescribir Pago / Reserva
            // Buscamos la ruta 'payments/book/{id?}' para inyectar nuestra lógica pre-auth
            foreach ($routes as $route) {
                if ($route->uri() === 'payments/book/{id?}') {
                    // Cambiamos el controlador al del plugin
                    $route->uses([\Reda\RedaAlojamiento\Http\Controllers\General\RedaPaymentController::class, 'index']);
                    
                    // IMPORTANTE: Removemos el middleware 'guest' (que en este proyecto obliga a login)
                    // para que nuestra lógica en el controlador pueda capturar el POST antes de redirigir.
                    $middlewares = $route->middleware();
                    if (is_array($middlewares)) {
                        $route->setMiddleware(array_values(array_filter($middlewares, function($m) {
                            return !str_contains($m, 'guest');
                        })));
                    }
                }
            }

            // 3. Asignamos nombres a rutas de login si no los tienen
            foreach ($routes as $route) {
                if ($route->uri() === 'login' && !$route->getName()) {
                    $route->name('login');
                }
                if ($route->uri() === 'admin/login' && !$route->getName()) {
                    $route->name('admin.login');
                }
            }
        });
    }
}
