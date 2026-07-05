<?php

namespace Reda\RedaAlojamiento\Http\Controllers\Experiencia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Settings;
use App\Models\Currency;
use Reda\RedaAlojamiento\Models\Experiencia\{
    Experiencia,
    ActividadExperiencia,
    HorarioExperiencia,
    InformacionExperiencia,
    AnfitrionExperiencia,
    FotoExperiencia,
    PlanNegocio,
    CalificacionExperiencia
};
use Auth;
use Illuminate\Support\Facades\File;
use Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Country;
use Illuminate\Support\Facades\Validator;

class ExperienciaController extends Controller
{
    public function index(Request $request)
    {
        $data['experiencias'] = Experiencia::with('fotos')
                            ->withCount('calificaciones')
                            ->withAvg('calificaciones', 'estrellas')
                            ->where('user_id', Auth::id())
                            ->orderBy('id', 'desc')
                            ->paginate(Session::get('row_per_page') ?? 10);

        // Necesitamos la moneda para mostrar los precios
        $data['currentCurrency'] = \App\Http\Helpers\Common::getCurrentCurrency();

        return view('reda-alojamiento::experiencia.experiencias.index', $data);
    }

    public function listadoFrontend(Request $request)
    {
        // 1. Obtener Categorías desde Settings
        $setting = DB::table('settings')->where('name', 'opciones_tipos_de_negocios')->first();
        $categoriasNegocios = [];
        if ($setting && !empty($setting->value)) {
            $dataJson = json_decode($setting->value, true);
            $categoriasNegocios = $dataJson['categorias'] ?? [];
            ksort($categoriasNegocios);
        }

        // 2. Construir la consulta base con conteo y promedio de calificaciones
        $query = Experiencia::with(['fotos', 'owner'])
            ->withCount('calificaciones')
            ->withAvg('calificaciones', 'estrellas');

        // 3. Aplicar Filtros si existen (para AJAX o búsqueda directa)
        if ($request->filled('nombre_comercio')) {
            $query->where('titulo', 'like', '%' . $request->nombre_comercio . '%');
        }

        if ($request->filled('categoria')) {
            $query->where('categoria_negocio', $request->categoria);
        }

        $distanciaCalculada = false;
        if ($request->filled('latitud') && $request->filled('longitud') && $request->filled('radio')) {
            $lat = $request->latitud;
            $lng = $request->longitud;
            $radio = $request->radio; // en km

            // Fórmula de Haversine para filtrar por distancia usando whereRaw (más robusto para filtrado puro)
            $query->whereRaw("
                (6371 * acos(cos(radians(?)) * cos(radians(JSON_UNQUOTE(JSON_EXTRACT(ubicacion, '$.latitud'))))
                * cos(radians(JSON_UNQUOTE(JSON_EXTRACT(ubicacion, '$.longitud'))) - radians(?))
                + sin(radians(?)) * sin(radians(JSON_UNQUOTE(JSON_EXTRACT(ubicacion, '$.latitud')))))) <= ?
            ", [$lat, $lng, $lat, $radio]);

            $distanciaCalculada = true;
        } elseif ($request->filled('ubicacion_texto')) {
            $query->where('ubicacion', 'like', '%' . $request->ubicacion_texto . '%');
        }

        // 4. Obtener Destacados (Filtrar por los que tienen plan_negocios y el plan sea destacado)
        $idsPlanesDestacados = PlanNegocio::where('destacado', true)->pluck('id')->toArray();

        $destacadosQuery = clone $query;
        $destacadosQuery->whereNotNull('plan_negocios')
                        ->where('plan_negocios', '<>', '[]')
                        ->where('plan_negocios', '<>', '{}')
                        ->whereIn('plan_negocios->plan_id', $idsPlanesDestacados);
        
        // Ordenar destacados: si hay búsqueda por radio, el orden por defecto es el de la query filtrada
        $destacados = $destacadosQuery->orderBy('id', 'desc')->take(10)->get();
        $totalDestacados = $destacadosQuery->count();

        // 5. Obtener Listado General con Paginación
        $experiencias = $query->orderBy('id', 'desc')->paginate(10);
        $totalExperiencias = $experiencias->total();

        $currentCurrency = \App\Http\Helpers\Common::getCurrentCurrency();

        // 6. Respuesta para AJAX
        if ($request->ajax()) {
            try {
                $htmlDestacados = '';
                if ($totalDestacados > 0) {
                    $htmlDestacados = view('reda-alojamiento::experiencia.experiencias.frontend.partials.lista_cards', [
                        'experiencias' => $destacados,
                        'currentCurrency' => $currentCurrency,
                        'idsPlanesDestacados' => $idsPlanesDestacados
                    ])->render();

                    if ($totalDestacados > 10) {
                         $htmlDestacados .= view('reda-alojamiento::experiencia.experiencias.frontend.partials.card_ver_todos_negocios', [
                            'items' => $destacados,
                            'tipo' => 'destacados',
                            'tituloModal' => __('Comercios Destacados'),
                            'total' => $totalDestacados
                        ])->render();
                    }
                }

                $htmlGeneral = view('reda-alojamiento::experiencia.experiencias.frontend.partials.lista_cards', [
                    'experiencias' => $experiencias,
                    'currentCurrency' => $currentCurrency,
                    'idsPlanesDestacados' => $idsPlanesDestacados
                ])->render();

                if ($totalExperiencias > 10) {
                    $htmlGeneral .= view('reda-alojamiento::experiencia.experiencias.frontend.partials.card_ver_todos_negocios', [
                        'items' => $experiencias,
                        'tipo' => 'todos',
                        'tituloModal' => __('Explora todos los Comercios'),
                        'total' => $totalExperiencias
                    ])->render();
                }

                $htmlPaginacion = $experiencias->links('vendor.pagination.bootstrap-4')->render();

                $respuesta = [
                    'success' => true,
                    'message' => 'Results retrieved successfully',
                    'mensaje_usuario' => __('Resultados recuperados con éxito'),
                    'respuesta' => [
                        'html_destacados' => $htmlDestacados,
                        'total_destacados' => $totalDestacados,
                        'html_general'    => $htmlGeneral,
                        'html_paginacion' => $htmlPaginacion,
                        'total'           => $totalExperiencias
                    ],
                    'code' => 200
                ];
            } catch (\Exception $e) {
                $respuesta = [
                    'success' => false,
                    'message' => 'Error rendering results: ' . $e->getMessage(),
                    'mensaje_usuario' => __('Error al preparar los resultados'),
                    'respuesta' => $e->getMessage(),
                    'code' => 500
                ];
            }
            return response()->json($respuesta, $respuesta['code']);
        }

        // 7. Listas para búsqueda inteligente
        $nombresComercios = Experiencia::distinct()->whereNotNull('titulo')->pluck('titulo')->toArray();
        
        $nombresProductos = ActividadExperiencia::where('tipo_producto_servicio', 'producto')
            ->where('estatus_producto_servicio', 'activo')
            ->whereNotNull('nombre_actividad')
            ->distinct()
            ->pluck('nombre_actividad')
            ->toArray();

        $nombresServicios = ActividadExperiencia::where('tipo_producto_servicio', 'servicio')
            ->where('estatus_producto_servicio', 'activo')
            ->whereNotNull('nombre_actividad')
            ->distinct()
            ->pluck('nombre_actividad')
            ->toArray();

        return view('reda-alojamiento::experiencia.experiencias.frontend.listado_experiencias', compact(
            'experiencias',
            'totalExperiencias',
            'destacados',
            'totalDestacados',
            'categoriasNegocios',
            'currentCurrency',
            'nombresComercios',
            'nombresProductos',
            'nombresServicios',
            'idsPlanesDestacados'
        ));
    }

    /**
     * Muestra todos los productos y servicios encontrados independientemente del comercio.
     */
    public function productosServiciosEncontrados(Request $request)
    {
        $busqueda = $request->get('q');
        $tipo = $request->get('tipo'); // 'producto' o 'servicio'

        $query = ActividadExperiencia::with(['experiencia.fotos', 'currency'])
            ->where('estatus_producto_servicio', 'activo');

        if ($busqueda) {
            $query->where('nombre_actividad', 'like', '%' . $busqueda . '%');
        }

        if ($tipo) {
            $query->where('tipo_producto_servicio', $tipo);
        }

        // Obtener resultados destacados (por ahora los 10 primeros que coincidan)
        $destacadosQuery = clone $query;
        $actividadesDestacadas = $destacadosQuery->orderBy('id', 'desc')->take(10)->get();
        $totalDestacados = $destacadosQuery->count();

        // Obtener todos los resultados con paginación
        $actividades = $query->orderBy('id', 'desc')->paginate(12);
        $totalActividades = $actividades->total();

        $currentCurrency = \App\Http\Helpers\Common::getCurrentCurrency();

        // Si es AJAX (para scroll infinito o filtros rápidos en esta vista)
        if ($request->ajax()) {
            // Implementar lógica similar a obtenerActividadesPaginadas si es necesario
        }

        return view('reda-alojamiento::experiencia.experiencias.frontend.productos_servicios_encontrados', compact(
            'actividades',
            'totalActividades',
            'actividadesDestacadas',
            'totalDestacados',
            'currentCurrency',
            'busqueda',
            'tipo'
        ));
    }

    /**
     * Obtiene negocios paginados vía AJAX para el modal de scroll infinito.
     */
    public function obtenerNegociosPaginados(Request $request)
    {
        try {
            $offset = $request->get('offset', 0);
            $limit = 10;
            $tipo = $request->get('tipo', 'todos');
            $esModal = $request->get('es_modal', false);

            $query = Experiencia::with(['fotos', 'owner'])
                ->withCount('calificaciones')
                ->withAvg('calificaciones', 'estrellas');

            // --- APLICAR MISMOS FILTROS QUE EN listadoFrontend ---

            if ($request->filled('nombre_comercio')) {
                $query->where('titulo', 'like', '%' . $request->nombre_comercio . '%');
            }

            if ($request->filled('categoria')) {
                $query->where('categoria_negocio', $request->categoria);
            }

            $distanciaCalculada = false;
            if ($request->filled('latitud') && $request->filled('longitud') && $request->filled('radio')) {
                $lat = $request->latitud;
                $lng = $request->longitud;
                $radio = $request->radio;

                // Fórmula de Haversine para filtrar por distancia usando whereRaw (más robusto para filtrado puro)
                $query->whereRaw("
                    (6371 * acos(cos(radians(?)) * cos(radians(JSON_UNQUOTE(JSON_EXTRACT(ubicacion, '$.latitud'))))
                    * cos(radians(JSON_UNQUOTE(JSON_EXTRACT(ubicacion, '$.longitud'))) - radians(?))
                    + sin(radians(?)) * sin(radians(JSON_UNQUOTE(JSON_EXTRACT(ubicacion, '$.latitud')))))) <= ?
                ", [$lat, $lng, $lat, $radio]);

                $distanciaCalculada = true;
            } elseif ($request->filled('ubicacion_texto')) {
                $query->where('ubicacion', 'like', '%' . $request->ubicacion_texto . '%');
            }

            // Filtrar por plan_negocios si el tipo es destacados
            if ($tipo === 'destacados') {
                $idsPlanesDestacados = PlanNegocio::where('destacado', true)->pluck('id')->toArray();
                $query->whereNotNull('plan_negocios')
                      ->where('plan_negocios', '<>', '[]')
                      ->where('plan_negocios', '<>', '{}')
                      ->whereIn('plan_negocios->plan_id', $idsPlanesDestacados);
            }

            if ($distanciaCalculada) {
                $query->orderBy('distancia', 'asc');
            } else {
                $query->orderBy('id', 'desc');
            }

            $items = $query->offset($offset)->limit($limit)->get();
            $currentCurrency = \App\Http\Helpers\Common::getCurrentCurrency();
            $idsPlanesDestacados = PlanNegocio::where('destacado', true)->pluck('id')->toArray();
            $html = '';

            foreach ($items as $item) {
                if ($esModal) {
                    $html .= '<div class="col-12 col-md-6 col-lg-4 item-col-infinito mb-4">';
                    $html .= view('reda-alojamiento::experiencia.experiencias.frontend.partials.card_negocio', [
                        'experiencia' => $item,
                        'currentCurrency' => $currentCurrency,
                        'es_modal' => true,
                        'idsPlanesDestacados' => $idsPlanesDestacados
                    ])->render();
                    $html .= '</div>';
                } else {
                    $html .= view('reda-alojamiento::experiencia.experiencias.frontend.partials.card_negocio', [
                        'experiencia' => $item,
                        'currentCurrency' => $currentCurrency,
                        'idsPlanesDestacados' => $idsPlanesDestacados
                    ])->render();
                }
            }

            $respuesta = [
                'success' => true,
                'message' => 'Businesses retrieved successfully',
                'mensaje_usuario' => __('Negocios recuperados con éxito'),
                'respuesta' => [
                    'html' => $html,
                    'cantidad' => $items->count(),
                    'proximo_offset' => $offset + $items->count()
                ],
                'code' => 200
            ];
            return response()->json($respuesta, $respuesta['code']);

        } catch (\Exception $e) {
            $respuesta = [
                'success' => false,
                'message' => 'Error retrieving businesses: ' . $e->getMessage(),
                'mensaje_usuario' => __('Error al recuperar los negocios'),
                'respuesta' => $e->getMessage(),
                'code' => 500
            ];
            return response()->json($respuesta, $respuesta['code']);
        }
    }

    /**
     * Muestra la vista de detalle de un negocio (experiencia) para el frontend.
     */
    public function listadoProductosServicios(Request $request, $id, $actividad_id = null)
    {
        $experiencia = Experiencia::with(['fotos', 'actividades', 'owner', 'anfitrion', 'informaciones', 'calificaciones.usuario'])
            ->withCount('calificaciones')
            ->withAvg('calificaciones', 'estrellas')
            ->findOrFail($id);

        $q = $request->get('q');
        $tipo_actividad_filtro = $request->get('tipo_actividad');

        // El parámetro puede venir de la ruta ({actividad_id}) o del query string (?actividad_id=)
        $actividadIdDeepLink = $actividad_id ?? $request->get('actividad_id');
        $actividadTarget = null;

        if ($actividadIdDeepLink) {
            $actividadTarget = $experiencia->actividades()
                ->where('id', $actividadIdDeepLink)
                ->where('estatus_producto_servicio', 'activo')
                ->first();
        }

        // Obtenemos los productos/servicios activos con FILTRADO REAL
        $queryBase = $experiencia->actividades()
            ->where('estatus_producto_servicio', 'activo');

        if ($q) {
            $queryBase->where('nombre_actividad', 'like', '%' . $q . '%');
        }

        // Filtrado ESTRICTO por tipo si se recibe (evita mezcla de productos y servicios)
        if ($tipo_actividad_filtro) {
            $queryBase->where('tipo_producto_servicio', $tipo_actividad_filtro);
        }

        $todasActividades = $queryBase->get();

        // Ordenamos por relevancia si hay búsqueda por texto
        if ($q) {
            $todasActividades = $todasActividades->sortByDesc(function($actividad) use ($q) {
                // Prioridad: coincidencia exacta > contiene la palabra
                if (strtolower($actividad->nombre_actividad) == strtolower($q)) return 2;
                return 1;
            });
        } else {
            $todasActividades = $todasActividades->sortBy('orden_actividad');
        }

        $actividades = $todasActividades->take(10);
        $totalActividades = $todasActividades->count();

        // Promociones (Filtradas también por los criterios de búsqueda)
        $promocionesCompletas = $todasActividades->filter(function($actividad) {
            $complementos = json_decode($actividad->precios_monedas_complementarios, true);
            return isset($complementos['precio_promocion']) && floatval($complementos['precio_promocion']) > 0;
        });
        
        $promociones = $promocionesCompletas->take(10);
        $totalPromociones = $promocionesCompletas->count();

        // Reseñas (Primeras 10 para carrusel)
        $calificaciones = $experiencia->calificaciones->sortByDesc('created_at')->take(10);
        $totalCalificaciones = $experiencia->calificaciones_count;

        $currentCurrency = \App\Http\Helpers\Common::getCurrentCurrency();

        // --- Listas para búsqueda inteligente (Sugerencias) ---
        $listaNombresProductos = $experiencia->actividades()
            ->where('tipo_producto_servicio', 'producto')
            ->where('estatus_producto_servicio', 'activo')
            ->whereNotNull('nombre_actividad')
            ->distinct()
            ->pluck('nombre_actividad')
            ->toArray();

        $listaNombresServicios = $experiencia->actividades()
            ->where('tipo_producto_servicio', 'servicio')
            ->where('estatus_producto_servicio', 'activo')
            ->whereNotNull('nombre_actividad')
            ->distinct()
            ->pluck('nombre_actividad')
            ->toArray();

        return view('reda-alojamiento::experiencia.experiencias.frontend.listado_productos_servicios', compact(
            'experiencia',
            'actividades',
            'totalActividades',
            'promociones',
            'totalPromociones',
            'calificaciones',
            'totalCalificaciones',
            'currentCurrency',
            'actividadIdDeepLink',
            'actividadTarget',
            'q',
            'listaNombresProductos',
            'listaNombresServicios'
        ));
    }

    /**
     * Obtiene actividades paginadas vía AJAX para los carruseles o el modal de scroll infinito.
     */
    public function obtenerActividadesPaginadas(Request $request, $id)
    {
        try {
            $offset = $request->get('offset', 0);
            $limit = 10;
            $tipo = $request->get('tipo', 'todas'); 
            $esModal = $request->get('es_modal', false);
            $tipoActividadFiltro = $request->get('tipo_actividad'); // 'producto' o 'servicio'

            $experiencia = Experiencia::findOrFail($id);
            $currentCurrency = \App\Http\Helpers\Common::getCurrentCurrency();
            $html = '';

            if ($tipo === 'reseñas') {
                $items = $experiencia->calificaciones()
                    ->with('usuario')
                    ->orderBy('created_at', 'desc')
                    ->offset($offset)
                    ->limit($limit)
                    ->get();

                foreach ($items as $item) {
                    $view = 'reda-alojamiento::experiencia.experiencias.frontend.partials.card_reseña';
                    if ($esModal) {
                        $html .= '<div class="col-12 col-lg-6 item-col-infinito">';
                        $html .= view($view, ['calificacion' => $item])->render();
                        $html .= '</div>';
                    } else {
                        $html .= view($view, ['calificacion' => $item])->render();
                    }
                }
            } else {
                $query = $experiencia->actividades()
                    ->where('estatus_producto_servicio', 'activo');

                // Aplicar filtro por tipo de actividad si se recibe
                if (!empty($tipoActividadFiltro)) {
                    $query->where('tipo_producto_servicio', $tipoActividadFiltro);
                }

                $query->orderBy('orden_actividad', 'asc');

                if ($tipo === 'promociones') {
                    $todas = $query->get();
                    $itemsFiltrados = $todas->filter(function($actividad) {
                        $complementos = json_decode($actividad->precios_monedas_complementarios, true);
                        return isset($complementos['precio_promocion']) && floatval($complementos['precio_promocion']) > 0;
                    });
                    $items = $itemsFiltrados->slice($offset, $limit);
                } else {
                    $items = $query->offset($offset)->limit($limit)->get();
                }

                foreach ($items as $item) {
                    $view = 'reda-alojamiento::experiencia.experiencias.frontend.partials.card_producto_servicio';
                    
                    if ($esModal) {
                        $html .= '<div class="col-12 col-lg-6 item-col-infinito">';
                        $html .= view($view, [
                            'actividad' => $item,
                            'currentCurrency' => $currentCurrency,
                            'es_promo' => ($tipo === 'promociones')
                        ])->render();
                        $html .= '</div>';
                    } else {
                        $html .= view($view, [
                            'actividad' => $item,
                            'currentCurrency' => $currentCurrency,
                            'es_promo' => ($tipo === 'promociones')
                        ])->render();
                    }
                }
            }

            $respuesta = [
                'success' => true,
                'message' => 'Activities retrieved successfully',
                'mensaje_usuario' => __('Actividades recuperadas con éxito'),
                'respuesta' => [
                    'html' => $html,
                    'cantidad' => (isset($items) ? $items->count() : 0),
                    'proximo_offset' => $offset + (isset($items) ? $items->count() : 0)
                ],
                'code' => 200
            ];
            return response()->json($respuesta, $respuesta['code']);

        } catch (\Exception $e) {
            $respuesta = [
                'success' => false,
                'message' => 'Error retrieving activities: ' . $e->getMessage(),
                'mensaje_usuario' => __('Error al recuperar las actividades'),
                'respuesta' => $e->getMessage(),
                'code' => 500
            ];
            return response()->json($respuesta, $respuesta['code']);
        }
    }

    public function create(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate(
                [
                    'titulo' => 'required|min:5'
                ],
                [
                    'titulo.required' => __('El nombre del negocio es obligatorio.'),
                    'titulo.min'      => __('El nombre del negocio debe tener al menos 5 caracteres.'),
                ]);

            // 1. Crear Experiencia Principal
            $experiencia = new Experiencia;
            $experiencia->titulo = $request->titulo;
            $experiencia->user_id = Auth::id();
            $experiencia->save();

            // 2. Inicializar registros relacionados para que existan en los siguientes pasos
            ActividadExperiencia::create(['experiencia_id' => $experiencia->id]);
            HorarioExperiencia::create(['experiencia_id' => $experiencia->id]);
            InformacionExperiencia::create(['experiencia_id' => $experiencia->id]);
            AnfitrionExperiencia::create(['experiencia_id' => $experiencia->id, 'user_id' => Auth::id()]);

            return redirect()->route('reda.negocios.experiencias.pasos', ['id' => $experiencia->id, 'paso' => 'descripcion']);
        }

        return view('reda-alojamiento::experiencia.experiencias.create');
    }

    public function formularioDePasosExperiencias(Request $request)
    {
        $id = $request->id;
        $paso = $request->paso;
        $actividades = null;
        $categoriasNegocios = [];
        $country = [];

        // Cargamos la experiencia con TODAS sus relaciones de una sola vez
        $result = Experiencia::with([
            'fotos',
            'actividades',
            'horarios',
            'informaciones',
            'anfitrion'
        ])->findOrFail($id);

        if ($request->isMethod('get')) {
            if ($paso === 'descripcion') {
                $setting = Settings::where('name', 'opciones_tipos_de_negocios')->first();
                if ($setting && !empty($setting->value)) {
                    $dataJson = json_decode($setting->value, true);
                    $categoriasNegocios = $dataJson['categorias'] ?? [];
                    ksort($categoriasNegocios);
                }
            }

            if ($paso === 'actividades') {
                $actividades = ActividadExperiencia::where('experiencia_id', $id)
                    ->orderBy('orden_actividad', 'asc')
                    ->paginate(10);

                if ($actividades->isEmpty()) {
                    $this->crearActividadInicial($id);

                    // Re-consultamos para obtener el registro recién creado
                    $actividades = ActividadExperiencia::where('experiencia_id', $id)
                        ->orderBy('orden_actividad', 'asc')
                        ->paginate(10);
                }
            }

            $planes = [];
            if ($paso === 'precio') {
                $planes = PlanNegocio::where('estatus', 1)->orderBy('orden', 'asc')->get();
            }

            if ($paso === 'ubicacion') {
                $country = Country::pluck('name', 'short_name');

                // --- SOPORTE PARA NOMBRES DE COLUMNA CON/SIN ACENTO ---
                $datosUbicacion = $result->ubicacion ?? $result->ubicación ?? null;

                // Si sigue siendo nulo, intentamos sacarlo manualmente de los atributos
                if (!$datosUbicacion) {
                    $atributos = $result->getAttributes();
                    $datosUbicacion = $atributos['ubicacion'] ?? $atributos['ubicación'] ?? null;
                }

                // Si es un string (JSON sin decodificar), lo decodificamos
                if (is_string($datosUbicacion)) {
                    $datosUbicacion = json_decode($datosUbicacion, true);
                }

                // Normalizamos las claves para que Blade siempre las encuentre (sin acentos)
                if (is_array($datosUbicacion)) {
                    $result->ubicacion = [
                        'busqueda_mapa'       => $datosUbicacion['busqueda_mapa'] ?? $datosUbicacion['búsqueda_mapa'] ?? '',
                        'latitud'             => $datosUbicacion['latitud'] ?? '',
                        'longitud'            => $datosUbicacion['longitud'] ?? '',
                        'linea_uno_direccion' => $datosUbicacion['linea_uno_direccion'] ?? $datosUbicacion['línea_uno_dirección'] ?? '',
                        'linea_dos_direccion' => $datosUbicacion['linea_dos_direccion'] ?? $datosUbicacion['línea_dos_dirección'] ?? '',
                        'ciudad'              => $datosUbicacion['ciudad'] ?? '',
                        'estado'              => $datosUbicacion['estado'] ?? '',
                        'pais'                => $datosUbicacion['pais'] ?? $datosUbicacion['país'] ?? '',
                        'codigo_postal'       => $datosUbicacion['codigo_postal'] ?? $datosUbicacion['código_postal'] ?? '',
                        'email_negocio'       => $datosUbicacion['email_negocio'] ?? '',
                        'whatsapp_negocio'    => $datosUbicacion['whatsapp_negocio'] ?? '',
                    ];
                }

                Log::info("Ubicación normalizada para la vista: " . print_r($result->ubicacion, true));
            }

            Log::info("formularioDePasosExperiencias, actividades: " . print_r($actividades, true));

            return view("reda-alojamiento::experiencia.experiencias.formularios_de_pasos.$paso",
                compact('result', 'paso', 'actividades', 'categoriasNegocios', 'country', 'planes'));
        }
        elseif ($request->isMethod('post')) {
            switch ($paso) {
                case 'descripcion':
                    $request->validate(
                        [
                            'titulo' => 'required|min:5',
                            'descripcion' => 'required|min:20',
                            'categoria_negocio' => 'required',
                            'logo_exists' => 'required'
                        ],
                        [
                            'titulo.required' => __('El nombre del negocio es obligatorio.'),
                            'titulo.min'      => __('El nombre del negocio debe tener al menos 5 caracteres.'),
                            'descripcion.required' => __('La descripción es obligatoria.'),
                            'descripcion.min'      => __('La descripción debe tener al menos 20 caracteres.'),
                            'categoria_negocio.required' => __('La categoría del negocio es obligatoria.'),
                            'logo_exists.required' => __('El logo del negocio es obligatorio.'),
                        ]);

                    // Doble verificación: si el logo no está en DB, lanzamos error aunque el input diga que sí
                    if (empty($result->ruta_imagenes)) {
                        return back()->withErrors(['logo_exists' => __('El logo del negocio es obligatorio.')])->withInput();
                    }

                    $result->titulo = $request->titulo;
                    $result->descripcion = $request->descripcion;
                    $result->categoria_negocio = $request->categoria_negocio; // Guardamos el valor
                    $result->save();

                    return redirect()->route('reda.negocios.experiencias.pasos', ['id' => $id, 'paso' => 'fotos']);

                case 'fotos':
                    $conteoFotos = FotoExperiencia::where('experiencia_id', $id)->count();

                    if ($conteoFotos == 0) {
                        return back()->withErrors(['foto' => __('La foto es obligatoria.')]);
                    }

                    return redirect()->route('reda.negocios.experiencias.pasos', ['id' => $id, 'paso' => 'actividades']);

                case 'actividades':
                    if ($request->has('actividades') && is_array($request->actividades)) {
                        $request->validate(
                            [
                                'actividades' => 'required|array|min:1',
                                'actividades.*.nombre_actividad' => 'required|min:3',
                                'actividades.*.descripcion_actividad' => 'required|min:20',
                                'actividades.*.tipo_producto_servicio' => 'required',
                                'actividades.*.precio' => 'required|numeric|min:0.01',
                                'actividades.*.currency_id' => 'required',
                                'actividades.*.disponibilidad' => 'required',
                                'actividades.*.precio_pago_bolivares' => 'nullable|required_if:actividades.*.tipo_carga_precio_local,manual|numeric|min:0.01',
                                'actividades.*.moneda_pago_bolivares' => 'nullable|required_if:actividades.*.tipo_carga_precio_local,manual',
                            ],
                            [
                                // Nombre
                                'actividades.*.nombre_actividad.required' => __('El nombre del producto o servicio es obligatorio.'),
                                'actividades.*.nombre_actividad.min' => __('El nombre del producto o servicio debe tener al menos 3 caracteres.'),

                                // Descripción
                                'actividades.*.descripcion_actividad.required' => __('La descripción es obligatoria.'),
                                'actividades.*.descripcion_actividad.min' => __('La descripción debe tener al menos 20 caracteres.'),

                                // Tipo de producto o servicio
                                'actividades.*.tipo_producto_servicio.required' => __('El tipo (producto o servicio) es obligatorio.'),

                                // Precio
                                'actividades.*.precio.required' => __('El precio es obligatorio.'),
                                'actividades.*.precio.numeric' => __('El precio debe ser un número válido.'),
                                'actividades.*.precio.min' => __('El precio debe ser mayor a cero.'),

                                // Moneda
                                'actividades.*.currency_id.required' => __('El tipo de moneda es obligatorio.'),

                                // Disponibilidad
                                'actividades.*.disponibilidad.required' => __('Debe seleccionar si está disponible o no.'),

                                // Pago en Bolívares (Manual)
                                'actividades.*.precio_pago_bolivares.required_if' => __('El precio para pago en bolívares es obligatorio'),
                                'actividades.*.precio_pago_bolivares.numeric' => __('El precio debe ser un número válido.'),
                                'actividades.*.precio_pago_bolivares.min' => __('Mínimo 0.01'),
                                'actividades.*.moneda_pago_bolivares.required_if' => __('Debe seleccionar una moneda'),
                            ]);

                        foreach ($request->actividades as $id_actividad => $datos) {
                            $actividad = ActividadExperiencia::find($id_actividad);

                            if ($actividad) {
                                if (empty($actividad->foto_actividad)) {
                                    $errorMsg = __('Falta la foto en la actividad');
                                    if ($request->ajax()) {
                                        $respuesta = [
                                            'success' => false,
                                            'message' => 'Missing photo in activity',
                                            'mensaje_usuario' => $errorMsg,
                                            'respuesta' => '',
                                            'code' => 422
                                        ];
                                        return response()->json($respuesta, $respuesta['code']);
                                    }
                                    return back()->withErrors([
                                        "foto_actividad_id_" . $id_actividad => $errorMsg
                                    ])->withInput();
                                }

                                $datosComplementarios = [
                                    'precio_pago_bolivares'  => $datos['precio_pago_bolivares'] ?? null,
                                    'moneda_pago_bolivares'  => $datos['moneda_pago_bolivares'] ?? null,
                                    'precio_promocion'       => $datos['precio_promocion'] ?? null,
                                    'moneda_precio_promocion' => $datos['moneda_precio_promocion'] ?? null,
                                ];

                                $actividad->update([
                                    'nombre_actividad'      => $datos['nombre_actividad'],
                                    'descripcion_actividad' => $datos['descripcion_actividad'],
                                    'tipo_producto_servicio'=> $datos['tipo_producto_servicio'],
                                    'precio'                => $datos['precio'],
                                    'currency_id'           => $datos['currency_id'],
                                    'disponibilidad'        => $datos['disponibilidad'],
                                    'estatus_producto_servicio' => $datos['estatus_producto_servicio'] ?? 'activo',
                                    'tipo_carga_precio_local' => $datos['tipo_carga_precio_local'] ?? 'automatico_bcv',
                                    'precios_monedas_complementarios' => json_encode($datosComplementarios)
                                ]);
                            }
                        }
                    }

                    // Si el usuario hizo clic en "Guardar" dentro del flujo de agregar producto
                    // nos quedamos en el mismo paso para mostrar la lista actualizada.
                    if ($request->stay_on_step == '1') {
                        if ($request->ajax()) {
                            $respuesta = [
                                'success' => true,
                                'message' => 'Product or service saved successfully',
                                'mensaje_usuario' => __('Producto o servicio guardado con éxito'),
                                'respuesta' => '',
                                'code' => 200
                            ];
                            return response()->json($respuesta, $respuesta['code']);
                        }
                        return redirect()->route('reda.negocios.experiencias.pasos', ['id' => $id, 'paso' => 'actividades'])
                                    ->with('success', __('Producto o servicio guardado con éxito'));
                    }

                    return redirect()->route('reda.negocios.experiencias.pasos', ['id' => $id, 'paso' => 'ubicacion'])
                                ->with('success', __('Productos y servicios actualizados con éxito.'));

                case 'ubicacion':
                    try {
                        $request->validate(
                            [
                                'map_search'          => 'required|max:250',
                                'address_line_1'      => 'required|max:250',
                                'country'             => 'required',
                                'city'                => 'required',
                                'state'               => 'required',
                                'latitude'            => 'required|not_in:0',
                                'email_negocio'       => 'required|email|max:255',
                                'whatsapp_negocio'    => 'required|max:255',
                            ],
                            [
                                'map_search.required'     => __('Búsqueda en el mapa obligatoria'),
                                'address_line_1.required' => __('Dirección obligatoria'),
                                'country.required'        => __('País obligatorio'),
                                'city.required'           => __('Ciudad obligatoria'),
                                'state.required'          => __('Estado obligatorio'),
                                'latitude.not_in'         => __('Debe fijar la posición en el mapa'),
                                'email_negocio.required'  => __('Correo electrónico obligatorio'),
                                'email_negocio.email'     => __('Ingrese un correo válido'),
                                'whatsapp_negocio.required' => __('WhatsApp obligatorio'),
                            ]);

                        $ubicacion = [
                            'busqueda_mapa'       => $request->map_search,
                            'longitud'            => $request->longitude,
                            'latitud'             => $request->latitude,
                            'linea_uno_direccion' => $request->address_line_1,
                            'linea_dos_direccion' => $request->address_line_2,
                            'ciudad'              => $request->city,
                            'estado'              => $request->state,
                            'pais'                => $request->country,
                            'codigo_postal'       => $request->postal_code,
                            'email_negocio'       => $request->email_negocio,
                            'whatsapp_negocio'    => $request->whatsapp_negocio,
                        ];

                        $result->ubicacion = $ubicacion;
                        $result->save();

                        if ($request->ajax()) {
                            $respuesta = [
                                'success' => true,
                                'message' => 'Location updated successfully',
                                'mensaje_usuario' => __('Ubicación actualizada con éxito.'),
                                'respuesta' => route('reda.negocios.experiencias.pasos', ['id' => $id, 'paso' => 'horario']),
                                'code' => 200
                            ];
                            return response()->json($respuesta, $respuesta['code']);
                        }

                        return redirect()->route('reda.negocios.experiencias.pasos', ['id' => $id, 'paso' => 'horario'])
                        ->with('success', __('Ubicación actualizada con éxito.'));

                    } catch (\Illuminate\Validation\ValidationException $e) {
                        if ($request->ajax()) {
                            $respuesta = [
                                'success' => false,
                                'message' => 'Validation error in location',
                                'mensaje_usuario' => $e->validator->errors()->first(),
                                'respuesta' => $e->validator->errors(),
                                'code' => 422
                            ];
                            return response()->json($respuesta, $respuesta['code']);
                        }
                        throw $e;
                    } catch (\Exception $e) {
                        if ($request->ajax()) {
                            $respuesta = [
                                'success' => false,
                                'message' => 'Error updating location: ' . $e->getMessage(),
                                'mensaje_usuario' => __('Error al actualizar la ubicación'),
                                'respuesta' => $e->getMessage(),
                                'code' => 500
                            ];
                            return response()->json($respuesta, $respuesta['code']);
                        }
                        return back()->withErrors(['error' => $e->getMessage()]);
                    }

                case 'horario':
                    return redirect()->route('reda.negocios.experiencias.pasos', ['id' => $id, 'paso' => 'anfitrion'])
                    ->with('success', __('Horarios confirmados con éxito.'));
                case 'anfitrion':
                    $request->validate(
                        [
                            'trayectoria_profesional' => 'required',
                        ],
                        [
                            'trayectoria_profesional.required' => __('La información de Nosotros es obligatoria'),
                        ]);

                    $anfitrion = AnfitrionExperiencia::where('experiencia_id', $id)->first();
                    if ($anfitrion) {
                        if (empty($anfitrion->foto_anfitrion)) {
                            return back()->withErrors(['foto_anfitrion' => __('La foto es obligatoria.')])->withInput();
                        }

                        $anfitrion->trayectoria_profesional = $request->trayectoria_profesional;
                        $anfitrion->save();
                    }
                    return redirect()->route('reda.negocios.experiencias.pasos', ['id' => $id, 'paso' => 'informacion_adicional'])
                    ->with('success', __('Información de Nosotros actualizada con éxito'));
                case 'informacion_adicional':
                    $informacion = InformacionExperiencia::where('experiencia_id', $id)->first();
                    if ($informacion) {
                        $informacion->requisitos_viajero = $request->requisitos_viajero;
                        $informacion->save();
                    }

                    return redirect()->route('reda.negocios.experiencias.pasos', ['id' => $id, 'paso' => 'precio'])
                    ->with('success', __('Información adicional actualizada con éxito'));
                case 'precio':
                    $request->validate([
                        'plan_id'           => 'required|exists:planes_negocios,id',
                        'plan_opcion_index' => 'required|integer|min:0'
                    ], [
                        'plan_id.required' => __('Por favor seleccione un plan para continuar'),
                        'plan_id.exists'   => __('El plan seleccionado no es válido'),
                    ]);

                    // --- VALIDACIÓN DE REQUISITOS PARA PLANES DESTACADOS ---
                    $plan = PlanNegocio::find($request->plan_id);
                    if ($plan && $plan->destacado) {
                        
                        // 1. Verificar Antigüedad
                        $settingAntiguedad = DB::table('settings')->where('name', 'antiguedad_planes_destacados')->first();
                        if ($settingAntiguedad) {
                            $config = json_decode($settingAntiguedad->value, true);
                            $cantidadReq = $config['cantidad'] ?? 0;
                            $unidadReq = $config['unidad_tiempo'] ?? 'Mes(es)';
                            
                            $fechaCreacion = \Carbon\Carbon::parse($result->created_at);
                            $antiguedadReal = 0;
                            
                            if ($unidadReq == 'Año(s)') {
                                $antiguedadReal = $fechaCreacion->diffInYears(now());
                            } elseif ($unidadReq == 'Mes(es)') {
                                $antiguedadReal = $fechaCreacion->diffInMonths(now());
                            } else {
                                $antiguedadReal = $fechaCreacion->diffInDays(now());
                            }
                            
                            if ($antiguedadReal < $cantidadReq) {
                                return back()->with('error_destacado', __('Su comercio no cumple con el requisito de antigüedad mínima de :cantidad :unidad para optar a un plan destacado.', [
                                    'cantidad' => $cantidadReq, 
                                    'unidad' => __($unidadReq)
                                ]));
                            }
                        }
                        
                        // 2. Verificar Promedio de Calificaciones
                        $settingPromedio = DB::table('settings')->where('name', 'promedio_calificaciones_planes_destacados')->first();
                        if ($settingPromedio) {
                            $promedioMinimo = (float) $settingPromedio->value;
                            $promedioReal = (float) CalificacionExperiencia::where('experiencia_id', $id)->avg('estrellas') ?? 0;
                            
                            if ($promedioReal < $promedioMinimo) {
                                return back()->with('error_destacado', __('Su comercio no cumple con el promedio de calificaciones mínimo de :promedio estrellas para optar a un plan destacado.', [
                                    'promedio' => $promedioMinimo
                                ]));
                            }
                        }
                    }
                    // --- FIN VALIDACIÓN ---

                    $result->plan_negocios = [
                        'plan_id'           => $request->plan_id,
                        'plan_opcion_index' => $request->plan_opcion_index,
                        // Aquí se pueden agregar estatus, fechas, etc. en el futuro
                    ];
                    $result->save();

                    return redirect()->route('reda.negocios.experiencias.index')
                                ->with('success', __('Pago realizado con éxito'));
            }
        }
    }

    /**
     * Actualiza el orden de las actividades vía Ajax.
     */
    public function reordenarActividades(Request $request)
    {
        try {
            $orden = $request->orden; // Array de IDs en el nuevo orden
            if (is_array($orden)) {
                foreach ($orden as $index => $id) {
                    ActividadExperiencia::where('id', $id)->update(['orden_actividad' => $index + 1]);
                }
                $respuesta = [
                    'success' => true,
                    'message' => 'Order updated successfully',
                    'mensaje_usuario' => __('Orden actualizado con éxito'),
                    'respuesta' => '',
                    'code' => 200
                ];
                return response()->json($respuesta, $respuesta['code']);
            }

            $respuesta = [
                'success' => false,
                'message' => 'Invalid order data',
                'mensaje_usuario' => __('Error al actualizar el orden'),
                'respuesta' => '',
                'code' => 400
            ];
            return response()->json($respuesta, $respuesta['code']);
        } catch (\Exception $e) {
            $respuesta = [
                'success' => false,
                'message' => 'Error reordering activities: ' . $e->getMessage(),
                'mensaje_usuario' => __('Error técnico al reordenar las actividades'),
                'respuesta' => $e->getMessage(),
                'code' => 500
            ];
            return response()->json($respuesta, $respuesta['code']);
        }
    }

    /**
     * Crea una actividad con valores por defecto para una experiencia.
     */
    private function crearActividadInicial($experienciaId)
    {
        try {
            $ultimoOrden = ActividadExperiencia::where('experiencia_id', $experienciaId)
                ->max('orden_actividad') ?? 0;

            $actividad = ActividadExperiencia::create([
                'experiencia_id'          => $experienciaId,
                'orden_actividad'         => $ultimoOrden + 1,
                'nombre_actividad'        => '',
                'descripcion_actividad'   => '',
                'tipo_producto_servicio'  => null, // Nueva columna
                'precio'                  => null,
                'currency_id'             => null,
                'disponibilidad'          => null,
                'estatus_producto_servicio' => 'activo',
                'foto_actividad'          => null
            ]);

            return [
                'success' => true,
                'message' => 'Initial activity created',
                'mensaje_usuario' => __('Actividad inicial creada'),
                'respuesta' => $actividad,
                'code' => 200
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error creating initial activity: ' . $e->getMessage(),
                'mensaje_usuario' => __('Error al crear la actividad inicial'),
                'respuesta' => $e->getMessage(),
                'code' => 500
            ];
        }
    }

    public function agregarActividad(Request $request, $id)
    {
        $resultado = $this->crearActividadInicial($id);

        if (!$resultado['success']) {
            return response()->json($resultado, $resultado['code']);
        }

        $actividad = $resultado['respuesta'];

        if ($request->ajax()) {
            try {
                $currencies = Currency::where('status', 'Active')->get();

                // Retornamos el HTML de la fila para insertarlo directamente
                $html = view('reda-alojamiento::experiencia.experiencias.formularios_de_pasos.partials.fila_actividad', compact('actividad', 'currencies'))->render();

                $respuesta = [
                    'success' => true,
                    'message' => 'Activity added successfully',
                    'mensaje_usuario' => __('Actividad agregada exitosamente'),
                    'respuesta' => [
                        'html' => $html,
                        'id' => $actividad->id
                    ],
                    'code' => 200
                ];
                return response()->json($respuesta, $respuesta['code']);
            } catch (\Exception $e) {
                $respuesta = [
                    'success' => false,
                    'message' => 'Error rendering activity: ' . $e->getMessage(),
                    'mensaje_usuario' => __('Error al preparar la actividad agregada'),
                    'respuesta' => $e->getMessage(),
                    'code' => 500
                ];
                return response()->json($respuesta, $respuesta['code']);
            }
        }
    }

    public function getActividadForm(Request $request, $id)
    {
        try {
            $actividad = ActividadExperiencia::findOrFail($id);
            $currencies = Currency::where('status', 'Active')->get();
            $readonly = $request->get('mode') === 'view';

            if ($request->ajax()) {
                $html = view('reda-alojamiento::experiencia.experiencias.formularios_de_pasos.partials.fila_actividad', compact('actividad', 'currencies', 'readonly'))->render();

                $respuesta = [
                    'success' => true,
                    'message' => 'Activity form retrieved successfully',
                    'mensaje_usuario' => __('Formulario de actividad recuperado con éxito'),
                    'respuesta' => [
                        'html' => $html,
                        'id' => $actividad->id,
                        'readonly' => $readonly
                    ],
                    'code' => 200
                ];
                return response()->json($respuesta, $respuesta['code']);
            }
        } catch (\Exception $e) {
            $respuesta = [
                'success' => false,
                'message' => 'Error getting activity form: ' . $e->getMessage(),
                'mensaje_usuario' => __('Error al recuperar el formulario de la actividad'),
                'respuesta' => $e->getMessage(),
                'code' => 500
            ];
            return response()->json($respuesta, $respuesta['code']);
        }
    }

    /**
     * Obtiene el detalle de una actividad para el frontend (Modal).
     */
    public function getActividadDetalle(Request $request, $id)
    {
        try {
            $actividad = ActividadExperiencia::with('currency')->findOrFail($id);

            if ($request->ajax()) {
                $html = view('reda-alojamiento::experiencia.experiencias.frontend.partials.detalle_actividad', compact('actividad'))->render();

                $respuesta = [
                    'success' => true,
                    'message' => 'Activity detail retrieved successfully',
                    'mensaje_usuario' => __('Detalle de la actividad recuperado con éxito'),
                    'respuesta' => [
                        'html' => $html,
                        'id' => $actividad->id
                    ],
                    'code' => 200
                ];
                return response()->json($respuesta, $respuesta['code']);
            }
        } catch (\Exception $e) {
            $respuesta = [
                'success' => false,
                'message' => 'Error getting activity detail: ' . $e->getMessage(),
                'mensaje_usuario' => __('Error al recuperar el detalle de la actividad'),
                'respuesta' => $e->getMessage(),
                'code' => 500
            ];
            return response()->json($respuesta, $respuesta['code']);
        }
    }

    public function deleteActividad($id)
    {
        try {
            $actividad = ActividadExperiencia::find($id);

            if (!$actividad) {
                $respuesta = [
                    'success' => false,
                    'message' => 'Product or service not found',
                    'mensaje_usuario' => __('Producto o servicio no encontrado.'),
                    'respuesta' => '',
                    'code' => 404
                ];
                return response()->json($respuesta, $respuesta['code']);
            }

            $directoryPath = public_path('images/actividades_experiencias/' . $id);

            // Eliminamos el directorio completo y su contenido
            if (File::isDirectory($directoryPath)) {
                File::deleteDirectory($directoryPath);
            }

            // Eliminamos el registro de la base de datos
            $actividad->delete();

            $respuesta = [
                'success' => true,
                'message' => 'Product or service and files deleted correctly',
                'mensaje_usuario' => __('¡Producto o servicio y sus archivos eliminados correctamente!'),
                'respuesta' => '',
                'code' => 200
            ];
            return response()->json($respuesta, $respuesta['code']);

        } catch (\Exception $e) {
            $respuesta = [
                'success' => false,
                'message' => 'Error deleting activity: ' . $e->getMessage(),
                'mensaje_usuario' => __('Error al eliminar el producto o servicio'),
                'respuesta' => $e->getMessage(),
                'code' => 500
            ];
            return response()->json($respuesta, $respuesta['code']);
        }
    }
    public function destroy($id)
    {
        $experiencia = Experiencia::with(['actividades', 'fotos'])->find($id);

        if (!$experiencia) {
            $respuesta = [
                'success' => false,
                'message' => 'Experience not found',
                'mensaje_usuario' => __('Experiencia no encontrada.'),
                'respuesta' => '',
                'code' => 404
            ];
            return response()->json($respuesta, $respuesta['code']);
        }

        // Seguridad: Verificar dueño
        if ($experiencia->user_id != Auth::id()) {
            $respuesta = [
                'success' => false,
                'message' => 'Unauthorized user',
                'mensaje_usuario' => __('Usuario no autorizado.'),
                'respuesta' => '',
                'code' => 403
            ];
            return response()->json($respuesta, $respuesta['code']);
        }

        DB::beginTransaction();
        try {
            // --- 1. PREPARAR RUTAS DE ARCHIVOS ---

            // Ruta de la carpeta principal de la experiencia (donde están las fotos de fotos_experiencias)
            $pathExperiencia = public_path('images/experiencias/' . $id);

            // Rutas de carpetas de actividades (cada actividad tiene su propia carpeta por ID)
            $actividadesIds = $experiencia->actividades->pluck('id')->toArray();

            // --- 2. ELIMINAR ARCHIVOS FÍSICOS ---

            // A. Borrar carpeta principal de la experiencia
            if (File::isDirectory($pathExperiencia)) {
                File::deleteDirectory($pathExperiencia);
            }

            // B. Borrar carpetas de cada actividad relacionada
            foreach ($actividadesIds as $actividadId) {
                $pathActividad = public_path('images/actividades_experiencias/' . $actividadId);
                if (File::isDirectory($pathActividad)) {
                    File::deleteDirectory($pathActividad);
                }
            }

            // --- 3. ELIMINAR REGISTROS DE BASE DE DATOS ---

            $experiencia->delete();

            DB::commit();

            $respuesta = [
                'success' => true,
                'message' => 'Experience deleted successfully',
                'mensaje_usuario' => __('Experiencia eliminada con éxito.'),
                'respuesta' => '',
                'code' => 200
            ];
            return response()->json($respuesta, $respuesta['code']);

        } catch (\Exception $e) {
            DB::rollBack();
            $respuesta = [
                'success' => false,
                'message' => 'Technical error deleting experience: ' . $e->getMessage(),
                'mensaje_usuario' => __('Error técnico al eliminar la experiencia'),
                'respuesta' => $e->getMessage(),
                'code' => 500
            ];
            return response()->json($respuesta, $respuesta['code']);
        }
    }

    public function actualizarPreciosLote(Request $request)
    {
        try {
            $ids = $request->ids;
            $tipoCambio = $request->tipo_cambio; // 'aumento' o 'disminucion'
            $porcentaje = floatval($request->porcentaje);
            $preciosAfectar = $request->precios_afectar; // Array: ['general', 'bolivares', 'promocion']

            if (empty($ids) || !is_array($ids)) {
                $respuesta = [
                    'success' => false,
                    'message' => 'No activities selected',
                    'mensaje_usuario' => __('Debe seleccionar al menos una actividad'),
                    'respuesta' => '',
                    'code' => 400
                ];
                return response()->json($respuesta, $respuesta['code']);
            }

            $factor = ($tipoCambio === 'aumento') ? (1 + ($porcentaje / 100)) : (1 - ($porcentaje / 100));

            $actividades = ActividadExperiencia::whereIn('id', $ids)->get();

            foreach ($actividades as $actividad) {
                $datosComplementarios = json_decode($actividad->precios_monedas_complementarios, true) ?: [];

                // 1. Precio General
                if (in_array('general', $preciosAfectar) && $actividad->precio) {
                    $actividad->precio = round($actividad->precio * $factor, 2);
                }

                // 2. Precio Pago en Bolívares
                if (in_array('bolivares', $preciosAfectar) && isset($datosComplementarios['precio_pago_bolivares'])) {
                    $datosComplementarios['precio_pago_bolivares'] = round($datosComplementarios['precio_pago_bolivares'] * $factor, 2);
                }

                // 3. Precio Promoción
                if (in_array('promocion', $preciosAfectar) && isset($datosComplementarios['precio_promocion'])) {
                    $datosComplementarios['precio_promocion'] = round($datosComplementarios['precio_promocion'] * $factor, 2);
                }

                $actividad->precios_monedas_complementarios = json_encode($datosComplementarios);
                $actividad->save();
            }

            $respuesta = [
                'success' => true,
                'message' => 'Prices updated successfully in bulk',
                'mensaje_usuario' => __('Precios actualizados con éxito para las actividades seleccionadas'),
                'respuesta' => '',
                'code' => 200
            ];
            return response()->json($respuesta, $respuesta['code']);

        } catch (\Exception $e) {
            $respuesta = [
                'success' => false,
                'message' => 'Error updating prices in bulk: ' . $e->getMessage(),
                'mensaje_usuario' => __('Error al actualizar los precios en lote'),
                'respuesta' => $e->getMessage(),
                'code' => 500
            ];
            return response()->json($respuesta, $respuesta['code']);
        }
    }

    public function guardarHorario(Request $request, $id)
    {
        try {
            $experiencia = Experiencia::findOrFail($id);
            $horarios = $experiencia->horarios ?? [];

            $validator = Validator::make($request->all(), [
                'dias' => 'required|array|min:1',
                'bloques' => 'required|array|min:1',
                'bloques.*.hora_desde' => 'required',
                'bloques.*.ampm_desde' => 'required',
                'bloques.*.hora_hasta' => 'required',
                'bloques.*.ampm_hasta' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'mensaje_usuario' => __('Por favor complete todos los campos del horario.'),
                    'respuesta' => $validator->errors(),
                    'code' => 422
                ], 422);
            }

            $nuevoHorario = [
                'dias' => array_values($request->dias),
                'bloques' => array_values($request->bloques)
            ];

            if ($request->has('index') && $request->index !== null && $request->index !== '') {
                $horarios[$request->index] = $nuevoHorario;
            } else {
                $horarios[] = $nuevoHorario;
            }

            $experiencia->horarios = $horarios;
            $experiencia->save();

            return response()->json([
                'success' => true,
                'message' => 'Schedule saved successfully',
                'mensaje_usuario' => __('Horario guardado con éxito'),
                'respuesta' => $horarios,
                'code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'mensaje_usuario' => __('Error al guardar el horario'),
                'respuesta' => '',
                'code' => 500
            ], 500);
        }
    }

    public function eliminarHorario(Request $request, $id, $index)
    {
        try {
            $experiencia = Experiencia::findOrFail($id);
            $horarios = $experiencia->horarios ?? [];

            if (isset($horarios[$index])) {
                unset($horarios[$index]);
                $horarios = array_values($horarios);
                $experiencia->horarios = $horarios;
                $experiencia->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Schedule deleted successfully',
                'mensaje_usuario' => __('Horario eliminado con éxito'),
                'respuesta' => $horarios,
                'code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'mensaje_usuario' => __('Error al eliminar el horario'),
                'respuesta' => '',
                'code' => 500
            ], 500);
        }
    }
}
