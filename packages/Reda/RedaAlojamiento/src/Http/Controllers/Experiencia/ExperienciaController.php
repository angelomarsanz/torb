<?php

namespace Reda\RedaAlojamiento\Http\Controllers\Experiencia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
                        'actividades.*.descripcion_actividad' => 'required|string',
                    ], [
                        'actividades.*.orden_actividad.min' => 'El orden de la actividad debe ser al menos 1.',
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
                                    'descripcion_actividad' => $datos['descripcion_actividad'],
                                    'orden_actividad'       => $datos['orden_actividad'],
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
    // Agrega este método al controlador
    public function agregarActividad($id)
    {
        // 1. Obtener el último número de orden para esta experiencia
        $ultimoOrden = ActividadExperiencia::where('experiencia_id', $id)
            ->max('orden_actividad') ?? 0;

        // 2. Crear la nueva actividad en blanco
        ActividadExperiencia::create([
            'experiencia_id' => $id,
            'orden_actividad' => $ultimoOrden + 1,
            'descripcion_actividad' => '',
            'foto_actividad' => null
        ]);

        // 3. Redireccionar de vuelta con un mensaje (esto recargará la lista)
        return back()->with('success', 'Nueva actividad añadida.');
    }
}