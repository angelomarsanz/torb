<?php

namespace Reda\RedaAlojamiento\Http\Controllers\Experiencia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Reda\RedaAlojamiento\Models\Experiencia\CalificacionExperiencia;
use Reda\RedaAlojamiento\Models\Experiencia\Experiencia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PDF; // niklasravnsborg/laravel-pdf

class CalificacionController extends Controller
{
    /**
     * Muestra el formulario de calificación para un comercio específico.
     */
    public function calificacionExperienciaFrontend($id)
    {
        $experiencia = Experiencia::with(['fotos' => function($q) {
            $q->where('cover_photo', 1);
        }])->findOrFail($id);

        return view('reda-alojamiento::experiencia.experiencias.frontend.calificacion_experiencia_frontend', compact('experiencia'));
    }

    /**
     * Muestra el listado de negocios del usuario para gestionar sus códigos QR.
     */
    public function indexQR()
    {
        $userId = Auth::id();
        $experiencias = Experiencia::where('user_id', $userId)
            ->with(['fotos' => function($q) {
                $q->where('cover_photo', 1);
            }])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('reda-alojamiento::experiencia.experiencias.calificacion_experiencia', compact('experiencias'));
    }

    /**
     * Muestra todas las calificaciones recibidas por los negocios del usuario.
     */
    public function listadoDuenio()
    {
        $userId = Auth::id();

        // Obtenemos los IDs de los negocios del usuario
        $negociosIds = Experiencia::where('user_id', $userId)->pluck('id');

        // Obtenemos las calificaciones vinculadas a esos negocios
        $calificaciones = CalificacionExperiencia::whereIn('experiencia_id', $negociosIds)
            ->with(['experiencia', 'usuario'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('reda-alojamiento::experiencia.experiencias.listado_calificaciones', compact('calificaciones'));
    }

    /**
     * Genera un PDF con el cartel de calificación del comercio.
     */
    public function descargarCartel($id)
    {
        $experiencia = Experiencia::findOrFail($id);

        if ($experiencia->user_id != Auth::id()) {
            abort(403);
        }

        // Generamos la URL de calificación para el QR
        $urlCalificar = route('reda.negocios.experiencias.calificar', $id);

        // Diseñamos el PDF (usando la vista que crearemos)
        $pdf = PDF::loadView('reda-alojamiento::experiencia.experiencias.cartel_calificacion_pdf', [
            'experiencia' => $experiencia,
            'urlCalificar' => $urlCalificar
        ], [], [
            'format' => 'A4',
            'display_mode' => 'fullpage',
        ]);

        $nombreArchivo = 'Cartel_Calificacion_' . str_replace(' ', '_', $experiencia->titulo) . '.pdf';

        return $pdf->download($nombreArchivo);
    }

    /**
     * Guarda la calificación enviada por el usuario.
     */
    public function guardarCalificacion(Request $peticion)
    {
        try {
            $datosValidados = $peticion->validate([
                'experiencia_id' => 'required|integer',
                'estrellas'      => 'required|integer|min:1|max:5',
                'comentario'     => 'nullable|string|max:1000',
            ]);

            // Verificar si el usuario ya calificó este negocio (opcional, pero recomendado)
            $yaCalifico = CalificacionExperiencia::where('experiencia_id', $datosValidados['experiencia_id'])
                ->where('user_id', Auth::id())
                ->exists();

            if ($yaCalifico) {
                return response()->json([
                    'success' => false,
                    'message' => 'User already rated this business',
                    'mensaje_usuario' => __('Ya has calificado este negocio anteriormente'),
                    'respuesta' => '',
                    'code' => 400
                ], 400);
            }

            $nuevaCalificacion = CalificacionExperiencia::create([
                'experiencia_id' => $datosValidados['experiencia_id'],
                'user_id'        => Auth::id(),
                'estrellas'      => $datosValidados['estrellas'],
                'comentario'     => $datosValidados['comentario'],
            ]);

            $respuesta = [
                'success' => true,
                'message' => 'Calificación guardada',
                'mensaje_usuario' => __('¡Gracias! Tu calificación ha sido enviada con éxito'),
                'respuesta' => $nuevaCalificacion,
                'code' => 200
            ];
        } catch (\Exception $e) {
            Log::error("Error guardando calificación: " . $e->getMessage());
            $respuesta = [
                'success' => false,
                'message' => $e->getMessage(),
                'mensaje_usuario' => __('Hubo un error al procesar tu calificación. Por favor, intenta de nuevo.'),
                'respuesta' => '',
                'code' => 400
            ];
        }

        return response()->json($respuesta, $respuesta['code']);
    }
}
