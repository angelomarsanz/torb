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
     * Verifica si existe una disputa para una reservación y retorna sus detalles.
     */
    public function checkDispute($booking_id)
    {
        $disputa = Disputa::where('booking_id', $booking_id)->first();

        if ($disputa) {
            return response()->json([
                'exists' => true,
                'data' => [
                    'id'           => $disputa->id,
                    'fecha'        => $disputa->fecha_apertura ? $disputa->fecha_apertura->format('d/m/Y') : '',
                    'estado'       => $disputa->estado,
                    'paso_actual'  => $disputa->paso_actual,
                ]
            ]);
        }

        return response()->json(['exists' => false]);
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
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $booking = Bookings::findOrFail($request->booking_id);
        $myUserId = Auth::id();

        // Determinar rol del iniciador
        $esAnfitrion = ($myUserId == $booking->host_id);
        $esTurista = ($myUserId == $booking->user_id);

        if (!$esAnfitrion && !$esTurista) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para iniciar una mediación en esta reserva.'], 403);
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
        $disputa->paso_actual = 'Caso creado';
        $disputa->fecha_apertura = Carbon::now();
        $disputa->fecha_limite = Carbon::now()->addHours(48);
        $disputa->estado = 'Abierto';

        // Manejo de archivos
        if ($request->hasFile('documentos')) {
            $paths = [];
            // Carpeta: public/images/{booking_id}/[documentos_anfitrion|documentos_turista]
            $subFolder = $esAnfitrion ? 'documentos_anfitrion' : 'documentos_turista';
            $destPath = public_path("images/{$request->booking_id}/{$subFolder}");

            if (!file_exists($destPath)) {
                mkdir($destPath, 0777, true);
            }

            foreach ($request->file('documentos') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move($destPath, $fileName);
                $paths[] = "images/{$request->booking_id}/{$subFolder}/{$fileName}";
            }

            // Guardar rutas como JSON en la columna correspondiente
            if ($esAnfitrion) {
                $disputa->documentos_anfitrion = json_encode($paths);
            } else {
                $disputa->documentos_turista = json_encode($paths);
            }
        }

        $disputa->save();

        return response()->json([
            'success' => true,
            'message' => 'Solicitud de mediación enviada correctamente.',
            'data'    => $disputa
        ]);
    }
}