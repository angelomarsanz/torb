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
            $request->validate(['titulo' => 'required|max:255']);

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
                    $request->validate([
                        'titulo' => 'required|max:255',
                        'descripcion' => 'required|min:20'
                    ]);
                    $result->titulo = $request->titulo;
                    $result->descripcion = $request->descripcion;
                    $result->save();
                    
                    return redirect()->route('reda.experiencias.pasos', ['id' => $id, 'paso' => 'fotos']);
                
                case 'fotos':
                    return redirect()->route('reda.experiencias.pasos', ['id' => $id, 'paso' => 'actividades']);

                case 'actividades':
                    $request->validate([
                        'actividades.*.orden_actividad' => 'required|integer|min:1',
                        'actividades.*.descripcion_actividad' => 'required|string|min:5',
                    ], [
                        // Mensajes personalizados
                        'actividades.*.orden_actividad.required' => 'El número es obligatorio.',
                        'actividades.*.orden_actividad.min' => 'Debe ser mayor a cero.',
                        'actividades.*.descripcion_actividad.required' => 'La descripción es obligatoria.',
                    ]);
                
                    if ($request->has('actividades') && is_array($request->actividades)) {
                        foreach ($request->actividades as $id_actividad => $datos) {
                            $actividad = ActividadExperiencia::find($id_actividad);
                
                            if ($actividad) {
                                // VALIDACIÓN CRÍTICA:
                                if (empty($actividad->foto_actividad)) {
                                    // Importante: Usar back() con withErrors y asegurar que la clave sea correcta
                                    return back()->withErrors([
                                        "foto_actividad_id_" . $id_actividad => "Falta la foto en la actividad Nro. " . $datos['orden_actividad']
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
                                        ->with('success', 'Actividades actualizadas con éxito.');

                case 'ubicacion':
                    return redirect()->route('reda.experiencias.pasos', ['id' => $id, 'paso' => 'horario'])
                    ->with('success', 'Ubicación actualizada con éxito.');
                
                case 'horario':
                    return redirect()->route('reda.experiencias.pasos', ['id' => $id, 'paso' => 'precio'])
                    ->with('success', 'Horario actualizado con éxito.');

                case 'precio':
                    return redirect()->route('reda.experiencias.pasos', ['id' => $id, 'paso' => 'informacion_adicional'])
                    ->with('success', 'Precio actualizado con éxito.');

                case 'informacion_adicional':
                    return redirect()->route('reda.experiencias.pasos', ['id' => $id, 'paso' => 'anfitrion'])
                    ->with('success', 'Información actualizada con éxito.');

                case 'anfitrion':
                    return redirect()->route('reda.experiencias.pasos', ['id' => $id, 'paso' => 'anfitrion'])
                    ->with('success', 'Anfitrión actualizado con éxito.');
    
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
            return response()->json(['success' => false, 'message' => 'Actividad no encontrada'], 404);
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
                'message' => '¡Actividad y sus archivos eliminados correctamente!'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }        
    }
}