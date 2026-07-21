<?php
namespace Reda\RedaAlojamiento\Http\Controllers\Disputa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Reda\RedaAlojamiento\Models\Disputa\Disputa;
use App\Models\Bookings;
use Auth;
use Validator;
use Carbon\Carbon;

class DisputaController extends Controller
{
    public function index()
    {
        return view('reda-alojamiento::disputa.disputas.index');
    }

    /**
     * Obtiene el listado de mediaciones paginado para el dashboard.
     */
    public function obtenerDisputasPaginadas(Request $request)
    {
        $status = $request->get('status', 'todos');
        $myUserId = Auth::id();

        $query = Disputa::where(function($q) use ($myUserId) {
            $q->where('id_usuario_turista', $myUserId)
              ->orWhere('id_usuario_anfitrion', $myUserId);
        });

        if ($status !== 'todos') {
            // Mapeo de estados del frontend a los valores en la base de datos (traducidos)
            $mapeo = [
                'abiertos' => __('Abierto'),
                'revision' => __('En revisión'),
                'espera'   => __('Esperando respuesta'),
                'resueltos' => __('Resuelto'),
                'cerrados' => __('Cerrado')
            ];
            
            if (isset($mapeo[$status])) {
                $query->where('estado', $mapeo[$status]);
            }
        }

        $disputas = $query->with(['booking.properties', 'agente', 'turista', 'anfitrion'])->orderBy('updated_at', 'desc')->paginate(10);

        // Formatear los datos para el consumo del frontend via Javascript
        $items = $disputas->getCollection()->map(function($d) use ($myUserId) {
            // Determinar qué documentos mostrar (solo los del usuario actual)
            $documentosRaw = ($d->id_usuario_turista == $myUserId) ? $d->documentos_turista : $d->documentos_anfitrion;
            $adjuntos = [];
            
            if ($documentosRaw) {
                $paths = json_decode($documentosRaw, true);
                if (is_array($paths)) {
                    foreach ($paths as $path) {
                        // Asegurar que el path tenga el prefijo public/ para evitar 404
                        $webPath = strpos($path, 'public/') === 0 ? $path : 'public/' . $path;
                        $adjuntos[] = [
                            'nombre' => basename($path),
                            'url' => asset($webPath),
                            'es_imagen' => in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])
                        ];
                    }
                }
            }

            // Asegurar prefijo public/ para la foto de la propiedad si es ruta relativa
            $propiedadFoto = $d->booking && $d->booking->properties ? $d->booking->properties->cover_photo : asset('public/img/unnamed.png');
            if ($d->booking && $d->booking->properties && strpos($propiedadFoto, 'http') === false && strpos($propiedadFoto, 'public/') !== 0) {
                $propiedadFoto = asset('public/' . $propiedadFoto);
            } elseif ($d->booking && $d->booking->properties) {
                $propiedadFoto = asset($propiedadFoto);
            }

            return [
                'id' => $d->id,
                'estado' => $d->estado,
                'paso_actual' => $d->paso_actual,
                'motivo' => $d->motivo,
                'prioridad' => $d->prioridad,
                'descripcion' => $d->descripcion,
                'booking_id' => $d->booking_id,
                'adjuntos' => $adjuntos,
                'fecha_apertura' => $d->fecha_apertura ? $d->fecha_apertura->format('d/m/Y H:i') : '',
                'actualizado_hace' => $d->updated_at->diffForHumans(),
                'agente' => $d->agente ? [
                    'nombre' => $d->agente->username,
                    'foto' => $d->agente->profile_src
                ] : null,
                'turista_nombre' => $d->turista ? $d->turista->first_name . ' ' . $d->turista->last_name : '',
                'turista_foto' => $d->turista ? $d->turista->profile_src : asset('public/img/unnamed.png'),
                'anfitrion_nombre' => $d->anfitrion ? $d->anfitrion->first_name . ' ' . $d->anfitrion->last_name : '',
                'anfitrion_foto' => $d->anfitrion ? $d->anfitrion->profile_src : asset('public/img/unnamed.png'),
                'propiedad_foto' => $propiedadFoto
            ];
        });

        $respuesta = [
            'success' => true,
            'message' => __('Listado de mediaciones'),
            'mensaje_usuario' => __('Listado recuperado con éxito'),
            'respuesta' => [
                'data' => $items,
                'pagination' => (string) $disputas->appends(request()->except('page'))->links('reda-alojamiento::general.paginacion')
            ],
            'code' => 200
        ];

        return response()->json($respuesta, $respuesta['code']);
    }

    /**
     * Retorna el HTML del modal de mediación.
     */
    public function getModal()
    {
        $html = view('reda-alojamiento::disputa.disputas.modal_mediacion')->render();
        return response()->json([
            'success' => true,
            'message' => __('Carga de modal'),
            'mensaje_usuario' => __('Cargado con éxito'),
            'respuesta' => $html,
            'code' => 200
        ], 200);
    }

    /**
     * Retorna el HTML del modal de detalle de mediación.
     */
    public function getDetailModal($id)
    {
        $disputa = Disputa::findOrFail($id);
        $html = view('reda-alojamiento::disputa.disputas.modal_detalle', compact('disputa'))->render();
        return response()->json([
            'success' => true,
            'message' => __('Carga de detalle'),
            'mensaje_usuario' => __('Cargado con éxito'),
            'respuesta' => $html,
            'code' => 200
        ], 200);
    }

    /**
     * Verifica si existe una disputa para una reservación y retorna sus detalles.
     */
    public function checkDispute($booking_id)
    {
        $disputa = Disputa::where('booking_id', $booking_id)->first();

        $respuesta = [
            'success' => true,
            'message' => __('Verificación de disputa'),
            'mensaje_usuario' => __('Resultados recuperados con éxito'),
            'respuesta' => [
                'exists' => false
            ],
            'code' => 200
        ];

        if ($disputa) {
            $respuesta['respuesta'] = [
                'exists' => true,
                'data' => [
                    'id'           => $disputa->id,
                    'fecha'        => $disputa->fecha_apertura ? $disputa->fecha_apertura->format('d/m/Y') : '',
                    'estado'       => $disputa->estado,
                    'paso_actual'  => $disputa->paso_actual,
                ]
            ];
        }

        return response()->json($respuesta, $respuesta['code']);
    }

    /**
     * Muestra el detalle de una mediación.
     */
    public function show($id)
    {
        $disputa = Disputa::findOrFail($id);
        return view('reda-alojamiento::disputa.disputas.show', compact('disputa'));
    }

    /**
     * Almacena una nueva solicitud de mediación (disputa).
     */
    public function store(Request $request)
    {
        $rules = [
            'booking_id'  => 'required|exists:bookings,id',
            'prioridad'   => 'required|in:Baja,Media,Alta',
            'motivo'      => 'required|string|max:255',
            'descripcion' => 'required|string',
            'documentos.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240', // 10MB máx por archivo
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => __('Error de validación'),
                'mensaje_usuario' => __('Por favor complete todos los campos obligatorios'),
                'respuesta' => $validator->errors(),
                'code' => 422
            ], 422);
        }

        $booking = Bookings::findOrFail($request->booking_id);
        $myUserId = Auth::id();

        // Determinar rol del iniciador
        $esAnfitrion = ($myUserId == $booking->host_id);
        $esTurista = ($myUserId == $booking->user_id);

        if (!$esAnfitrion && !$esTurista) {
            return response()->json([
                'success' => false,
                'message' => __('Usuario no autorizado'),
                'mensaje_usuario' => __('No tienes permiso para iniciar una mediación en esta reserva.'),
                'respuesta' => '',
                'code' => 403
            ], 403);
        }

        // Preparar datos de la disputa
        $disputa = new Disputa();
        $disputa->booking_id = $request->booking_id;
        $disputa->prioridad = $request->prioridad;
        $disputa->motivo = $request->motivo;
        $disputa->descripcion = $request->descripcion;
        $disputa->id_usuario_turista = $booking->user_id;
        $disputa->id_usuario_anfitrion = $booking->host_id;
        $disputa->id_usuario_inicial = $myUserId;
        $disputa->rol_usuario_inicial = $esAnfitrion ? __('Anfitrión') : __('Turista');

        // Valores por defecto solicitados
        $disputa->paso_actual = __('Caso creado');
        $disputa->fecha_apertura = Carbon::now();
        $disputa->fecha_limite = Carbon::now()->addHours(48);
        $disputa->estado = __('Abierto');

        $disputa->save();

        // Manejo de archivos después de guardar para tener el ID de la disputa
        if ($request->hasFile('documentos')) {
            $paths = [];
            // Carpeta: public/images/disputas/{disputa_id}/[documentos_anfitrion|documentos_turista]/{user_id}
            $subFolder = $esAnfitrion ? 'documentos_anfitrion' : 'documentos_turista';
            $userIdFolder = $esAnfitrion ? $booking->host_id : $booking->user_id;
            $destPath = public_path("images/disputas/{$disputa->id}/{$subFolder}/{$userIdFolder}");

            if (!file_exists($destPath)) {
                mkdir($destPath, 0755, true);
            }

            foreach ($request->file('documentos') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move($destPath, $fileName);
                // Guardar con el prefijo public/ para evitar errores 404 en este entorno
                $paths[] = "public/images/disputas/{$disputa->id}/{$subFolder}/{$userIdFolder}/{$fileName}";
            }

            // Guardar rutas como JSON en la columna correspondiente
            if ($esAnfitrion) {
                $disputa->documentos_anfitrion = json_encode($paths);
            } else {
                $disputa->documentos_turista = json_encode($paths);
            }
            $disputa->save(); // Actualizar con las rutas de documentos
        }

        return response()->json([
            'success' => true,
            'message' => __('Mediación creada'),
            'mensaje_usuario' => __('Solicitud de mediación enviada correctamente.'),
            'respuesta' => $disputa,
            'code' => 200
        ], 200);
        }
        }