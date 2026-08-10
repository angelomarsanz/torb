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
         * ganando la prioridad sobre las rutas del archivo routes/web.php original.
         */
        $this->app->booted(function () {
            // Re-registramos las rutas del Inbox con nuestro controlador del plugin
            Route::middleware(['web', 'locale', 'auth'])->group(function () {
                Route::match(['get', 'post'], 'inbox', [RedaInboxController::class, 'index'])->name('inbox');
                Route::post('messaging/booking', [RedaInboxController::class, 'message']);
                Route::post('messaging/reply', [RedaInboxController::class, 'messageReply']);
            });

            // Sobrescribimos la ruta de reserva para manejar la redirección de login sin pérdida de datos
            Route::middleware(['web', 'locale'])->group(function () {
                Route::match(['get', 'post'], 'payments/book/{id?}', [\Reda\RedaAlojamiento\Http\Controllers\General\RedaPaymentController::class, 'index']);
            });

            // Asignamos nombres a rutas de login si no los tienen
            $router = $this->app['router'];
            $routes = $router->getRoutes();
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
