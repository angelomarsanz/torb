<?php

namespace Reda\RedaAlojamiento\Http\Controllers\Admin\General;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Reda\RedaAlojamiento\Models\Admin\SoporteTecnico;
use Illuminate\Support\Facades\Log;

class SoporteTecnicoController extends Controller
{
    /**
     * Display the index page for technical support.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = SoporteTecnico::with('user');

        // Filtrado por ID puntual
        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        // Filtrado por nombre de usuario
        if ($request->filled('nombre_usuario')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->nombre_usuario . '%')
                  ->orWhere('last_name', 'like', '%' . $request->nombre_usuario . '%');
            });
        }

        // Filtrado por tema
        if ($request->filled('tema')) {
            $query->where('tema', $request->tema);
        }

        // Filtrado por prioridad
        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->prioridad);
        }

        // Filtrado por estatus
        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        // Filtrado por rango de fechas
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(10);

        // Obtener temas únicos para el filtro
        $temas = SoporteTecnico::select('tema')->distinct()->pluck('tema');

        return view('reda-alojamiento::admin.general.soporte_tecnico.index', compact('tickets', 'temas'));
    }

    /**
     * Display the details of a technical support ticket.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $ticket = SoporteTecnico::with('user')->findOrFail($id);
        
        // Verificamos si el recurso vinculado (ej: reseña) aún existe
        $ticket->recurso_existe = $ticket->verificarExistenciaRecurso();
        
        return view('reda-alojamiento::admin.general.soporte_tecnico.show', compact('ticket'));
    }

    /**
     * Cierra un ticket con un resultado específico.
     * Útil para acciones como "Mantener reseña".
     */
    public function cerrarTicket(Request $peticion, $id)
    {
        try {
            $ticket = SoporteTecnico::findOrFail($id);
            $resultado = $peticion->input('resultado', 'Gestionado');

            $ticket->update([
                'estatus' => 'Cerrado',
                'resultado_gestion' => $resultado,
                'fecha_cambio_estatus' => now(),
                'mensaje_soporte_tecnico' => $ticket->mensaje_soporte_tecnico . "\n\n" . __("Acción manual: Ticket cerrado con resultado: :resultado", ['resultado' => $resultado])
            ]);

            $respuesta = [
                'success' => true,
                'message' => 'Ticket cerrado',
                'mensaje_usuario' => __('El ticket ha sido cerrado con éxito'),
                'respuesta' => '',
                'code' => 200
            ];
        } catch (\Exception $e) {
            Log::error("Error cerrando ticket: " . $e->getMessage());
            $respuesta = [
                'success' => false,
                'message' => $e->getMessage(),
                'mensaje_usuario' => __('Error al intentar cerrar el ticket'),
                'respuesta' => '',
                'code' => 400
            ];
        }

        return response()->json($respuesta, $respuesta['code']);
    }
}
