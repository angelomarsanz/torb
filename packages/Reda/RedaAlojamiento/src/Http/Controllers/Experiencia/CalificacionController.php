<?php

namespace Reda\RedaAlojamiento\src\Http\Controllers\Negocios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Reda\RedaAlojamiento\Models\Experiencia\CalificacionExperiencia;
use Illuminate\Support\Facades\Auth;

class CalificacionController extends Controller
{
    public function guardarCalificacion(Request $peticion)
    {
        try {
            $datosValidados = $peticion->validate([
                'experiencia_id' => 'required|integer',
                'estrellas'  => 'required|integer|min:1|max:5',
                'comentario' => 'nullable|string|max:1000',
            ]);

            $nuevaCalificacion = CalificacionExperiencia::create([
                'experiencia_id' => $datosValidados['experiencia_id'],
                'user_id' => Auth::id(),
                'estrellas'  => $datosValidados['estrellas'],
                'comentario' => $datosValidados['comentario'],
            ]);

            $respuesta = [
                'success' => true,
                'message' => 'Calificación guardada',
                'mensaje_usuario' => __('Calificación enviada con éxito'),
                'respuesta' => $nuevaCalificacion,
                'code' => 200
            ];
        } catch (\Exception $e) {
            $respuesta = [
                'success' => false,
                'message' => $e->getMessage(),
                'mensaje_usuario' => __('Error al procesar su calificación'),
                'respuesta' => '',
                'code' => 400
            ];
        }

        return response()->json($respuesta, $respuesta['code']);
    }
}
