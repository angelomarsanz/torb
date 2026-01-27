<?php

namespace Reda\RedaAlojamiento\Http\Controllers\Experiencia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Reda\RedaAlojamiento\Models\Experiencia\{
    Experiencia,
    ActividadExperiencia,
    HorarioExperiencia,
    InformacionExperiencia,
    AnfitrionExperiencia
};
use Auth;
use Reda\RedaAlojamiento\Models\Experiencia\FotoExperiencia;
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

    public function formularioDePasos(Request $request)
    {
        $id = $request->id;
        $paso = $request->paso;
        $result = Experiencia::findOrFail($id);

        if ($request->isMethod('get')) {
            return view("reda-alojamiento::experiencia.experiencias.formularios_de_pasos.$paso", compact('result', 'paso'));
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
                    if ($request->hasFile('photos')) {
                        $path = public_path('images/experiencias/' . $id);
                        if (!File::isDirectory($path)) {
                            File::makeDirectory($path, 0777, true, true);
                        }
                
                        foreach ($request->file('photos') as $file) {
                            $fileName = time() . '_' . $file->getClientOriginalName();
                            $file->move($path, $fileName);
                
                            $foto = new FotoExperiencia;
                            $foto->experiencia_id = $id;
                            $foto->photo = $fileName;
                            $foto->serial = 0;
                            $foto->cover_photo = 0;
                            $foto->save();
                        }
                    }
                    return redirect()->route('reda.experiencias.pasos', ['id' => $id, 'paso' => 'actividades']);
                
                // Agregar el resto de los casos: actividades, ubicacion, etc.
            }
        }
    }
}