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
    FotoExperiencia
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
        $data['experiencias'] = Experiencia::with('fotos') // <-- Agregamos las fotos aquí
                            ->where('user_id', Auth::id())
                            ->orderBy('id', 'desc')
                            ->paginate(Session::get('row_per_page') ?? 10);

        // Necesitamos la moneda para mostrar los precios
        $data['currentCurrency'] = \App\Http\Helpers\Common::getCurrentCurrency();

        return view('reda-alojamiento::experiencia.experiencias.index', $data);
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

            return redirect()->route('reda.experiencias.pasos', ['id' => $experiencia->id, 'paso' => 'descripcion']);
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
                    ];
                }

                Log::info("Ubicación normalizada para la vista: " . print_r($result->ubicacion, true));
            }

            Log::info("formularioDePasosExperiencias, actividades: " . print_r($actividades, true));

            return view("reda-alojamiento::experiencia.experiencias.formularios_de_pasos.$paso",
                compact('result', 'paso', 'actividades', 'categoriasNegocios', 'country'));
        }
        elseif ($request->isMethod('post')) {
            switch ($paso) {
                case 'descripcion':
                    $request->validate(
                        [
                            'titulo' => 'required|min:5',
                            'descripcion' => 'required|min:20',
                            'categoria_negocio' => 'required'
                        ],
                        [
                            'titulo.required' => __('El nombre del negocio es obligatorio.'),
                            'titulo.min'      => __('El nombre del negocio debe tener al menos 5 caracteres.'),
                            'descripcion.required' => __('La descripción es obligatoria.'),
                            'descripcion.min'      => __('La descripción debe tener al menos 20 caracteres.'),
                            'categoria_negocio.required' => __('La categoría del negocio es obligatoria.'),
                        ]);
                    $result->titulo = $request->titulo;
                    $result->descripcion = $request->descripcion;
                    $result->categoria_negocio = $request->categoria_negocio; // Guardamos el valor
                    $result->save();

                    return redirect()->route('reda.experiencias.pasos', ['id' => $id, 'paso' => 'fotos']);

                case 'fotos':
                    $conteoFotos = FotoExperiencia::where('experiencia_id', $id)->count();

                    if ($conteoFotos == 0) {
                        return back()->withErrors(['foto' => __('La foto es obligatoria.')]);
                    }

                    return redirect()->route('reda.experiencias.pasos', ['id' => $id, 'paso' => 'actividades']);

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
                        return redirect()->route('reda.experiencias.pasos', ['id' => $id, 'paso' => 'actividades'])
                                    ->with('success', __('Producto o servicio guardado con éxito'));
                    }

                    return redirect()->route('reda.experiencias.pasos', ['id' => $id, 'paso' => 'ubicacion'])
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
                            ],
                            [
                                'map_search.required'     => __('Búsqueda en el mapa obligatoria'),
                                'address_line_1.required' => __('Dirección obligatoria'),
                                'country.required'        => __('País obligatorio'),
                                'city.required'           => __('Ciudad obligatoria'),
                                'state.required'          => __('Estado obligatorio'),
                                'latitude.not_in'         => __('Debe fijar la posición en el mapa'),
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
                        ];

                        $result->ubicacion = $ubicacion;
                        $result->save();

                        if ($request->ajax()) {
                            $respuesta = [
                                'success' => true,
                                'message' => 'Location updated successfully',
                                'mensaje_usuario' => __('Ubicación actualizada con éxito.'),
                                'respuesta' => route('reda.experiencias.pasos', ['id' => $id, 'paso' => 'horario']),
                                'code' => 200
                            ];
                            return response()->json($respuesta, $respuesta['code']);
                        }

                        return redirect()->route('reda.experiencias.pasos', ['id' => $id, 'paso' => 'horario'])
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
                    return redirect()->route('reda.experiencias.pasos', ['id' => $id, 'paso' => 'anfitrion'])
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
                    return redirect()->route('reda.experiencias.pasos', ['id' => $id, 'paso' => 'informacion_adicional'])
                    ->with('success', __('Información de Nosotros actualizada con éxito'));
                case 'informacion_adicional':
                    $informacion = InformacionExperiencia::where('experiencia_id', $id)->first();
                    if ($informacion) {
                        $informacion->requisitos_viajero = $request->requisitos_viajero;
                        $informacion->save();
                    }

                    return redirect()->route('reda.experiencias.pasos', ['id' => $id, 'paso' => 'precio'])
                    ->with('success', __('Información adicional actualizada con éxito'));
                case 'precio':
                    return redirect()->route('reda.experiencias.index')
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

            if ($request->ajax()) {
                $html = view('reda-alojamiento::experiencia.experiencias.formularios_de_pasos.partials.fila_actividad', compact('actividad', 'currencies'))->render();

                $respuesta = [
                    'success' => true,
                    'message' => 'Activity form retrieved successfully',
                    'mensaje_usuario' => __('Formulario de actividad recuperado con éxito'),
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
                'message' => 'Error getting activity form: ' . $e->getMessage(),
                'mensaje_usuario' => __('Error al recuperar el formulario de la actividad'),
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
                return response()->json([
                    'success' => false,
                    'message' => 'No activities selected',
                    'mensaje_usuario' => __('Debe seleccionar al menos una actividad'),
                    'respuesta' => '',
                    'code' => 400
                ], 400);
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

            return response()->json([
                'success' => true,
                'message' => 'Prices updated successfully in bulk',
                'mensaje_usuario' => __('Precios actualizados con éxito para las actividades seleccionadas'),
                'respuesta' => '',
                'code' => 200
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating prices in bulk: ' . $e->getMessage(),
                'mensaje_usuario' => __('Error al actualizar los precios en lote'),
                'respuesta' => $e->getMessage(),
                'code' => 500
            ], 500);
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
