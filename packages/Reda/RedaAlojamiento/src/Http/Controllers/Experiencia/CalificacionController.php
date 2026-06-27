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

        $esDuenio = (Auth::id() == $experiencia->user_id);

        return view('reda-alojamiento::experiencia.experiencias.frontend.calificacion_experiencia_frontend', compact('experiencia', 'esDuenio'));
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
     * Muestra el resumen de calificaciones por cada negocio del usuario.
     */
    public function listadoDuenio(Request $peticion)
    {
        $userId = Auth::id();
        $busqueda = $peticion->search;

        // Obtenemos los negocios del usuario con el promedio de estrellas y cantidad de calificaciones
        $negocios = Experiencia::where('user_id', $userId)
            ->when($busqueda, function ($query) use ($busqueda) {
                return $query->where('titulo', 'like', '%' . $busqueda . '%');
            })
            ->withCount('calificaciones')
            ->withAvg('calificaciones', 'estrellas')
            ->with(['fotos'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('reda-alojamiento::experiencia.experiencias.listado_calificaciones', compact('negocios', 'busqueda'));
    }

    /**
     * Obtiene los nombres de todos los comercios del usuario activo.
     */
    public function getNombresComercios()
    {
        $userId = Auth::id();
        $nombres = Experiencia::where('user_id', $userId)
            ->pluck('titulo')
            ->toArray();

        return response()->json([
            'success' => true,
            'message' => 'Nombres de comercios obtenidos',
            'mensaje_usuario' => '',
            'respuesta' => $nombres,
            'code' => 200
        ], 200);
    }

    /**
     * Muestra el detalle de calificaciones para un negocio específico.
     */
    public function detalleCalificacionesDuenio($id)
    {
        $experiencia = Experiencia::withAvg('calificaciones', 'estrellas')
            ->withCount('calificaciones')
            ->with(['fotos'])
            ->findOrFail($id);

        if ($experiencia->user_id != Auth::id()) {
            abort(403);
        }

        $calificaciones = CalificacionExperiencia::where('experiencia_id', $id)
            ->with(['usuario'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('reda-alojamiento::experiencia.experiencias.detalle_calificaciones', compact('experiencia', 'calificaciones'));
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

            $experiencia = Experiencia::findOrFail($datosValidados['experiencia_id']);

            // SEGURIDAD: El dueño no puede calificar su propio negocio
            if ($experiencia->user_id == Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Owner cannot rate their own business',
                    'mensaje_usuario' => __('Usted no puede calificar su propio negocio'),
                    'respuesta' => '',
                    'code' => 403
                ], 403);
            }

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

    /**
     * Elimina una calificación/reseña.
     * Esta acción suele ser ejecutada por un administrador desde el módulo de soporte.
     */
    public function destroy(Request $peticion, $id)
    {
        try {
            $calificacion = CalificacionExperiencia::findOrFail($id);
            $calificacion->delete();

            // Si viene un ticket_id en la petición, cerramos el ticket de soporte automáticamente
            if ($peticion->has('ticket_id')) {
                $ticket = \Reda\RedaAlojamiento\Models\Admin\SoporteTecnico::find($peticion->ticket_id);
                if ($ticket) {
                    $ticket->update([
                        'estatus' => 'Cerrado',
                        'resultado_gestion' => 'Reseña eliminada',
                        'fecha_cambio_estatus' => now(),
                        'mensaje_soporte_tecnico' => $ticket->mensaje_soporte_tecnico . "\n\n" . __('Acción automática: Reseña eliminada por el administrador.')
                    ]);
                }
            }

            $respuesta = [
                'success' => true,
                'message' => 'Calificación eliminada',
                'mensaje_usuario' => __('Reseña eliminada con éxito'),
                'respuesta' => '',
                'code' => 200
            ];
        } catch (\Exception $e) {
            Log::error("Error eliminando calificación: " . $e->getMessage());
            $respuesta = [
                'success' => false,
                'message' => $e->getMessage(),
                'mensaje_usuario' => __('Error al intentar eliminar la reseña'),
                'respuesta' => '',
                'code' => 400
            ];
        }

        return response()->json($respuesta, $respuesta['code']);
    }
}
