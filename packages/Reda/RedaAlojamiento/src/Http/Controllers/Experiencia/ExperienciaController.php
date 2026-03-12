<?php

namespace Reda\RedaAlojamiento\Http\Controllers\Experiencia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

class ExperienciaController extends Controller
{
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
        // Cargamos la experiencia con TODAS sus relaciones de una sola vez
        $result = Experiencia::with([
            'fotos', 
            'actividades', 
            'horarios', 
            'informaciones', 
            'anfitrion'
        ])->findOrFail($id);

        $result = Experiencia::with(['fotos', 'actividades', 'horarios', 'informaciones', 'anfitrion'])->findOrFail($id);

        if ($request->isMethod('get')) {
            if ($paso === 'actividades') {
                $actividades = ActividadExperiencia::where('experiencia_id', $id)
                    ->orderBy('orden_actividad', 'asc')
                    ->paginate(10); // Paginación de 10 en 10
            }
    
            return view("reda-alojamiento::experiencia.experiencias.formularios_de_pasos.$paso", 
                compact('result', 'paso', 'actividades'));
        }        
        elseif ($request->isMethod('post')) {
            switch ($paso) {
                case 'descripcion':
                    $request->validate(
                        [
                            'titulo' => 'required|min:5',
                            'descripcion' => 'required|min:20'
                        ], 
                        [
                            'titulo.required' => __('reda-alojamiento::messages.general.el_nombre_del_negocio_es_obligatorio'),
                            'titulo.min'      => __('reda-alojamiento::messages.general.el_nombre_del_negocio_debe_tener_al_menos_5_caracteres'),
                            'descripcion.required' => __('reda-alojamiento::messages.general.la_descripcion_es_obligatoria'),
                            'descripcion.min'      => __('reda-alojamiento::messages.general.la_descripcion_debe_tener_al_menos_20_caracteres'),
                        ]);
                    $result->titulo = $request->titulo;
                    $result->descripcion = $request->descripcion;
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
                            'actividades.*.orden_actividad' => 'required|integer|min:1',
                            'actividades.*.nombre_actividad' => 'required|min:3',
                            'actividades.*.descripcion_actividad' => 'required|min:20',
                            'actividades.*.precio' => 'required|numeric|min:0.01',
                            
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

                            // Precio
                            'actividades.*.precio.required' => __('reda-alojamiento::messages.general.el_precio_es_obligatorio'),
                            'actividades.*.precio.numeric' => __('reda-alojamiento::messages.general.el_precio_debe_ser_un_numero_valido'),
                            'actividades.*.precio.min' => __('reda-alojamiento::messages.general.el_precio_debe_ser_mayor_a_cero'),
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
    public function agregarActividad(Request $request, $id)
    {
        $ultimoOrden = ActividadExperiencia::where('experiencia_id', $id)
            ->max('orden_actividad') ?? 0;
    
        $actividad = ActividadExperiencia::create([
            'experiencia_id' => $id,
            'orden_actividad' => $ultimoOrden + 1,
            'nombre_experiencia' => '', 
            'descripcion_actividad' => '',
            'precio' => null,           
            'currency_id' => null,      
            'disponibilidad' => 1,   // (1 para 'Sí' por defecto)
            'foto_actividad' => null
        ]);
    
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
}