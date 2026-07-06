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

            // 1. Obtenemos todos los tickets que tienen link_error para filtrar en memoria (más seguro que LIKE en JSON)
            // Solo traemos ID y link_error para optimizar memoria
            $ticketsConDatos = SoporteTecnico::whereNotNull('link_error')->select('id', 'link_error')->get();

            // 2. Buscamos IDs de experiencias que coincidan parcialmente con el nombre buscado
            $experienciasCoincidentes = \Reda\RedaAlojamiento\Models\Experiencia\Experiencia::where('titulo', 'LIKE', "%$nombreComercio%")->pluck('id')->toArray();

            // 3. Buscamos IDs de reseñas vinculadas a esas experiencias
            $reseñasCoincidentes = [];
            if (!empty($experienciasCoincidentes)) {
                $reseñasCoincidentes = \Reda\RedaAlojamiento\Models\Experiencia\CalificacionExperiencia::whereIn('experiencia_id', $experienciasCoincidentes)->pluck('id')->toArray();
            }

            // 4. Filtramos los tickets basándonos en si su link_error apunta a uno de esos comercios o reseñas
            $idsTicketsFiltrados = $ticketsConDatos->filter(function($t) use ($experienciasCoincidentes, $reseñasCoincidentes) {
                $datos = $t->link_error; // Esto usa el cast/accessor del modelo
                if (empty($datos) || !is_array($datos)) return false;

                // Caso directo: id_experiencia
                $idExp = $datos['id_experiencia'] ?? null;
                if ($idExp && in_array($idExp, $experienciasCoincidentes)) return true;

                // Caso indirecto: id_reseña
                $idRes = $datos['id_reseña'] ?? $datos['id_de_la_reseña'] ?? $datos['id_de_la_rese\u00f1a'] ?? null;
                if ($idRes && in_array($idRes, $reseñasCoincidentes)) return true;

                return false;
            })->pluck('id');

            // Aplicamos el filtro de IDs a la consulta principal
            $query->whereIn('id', $idsTicketsFiltrados);
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

        // Obtener nombres de usuarios únicos que tienen tickets (solo los que abrieron tickets)
        $usuariosConTickets = SoporteTecnico::join('users', 'soportes_tecnicos.user_id', '=', 'users.id')
            ->selectRaw("DISTINCT TRIM(CONCAT(users.first_name, ' ', users.last_name)) as nombre")
            ->orderBy('nombre')
            ->pluck('nombre')
            ->unique()
            ->values();

        // Obtener nombres de comercios únicos que tienen tickets (solo los vinculados en link_error)
        $todasLasLinkError = SoporteTecnico::whereNotNull('link_error')->get()->pluck('link_error');
        
        $experienciaIds = collect();
        $reseñaIds = collect();

        foreach ($todasLasLinkError as $datos) {
            if (!is_array($datos)) continue;

            if (isset($datos['id_experiencia'])) {
                $experienciaIds->push($datos['id_experiencia']);
            }

            if (($datos['vista_origen'] ?? '') === 'Reportar calificación') {
                $idReseña = $datos['id_reseña'] ?? $datos['id_de_la_reseña'] ?? $datos['id_de_la_rese\u00f1a'] ?? null;
                if ($idReseña) {
                    $reseñaIds->push($idReseña);
                }
            }
        }

        if ($reseñaIds->isNotEmpty()) {
            $idsFromReseñas = \Reda\RedaAlojamiento\Models\Experiencia\CalificacionExperiencia::whereIn('id', $reseñaIds->unique())
                ->pluck('experiencia_id');
            $experienciaIds = $experienciaIds->merge($idsFromReseñas);
        }

        $comerciosConTickets = \Reda\RedaAlojamiento\Models\Experiencia\Experiencia::whereIn('id', $experienciaIds->unique())
            ->orderBy('titulo')
            ->pluck('titulo')
            ->unique()
            ->values();

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
