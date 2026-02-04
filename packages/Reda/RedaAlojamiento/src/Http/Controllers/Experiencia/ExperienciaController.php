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

    public function formularioDePasos(Request $request)
    {
        $id = $request->id;
        $paso = $request->paso;
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
                    return redirect()->route('reda.experiencias.pasos', ['id' => $id, 'paso' => 'actividades']);
                
                // Agregar el resto de los casos: actividades, ubicacion, etc.
            }
        }
    }

    public function deletePhotoExperiencia(Request $request) {
        $foto = FotoExperiencia::find($request->photo_id);
        if ($foto) {
            $path = public_path('images/experiencias/' . $foto->experiencia_id . '/' . $foto->photo);
            if (File::exists($path)) {
                File::delete($path);
            }
            $foto->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Foto no encontrada'], 404);
    }

    public function makeDefaultPhotoExperiencia(Request $request) {
        // Poner todas en 0
        FotoExperiencia::where('experiencia_id', $request->experiencia_id)->update(['cover_photo' => 0]);
        // Poner la seleccionada en 1
        $foto = FotoExperiencia::find($request->photo_id);
        $foto->cover_photo = 1;
        $foto->save();
        
        return response()->json(['success' => true]);
    }

    public function uploadPhotoExperiencia(Request $request, $id) {
        if ($request->hasFile('cropped_image')) {
            $path = public_path('images/experiencias/' . $id);
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0777, true, true);
            }

            $file = $request->file('cropped_image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move($path, $fileName);

            $foto = new FotoExperiencia;
            $foto->experiencia_id = $id;
            $foto->photo = $fileName;
            $foto->serial = FotoExperiencia::where('experiencia_id', $id)->count() + 1;
            
            // Si es la primera foto, marcar como portada
            $foto->cover_photo = ($foto->serial == 1) ? 1 : 0;
            $foto->save();

            return response()->json([
                'success' => true,
                'message' => 'Foto subida correctamente',
                'photo_id' => $foto->id,
                'path' => asset('images/experiencias/'.$id.'/'.$fileName)
            ]);
        }
        return response()->json(['success' => false, 'message' => 'No se pudo subir la foto'], 400);
    }

    public function cropPhotoExperiencia(Request $request)
    {
        // Validación básica
        $request->validate([
            'photo_id' => 'required|exists:fotos_experiencias,id',
            'cropped_image' => 'required|max:5120',
        ]);
    
        $foto = FotoExperiencia::find($request->photo_id);
    
        if ($foto && $request->hasFile('cropped_image')) {
            // Definir la ruta física (donde se guarda)
            $path = public_path('images/experiencias/' . $foto->experiencia_id);
            
            // Mantener el nombre de archivo original para sobreescribirlo
            $fileName = $foto->photo;
            $file = $request->file('cropped_image');
            
            // Asegurar que el directorio existe
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0777, true, true);
            }

            // Eliminar físicamente la imagen anterior para evitar problemas de caché
            if (File::exists($path . '/' . $fileName)) {
                File::delete($path . '/' . $fileName);
            }
    
            // Mover el archivo (esto reemplaza la foto vieja físicamente)
            $file->move($path, $fileName);
    
            // Generar la URL pública correcta para devolver al JS
            // Usamos asset() que apunta a la carpeta public
            $newUrl = asset('images/experiencias/' . $foto->experiencia_id . '/' . $fileName) . '?v=' . time();
    
            return response()->json([
                'success' => true, 
                'message' => 'Foto actualizada correctamente',
                'path' => $newUrl // Esto sirve para actualizar el src en el JS si decides no recargar
            ]);
        }
        return response()->json(['success' => false, 'message' => 'No se pudo procesar la imagen'], 400);
    }
}