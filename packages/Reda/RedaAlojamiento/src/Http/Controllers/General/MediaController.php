<?php

namespace Reda\RedaAlojamiento\Http\Controllers\General;

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

class MediaController extends Controller
{
    public function uploadPhoto(Request $request, $id) {
        $request->validate(
            [
                'cropped_image' => 'required|image|mimes:jpg,jpeg,png,gif|max:25600',
            ],
            [
                'cropped_image.required' => __('reda-alojamiento::messages.general.la_foto_es_obligatoria'),
                'cropped_image.image'    => __('reda-alojamiento::messages.general.el_archivo_debe_ser_una_imagen'),
                'cropped_image.mimes' => __('reda-alojamiento::messages.general.solo_se_permiten_imagenes_jpg_jpeg_png_gif'),
                'cropped_image.max' => __('reda-alojamiento::messages.general.el_archivo_es_muy_pesado_máximo_25_mb'),
            ]);

        if ($request->hasFile('cropped_image')) {
            $origen = $request->origen;
            switch ($origen) {
                case 'fotos-experiencias':
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
                    break;

                case 'actividades-experiencias':
                    $actividadId = $id;
                    $actividad = ActividadExperiencia::find($actividadId);

                    if (!$actividad) {
                        return response()->json(['success' => false, 'message' => 'Actividad no encontrada'], 404);
                    }

                    // Definir ruta y nombre de archivo
                    $path = public_path('images/actividades_experiencias/' . $actividadId);

                    // Si ya existe una foto, eliminarla físicamente
                    if (!empty($actividad->foto_actividad)) {
                        $oldPath = public_path('images/actividades_experiencias/' . $actividad->foto_actividad);
                        if (File::exists($oldPath)) {
                            File::delete($oldPath);
                        }
                    }

                    // Crear directorio si no existe
                    if (!File::isDirectory($path)) {
                        File::makeDirectory($path, 0777, true, true);
                    }

                    // Guardar nueva foto
                    $file = $request->file('cropped_image');
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $fileName = time() . '_' . $originalName . '.' . $extension;
                    $file->move($path, $fileName);

                    // 5. Actualizar la base de datos
                    $actividad->foto_actividad = $actividadId.'/'.$fileName;
                    $actividad->save();

                    return response()->json([
                        'success' => true,
                        'message' => 'Foto de actividad actualizada',
                        'id' => $actividadId,
                        'path' => asset('public/images/actividades_experiencias/' . $actividadId.'/'.$fileName),
                        'file' => $actividadId.'/'.$fileName
                    ]);
                    break;

                case 'anfitrion-experiencia':
                    $anfitrionId = $id;
                    $anfitrion = AnfitrionExperiencia::find($anfitrionId);

                    if (!$anfitrion) {
                        return response()->json(['success' => false, 'message' => 'Anfitrión no encontrado'], 404);
                    }

                    // Definir ruta y nombre de archivo
                    $path = public_path('images/anfitriones_experiencias/' . $anfitrionId);

                    // Si ya existe una foto, eliminarla físicamente
                    if (!empty($anfitrion->foto_anfitrion)) {
                        $oldPath = public_path('images/anfitriones_experiencias/' . $anfitrion->foto_anfitrion);
                        if (File::exists($oldPath)) {
                            File::delete($oldPath);
                        }
                    }

                    // Crear directorio si no existe
                    if (!File::isDirectory($path)) {
                        File::makeDirectory($path, 0777, true, true);
                    }

                    // Guardar nueva foto
                    $file = $request->file('cropped_image');
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $fileName = time() . '_' . $originalName . '.' . $extension;
                    $file->move($path, $fileName);

                    // Actualizar la base de datos
                    $anfitrion->foto_anfitrion = $anfitrionId.'/'.$fileName;
                    $anfitrion->save();

                    return response()->json([
                        'success' => true,
                        'message' => 'Foto de anfitrión actualizada',
                        'id' => $anfitrionId,
                        'path' => asset('public/images/anfitriones_experiencias/' . $anfitrionId.'/'.$fileName),
                        'file' => $anfitrionId.'/'.$fileName
                    ]);
                    break;

                case 'logo-negocio':
                    $experienciaId = $id;
                    $experiencia = Experiencia::find($experienciaId);

                    if (!$experiencia) {
                        return response()->json(['success' => false, 'message' => 'Negocio no encontrado'], 404);
                    }

                    // Definir ruta y nombre de archivo
                    $path = public_path('images/logos_negocios/' . $experienciaId);

                    // Si ya existe un logo, eliminarlo físicamente
                    if (!empty($experiencia->ruta_imagenes)) {
                        $oldPath = public_path('images/logos_negocios/' . $experiencia->ruta_imagenes);
                        if (File::exists($oldPath)) {
                            File::delete($oldPath);
                        }
                    }

                    // Crear directorio si no existe
                    if (!File::isDirectory($path)) {
                        File::makeDirectory($path, 0777, true, true);
                    }

                    // Guardar nueva foto (Logo)
                    $file = $request->file('cropped_image');
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $fileName = time() . '_' . $originalName . '.' . $extension;
                    $file->move($path, $fileName);

                    // Actualizar la base de datos (columna ruta_imagenes)
                    $experiencia->ruta_imagenes = $experienciaId.'/'.$fileName;
                    $experiencia->save();

                    return response()->json([
                        'success' => true,
                        'message' => 'Logo de negocio actualizado',
                        'id' => $experienciaId,
                        'path' => asset('public/images/logos_negocios/' . $experienciaId.'/'.$fileName),
                        'file' => $experienciaId.'/'.$fileName
                    ]);
                    break;

            }
        }
        return response()->json(['success' => false, 'message' => 'No se pudo subir la foto'], 400);
    }

