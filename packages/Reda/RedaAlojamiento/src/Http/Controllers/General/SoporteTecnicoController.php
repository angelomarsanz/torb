<?php

namespace Reda\RedaAlojamiento\Http\Controllers\General;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Reda\RedaAlojamiento\Models\Admin\SoporteTecnico;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SoporteTecnicoController extends Controller
{
    /**
     * Guarda un nuevo ticket de soporte técnico.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $datosValidados = $request->validate([
                'mensaje'   => 'required|string|min:10',
                'prioridad' => 'required|string|in:Baja,Media,Alta,Urgente',
                'tema'      => 'required|string',
                'link_error'=> 'nullable|string',
            ]);

            $ticket = SoporteTecnico::create([
                'user_id'         => Auth::id(),
                'tema'            => $datosValidados['tema'],
                'mensaje_usuario' => $datosValidados['mensaje'],
                'prioridad'       => $datosValidados['prioridad'],
                'link_error'      => $datosValidados['link_error'],
                'estatus'         => 'Abierto', // Estatus inicial por defecto
                'visto_por_admin' => false,
                'visto_por_usuario' => true,
            ]);

            $respuesta = [
                'success' => true,
                'message' => __('Ticket de soporte creado'),
                'mensaje_usuario' => __('Ticket de soporte creado con éxito'),
                'respuesta' => $ticket,
                'code' => 200
            ];
        } catch (\Exception $e) {
            Log::error("Error creando ticket de soporte: " . $e->getMessage());
            $respuesta = [
                'success' => false,
                'message' => $e->getMessage(),
                'mensaje_usuario' => __('Error al crear el ticket de soporte'),
                'respuesta' => '',
                'code' => 400
            ];
        }

        return response()->json($respuesta, $respuesta['code']);
    }
}
