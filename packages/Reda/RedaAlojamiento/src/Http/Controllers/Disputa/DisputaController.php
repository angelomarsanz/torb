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
     * Retorna el HTML del modal de mediación.
     */
    public function getModal()
    {
        return view('reda-alojamiento::disputa.disputas.modal_mediacion')->render();
    }

    /**
     * Retorna el HTML del modal de detalle de mediación.
     */
    public function getDetailModal($id)
    {
        $disputa = Disputa::findOrFail($id);
        return view('reda-alojamiento::disputa.disputas.modal_detalle', compact('disputa'))->render();
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

        // Valores por defecto solicitados
        $disputa->paso_actual = __('Caso creado');
        $disputa->fecha_apertura = Carbon::now();
        $disputa->fecha_limite = Carbon::now()->addHours(48);
        $disputa->estado = __('Abierto');

        $disputa->save();

        // Manejo de archivos después de guardar para tener el ID de la disputa
        if ($request->hasFile('documentos')) {
            $paths = [];
            // Carpeta: public/images/disputas/{disputa_id}/[documentos_anfitrion|documentos_turista]
            $subFolder = $esAnfitrion ? 'documentos_anfitrion' : 'documentos_turista';
            $destPath = public_path("images/disputas/{$disputa->id}/{$subFolder}");

            if (!file_exists($destPath)) {
                mkdir($destPath, 0755, true);
            }

            foreach ($request->file('documentos') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move($destPath, $fileName);
                $paths[] = "images/disputas/{$disputa->id}/{$subFolder}/{$fileName}";
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