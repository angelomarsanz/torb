<?php

namespace Reda\RedaAlojamiento\Http\Controllers\Admin\General;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Reda\RedaAlojamiento\Models\Admin\SoporteTecnico;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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
        $query = SoporteTecnico::with(['user', 'admin']); // Eager load user y gestor (admin)

        // Filtrado por ID puntual
        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        // Filtrado por nombre de usuario
        if ($request->filled('nombre_usuario')) {
            $nombreBusqueda = $request->nombre_usuario;
            $query->whereHas('user', function($q) use ($nombreBusqueda) {
                $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$nombreBusqueda%"]);
            });
        }

        // Filtrado por nombre de comercio (basado en el contenido de link_error)
        if ($request->filled('nombre_comercio')) {
            $nombreComercio = $request->nombre_comercio;

            // 1. Buscamos IDs de comercios (experiencias) con ese nombre
            $experienciasIds = \Reda\RedaAlojamiento\Models\Experiencia\Experiencia::where('titulo', 'LIKE', "%$nombreComercio%")->pluck('id');

            if ($experienciasIds->isNotEmpty()) {
                $query->where(function($q) use ($experienciasIds) {
                    foreach ($experienciasIds as $id) {
                        // Búsqueda por el nuevo atributo id_experiencia
                        $q->orWhere('link_error', 'LIKE', "%\"id_experiencia\":$id%")
                          ->orWhere('link_error', 'LIKE', "%\"id_experiencia\":\"$id\"%");
                    }

                    // 2. Fallback: Búsqueda por IDs de calificaciones (para tickets antiguos)
                    $calificacionesIds = \Reda\RedaAlojamiento\Models\Experiencia\CalificacionExperiencia::whereIn('experiencia_id', $experienciasIds)->pluck('id');
                    foreach ($calificacionesIds as $idReseña) {
                        $q->orWhere('link_error', 'LIKE', "%\"id_reseña\":$idReseña%")
                          ->orWhere('link_error', 'LIKE', "%\"id_de_la_reseña\":$idReseña%")
                          ->orWhere('link_error', 'LIKE', "%\"id_de_la_rese\\u00f1a\":$idReseña%");
                    }
                });
            }
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

        // Obtener nombres de usuarios únicos que tienen tickets
        $usuariosConTickets = SoporteTecnico::join('users', 'soportes_tecnicos.user_id', '=', 'users.id')
            ->selectRaw("DISTINCT CONCAT(users.first_name, ' ', users.last_name) as nombre")
            ->orderBy('nombre')
            ->pluck('nombre');

        // Obtener nombres de comercios únicos para el buscador
        $comerciosConTickets = \Reda\RedaAlojamiento\Models\Experiencia\Experiencia::orderBy('titulo')->pluck('titulo');

        return view('reda-alojamiento::admin.general.soporte_tecnico.index', compact('tickets', 'temas', 'usuariosConTickets', 'comerciosConTickets'));
    }

    /**
     * Display the details of a technical support ticket.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $ticket = SoporteTecnico::with(['user', 'admin'])->findOrFail($id);

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

            // Los tickets solo pueden ser gestionados por usuarios en la tabla "admin"
            $idAdmin = Auth::guard('admin')->id();

            if (!$idAdmin) {
                Log::error("SoporteTecnicoController::cerrarTicket - No se detectó sesión en guard 'admin' para el ticket #$id");
                return response()->json([
                    'success' => false,
                    'message' => 'No session detected in admin guard',
                    'mensaje_usuario' => __('Su sesión de administrador ha expirado o no es válida'),
                    'code' => 401
                ], 401);
            }

            $ticket->update([
                'estatus' => 'Cerrado',
                'resultado_gestion' => $resultado,
                'admin_id' => $idAdmin,
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
