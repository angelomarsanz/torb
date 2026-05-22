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
                    'titulo.required' => __('reda-alojamiento::messages.general.el_nombre_del_negocio_es_obligatorio'),
                    'titulo.min'      => __('reda-alojamiento::messages.general.el_nombre_del_negocio_debe_tener_al_menos_5_caracteres'),
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

            Log::info("formularioDePasosExperiencias, actividades: " . print_r($actividades, true));

            return view("reda-alojamiento::experiencia.experiencias.formularios_de_pasos.$paso",
                compact('result', 'paso', 'actividades', 'categoriasNegocios'));
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
                            'titulo.required' => __('reda-alojamiento::messages.general.el_nombre_del_negocio_es_obligatorio'),
                            'titulo.min'      => __('reda-alojamiento::messages.general.el_nombre_del_negocio_debe_tener_al_menos_5_caracteres'),
                            'descripcion.required' => __('reda-alojamiento::messages.general.la_descripcion_es_obligatoria'),
                            'descripcion.min'      => __('reda-alojamiento::messages.general.la_descripcion_debe_tener_al_menos_20_caracteres'),
                            'categoria_negocio.required' => __('reda-alojamiento::messages.general.la_categoria_del_negocio_es_obligatoria'),
                        ]);
                    $result->titulo = $request->titulo;
                    $result->descripcion = $request->descripcion;
                    $result->categoria_negocio = $request->categoria_negocio; // Guardamos el valor
                    $result->save();

                    return redirect()->route('reda.experiencias.pasos', ['id' => $id, 'paso' => 'fotos']);

                case 'fotos':
                    $conteoFotos = FotoExperiencia::where('experiencia_id', $id)->count();

                    if ($conteoFotos == 0) {
                        return back()->withErrors(['foto' => __('reda-alojamiento::messages.general.la_foto_es_obligatoria')]);
                    }

                    return redirect()->route('reda.experiencias.pasos', ['id' => $id, 'paso' => 'actividades']);

                case 'actividades':
                    $request->validate(
                        [
                            'actividades' => 'required|array|min:1',
                            'actividades.*.orden_actividad' => 'required|integer|min:1',
                            'actividades.*.nombre_actividad' => 'required|min:3',
                            'actividades.*.descripcion_actividad' => 'required|min:20',
                            'actividades.*.tipo_producto_servicio' => 'required',
                            'actividades.*.precio' => 'required|numeric|min:0.01',
                            'actividades.*.currency_id' => 'required',
                            'actividades.*.disponibilidad' => 'required',
                        ],
                        [
                            // Número
                            'actividades.*.orden_actividad.required' => __('reda-alojamiento::messages.general.el_numero_de_la_actividad_es_obligatorio'),
                            'actividades.*.orden_actividad.integer' => __('reda-alojamiento::messages.general.el_numero_de_la_actividad_debe_ser_un_numero_valido'),
                            'actividades.*.orden_actividad.min' => __('reda-alojamiento::messages.general.el_numero_de_la_actividad_debe_ser_mayor_a_cero'),

                            // Nombre
                            'actividades.*.nombre_actividad.required' => __('reda-alojamiento::messages.general.el_nombre_del_producto_o_servicio_es_obligatorio'),
                            'actividades.*.nombre_actividad.min' => __('reda-alojamiento::messages.general.el_nombre_del_producto_o_servicio_debe_tener_al_menos_3_caracteres'),

                            // Descripción
                            'actividades.*.descripcion_actividad.required' => __('reda-alojamiento::messages.general.la_descripcion_es_obligatoria'),
                            'actividades.*.descripcion_actividad.min' => __('reda-alojamiento::messages.general.la_descripcion_debe_tener_al_menos_20_caracteres'),

                            // Tipo de producto o servicio
                            'actividades.*.tipo_producto_servicio.required' => __('reda-alojamiento::messages.general.el_tipo_producto_o_servicio_es_obligatorio'),

                            // Precio
                            'actividades.*.precio.required' => __('reda-alojamiento::messages.general.el_precio_es_obligatorio'),
                            'actividades.*.precio.numeric' => __('reda-alojamiento::messages.general.el_precio_debe_ser_un_numero_valido'),
                            'actividades.*.precio.min' => __('reda-alojamiento::messages.general.el_precio_debe_ser_mayor_a_cero'),

                            // Moneda
                            'actividades.*.currency_id.required' => __('reda-alojamiento::messages.general.el_tipo_de_moneda_es_obligatorio'),

                            // Disponibilidad
                            'actividades.*.disponibilidad.required' => __('reda-alojamiento::messages.general.debe_seleccionar_si_esta_disponible_o_no'),
                        ]);

                    if ($request->has('actividades') && is_array($request->actividades)) {
                        foreach ($request->actividades as $id_actividad => $datos) {
                            $actividad = ActividadExperiencia::find($id_actividad);

                            if ($actividad) {
                                if (empty($actividad->foto_actividad)) {
                                    return back()->withErrors([
                                        "foto_actividad_id_" . $id_actividad => __('reda-alojamiento::messages.general.falta_la_foto_en_la_actividad_nro') . $datos['orden_actividad']
                                    ])->withInput();
                                }

                                $actividad->update([
                                    'orden_actividad'       => $datos['orden_actividad'],
                                    'nombre_actividad'      => $datos['nombre_actividad'],
                                    'descripcion_actividad' => $datos['descripcion_actividad'],
                                    'tipo_producto_servicio'=> $datos['tipo_producto_servicio'],
                                    'precio'                => $datos['precio'],
                                    'currency_id'           => $datos['currency_id'],
                                    'disponibilidad'        => $datos['disponibilidad']
                                ]);
                            }
                        }
                    }

                    return redirect()->route('reda.experiencias.pasos', ['id' => $id, 'paso' => 'ubicacion'])
                                ->with('success', __('reda-alojamiento::messages.general.productos_y_servicios_actualizados_con_exito'));

                case 'ubicacion':
                    return redirect()->route('reda.experiencias.pasos', ['id' => $id, 'paso' => 'horario'])
                    ->with('success', __('reda-alojamiento::messages.general.ubicacion_actualizada_con_exito'));

                case 'horario':
                    return redirect()->route('reda.experiencias.pasos', ['id' => $id, 'paso' => 'precio'])
                    ->with('success', __('reda-alojamiento::messages.general.horario_actualizado_con_exito'));

                case 'precio':
                    return redirect()->route('reda.experiencias.pasos', ['id' => $id, 'paso' => 'informacion_adicional'])
                    ->with('success', __('reda-alojamiento::messages.general.precio_actualizado_con_exito'));

                case 'informacion_adicional':
                    return redirect()->route('reda.experiencias.pasos', ['id' => $id, 'paso' => 'anfitrion'])
                    ->with('success', __('reda-alojamiento::messages.general.informacion_adicional_actualizada_con_exito'));

                case 'anfitrion':
                    return redirect()->route('reda.experiencias.pasos', ['id' => $id, 'paso' => 'anfitrion'])
                    ->with('success', __('reda-alojamiento::messages.general.anfitrion_actualizado_con_exito'));
            }
        }
    }

    /**
     * Actualiza el orden de las actividades vía Ajax.
     */
    public function reordenarActividades(Request $request)
    {
        $orden = $request->orden; // Array de IDs en el nuevo orden
        if (is_array($orden)) {
            foreach ($orden as $index => $id) {
                ActividadExperiencia::where('id', $id)->update(['orden_actividad' => $index + 1]);
            }
            return response()->json(['success' => true, 'message' => __('reda-alojamiento::messages.general.orden_actualizado_con_exito')]);
        }
        return response()->json(['success' => false], 400);
    }

    /**
     * Crea una actividad con valores por defecto para una experiencia.
     */
    private function crearActividadInicial($experienciaId)
    {
        $ultimoOrden = ActividadExperiencia::where('experiencia_id', $experienciaId)
            ->max('orden_actividad') ?? 0;

        return ActividadExperiencia::create([
            'experiencia_id'          => $experienciaId,
            'orden_actividad'         => $ultimoOrden + 1,
            'nombre_actividad'        => '',
            'descripcion_actividad'   => '',
            'tipo_producto_servicio'  => null, // Nueva columna
            'precio'                  => null,
            'currency_id'             => null,
            'disponibilidad'          => null,
            'foto_actividad'          => null
        ]);
    }
    public function agregarActividad(Request $request, $id)
    {
        $actividad = $this->crearActividadInicial($id);

        if ($request->ajax()) {
            $currencies = Currency::where('status', 'Active')->get();

            // Retornamos el HTML de la fila para insertarlo directamente
            // Pasamos 'actividad' a una vista parcial o la renderizamos aquí
            $html = view('reda-alojamiento::experiencia.experiencias.formularios_de_pasos.partials.fila_actividad', compact('actividad', 'currencies'))->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'id' => $actividad->id
            ]);
        }
    }
    public function deleteActividad($id)
    {
        $actividad = ActividadExperiencia::find($id);

        if (!$actividad) {
            return response()->json(['success' => false, 'message' => __('reda-alojamiento::messages.general.producto_o_servicio_no_encontrado')], 404);
        }

        $directoryPath = public_path('images/actividades_experiencias/' . $id);

        try {
            // Eliminamos el directorio completo y su contenido
            if (File::isDirectory($directoryPath)) {
                File::deleteDirectory($directoryPath);
            }

            // Eliminamos el registro de la base de datos
            $actividad->delete();

            return response()->json([
                'success' => true,
                'message' => __('reda-alojamiento::messages.general.producto_o_servicio_y_sus_archivos_eliminados_correctamente')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('reda-alojamiento::messages.general.error_al_eliminar:') . $e->getMessage()
            ], 500);
        }
    }
    public function destroy($id)
    {
        $experiencia = Experiencia::with(['actividades', 'fotos'])->find($id);

        if (!$experiencia) {
            return response()->json([
                'success' => false,
                'message' => 'Experiencia no encontrada',
                'mensaje_usuario' => __('reda-alojamiento::messages.general.experiencia_no_encontrada'),
                'respuesta' => '',
                'code' => 404
            ], 404);
        }

        // Seguridad: Verificar dueño
        if ($experiencia->user_id != Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autorizado',
                'mensaje_usuario' => __('reda-alojamiento::messages.general.usuario_no_autorizado'),
                'respuesta' => '',
                'code' => 403
            ], 403);
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

            return response()->json([
                'success' => true,
                'message' => 'Experiencia eliminada',
                'mensaje_usuario' => __('reda-alojamiento::messages.general.experiencia_eliminada_con_exito'),
                'respuesta' => '',
                'code' => 200
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error técnico al eliminar',
                'mensaje_usuario' => __('reda-alojamiento::messages.general.error_tecnico_al_eliminar') . $e->getMessage(),
                'respuesta' => '',
                'code' => 500
            ], 500);
        }
    }
}