    public function cropPhoto(Request $request)
    {
        if ($request->hasFile('cropped_image')) {
            $origen = $request->origen;
            switch ($origen) {
                case 'fotos-experiencias':
                    // Validación básica
                    $request->validate(
                        [
                            'photo_id' => 'required|exists:fotos_experiencias,id',
                            'cropped_image' => 'required|max:25600',
                        ],
                        [
                            'photo_id.required' => __('reda-alojamiento::messages.general.el_id_de_la_foto_es_obligatorio'),
                            'photo_id.exists' => __('reda-alojamiento::messages.general.el_id_de_la_foto_no_existe_en_la_base_de_datos'),
                            'cropped_image.required' => __('reda-alojamiento::messages.general.la_foto_es_obligatoria'),
                            'cropped_image.max' => __('reda-alojamiento::messages.general.el_archivo_es_muy_pesado_máximo_25_mb'),
                        ]);

                    $foto = FotoExperiencia::find($request->photo_id);

                    if ($foto) {
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
                    break;

                case 'fotos-actividades':
                    // Lógica para actividades
                    break;
            }
        }
        return response()->json(['success' => false, 'message' => 'No se pudo procesar la imagen'], 400);
    }

    public function makeDefaultPhoto(Request $request) {
        $origen = $request->origen;
        switch ($origen) {
            case 'fotos-experiencias':
                // Poner todas en 0
                FotoExperiencia::where('experiencia_id', $request->experiencia_id)->update(['cover_photo' => 0]);
                // Poner la seleccionada en 1
                $foto = FotoExperiencia::find($request->photo_id);
                $foto->cover_photo = 1;
                $foto->save();

                return response()->json(['success' => true]);
                break;
            case 'actividades-experiencias':
                //
                break;
        }
    }

    public function deletePhoto(Request $request) {
        $origen = $request->origen;
        $foto = FotoExperiencia::find($request->photo_id);
        if ($foto) {
            $origen = $request->origen;
            switch ($origen) {
                case 'fotos-experiencias':
                    $path = public_path('images/experiencias/' . $foto->experiencia_id . '/' . $foto->photo);
                    if (File::exists($path)) {
                        File::delete($path);
                    }
                    $foto->delete();
                    return response()->json(['success' => true]);
                    break;

                case 'fotos-actividades':
                    // N/A
                    break;
            }
        }
        return response()->json(['success' => false, 'message' => 'Foto no encontrada'], 404);
    }
}
