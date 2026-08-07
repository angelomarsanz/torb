<?php

namespace Reda\RedaAlojamiento\Http\Controllers\Disputa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Messages;
use App\Models\Bookings;
use App\Models\User;
use App\Models\Admin;
use Reda\RedaAlojamiento\Models\Disputa\Disputa;
use Reda\RedaAlojamiento\Models\Disputa\MensajeMetadata;
use Auth;
use Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MensajeController extends Controller
{
    /**
     * Obtiene los mensajes de una mediación basada en el booking_id.
     */
    public function getMessages($booking_id)
    {
        $booking = Bookings::with(['users', 'host'])->find($booking_id);
        if (!$booking) {
            return response()->json([
                'success' => false,
                'mensaje_usuario' => __('Reservación no encontrada')
            ], 404);
        }

        // Identificamos a los dos usuarios participantes de la mediación actual
        $usuarioA = $booking->user_id;
        $usuarioB = $booking->host_id;

        // 1. Identificar todas las reservaciones/contextos compartidos estrictamente entre estos dos usuarios.
        // Se incluyen casos donde se invierten los roles (A es turista de B, o B es turista de A).
        $sharedBookingIds = Bookings::where(function($q) use ($usuarioA, $usuarioB) {
            $q->where(function($q2) use ($usuarioA, $usuarioB) {
                $q2->where('user_id', $usuarioA)->where('host_id', $usuarioB);
            })->orWhere(function($q2) use ($usuarioA, $usuarioB) {
                $q2->where('user_id', $usuarioB)->where('host_id', $usuarioA);
            });
        })->pluck('id')->toArray();

        // 2. Consultar mensajes bajo criterios estrictos de "Pareja Compartida"
        $messages = Messages::with(['sender', 'receiver'])
            ->leftJoin('reda_mensajes_metadata', 'messages.id', '=', 'reda_mensajes_metadata.message_id')
            ->select('messages.*', 'reda_mensajes_metadata.sender_type')
            ->where(function($query) use ($usuarioA, $usuarioB, $sharedBookingIds) {
                
                // CRITERIO A: Intercambios directos entre estos dos usuarios (Consultas o Reservas)
                $query->where(function($q) use ($usuarioA, $usuarioB) {
                    $q->where(function($q2) use ($usuarioA, $usuarioB) {
                        $q2->where('sender_id', $usuarioA)->where('receiver_id', $usuarioB);
                    })->orWhere(function($q2) use ($usuarioA, $usuarioB) {
                        $q2->where('sender_id', $usuarioB)->where('receiver_id', $usuarioA);
                    });
                })
                
                // CRITERIO B: Intervenciones de Administradores (Agentes) en CUALQUIER reservación 
                // que haya involucrado a estos dos usuarios juntos.
                ->orWhereIn('booking_id', $sharedBookingIds);
            })
            ->orderBy('messages.created_at', 'asc') // Orden cronológico total
            ->get();

        // Marcar como leídos los mensajes de esta conversación que no sean del usuario actual
        $userId = Auth::id();
        Messages::whereIn('booking_id', $sharedBookingIds)
            ->where(function($q) use ($userId) {
                // Mensajes que NO son del usuario actual (considerando metadata)
                $q->where('sender_id', '!=', $userId)
                  ->orWhereExists(function ($query) {
                      $query->select(DB::raw(1))
                            ->from('reda_mensajes_metadata')
                            ->whereColumn('reda_mensajes_metadata.message_id', 'messages.id')
                            ->where('reda_mensajes_metadata.sender_type', 'admin');
                  });
            })
            ->where('read', 0)
            ->update(['read' => 1]);

        // Identificar IDs de administradores para cargar sus nombres
        $adminIds = $messages->where('sender_type', 'admin')->pluck('sender_id')->unique()->toArray();
        $admins = Admin::whereIn('id', $adminIds)->get()->keyBy('id');

        // Cargar todas las disputas y bookings relacionados para identificar demandantes correctamente por mensaje
        $disputas = Disputa::whereIn('booking_id', $sharedBookingIds)->get()->keyBy('booking_id');
        $allRelatedBookings = Bookings::whereIn('id', $sharedBookingIds)->get()->keyBy('id');

        foreach ($messages as $message) {
            $message->created_at_humans = $message->created_at->diffForHumans();

            // IMPORTANTE: Evitar crash en PHP 8.2 por accessors en el modelo original Messages.php
            $message->makeHidden(['host_user', 'guest_user']);

            if ($message->sender_type === 'admin') {
                $admin = $admins->get($message->sender_id);
                $message->sender_name = $admin ? $admin->username : __('Administrador');
                $message->sender_foto = reda_get_profile_src($admin, 'admin');
                $message->sender_role = __('agente');
            } else {
                $message->sender_name = $message->sender ? ($message->sender->first_name . ' ' . $message->sender->last_name) : __('Sistema');
                $message->sender_foto = reda_get_profile_src($message->sender);
                
                // Lógica de Rol y Demandante basada en el contexto REAL del mensaje (su booking_id original)
                $actualBookingId = $message->booking_id;
                $msgBooking = $allRelatedBookings->get($actualBookingId);
                $msgDisputa = $disputas->get($actualBookingId);
                $demandanteLabel = ' - ' . __('demandante');
                
                if ($msgBooking && $message->sender_id == $msgBooking->host_id) {
                    $esDemandante = ($msgDisputa && $msgDisputa->id_usuario_inicial == $message->sender_id && str_contains(strtolower($msgDisputa->rol_usuario_inicial), 'anfitr'));
                    $message->sender_role = __('anfitrión') . ($esDemandante ? $demandanteLabel : '');
                } elseif ($msgBooking && $message->sender_id == $msgBooking->user_id) {
                    $esDemandante = ($msgDisputa && $msgDisputa->id_usuario_inicial == $message->sender_id && str_contains(strtolower($msgDisputa->rol_usuario_inicial), 'turist'));
                    $message->sender_role = __('turista') . ($esDemandante ? $demandanteLabel : '');
                } else {
                    $message->sender_role = __('usuario');
                }
            }

            // Virtualización de booking_id para que el frontend agrupe todo en una sola conversación (Después de los cálculos)
            $message->booking_id = (int) $booking_id;
        }

        $disputa = $disputas->get($booking_id);

        return response()->json([
            'success' => true,
            'message' => __('Mensajes recuperados'),
            'mensaje_usuario' => __('Listado recuperado con éxito'),
            'respuesta' => [
                'messages' => $messages,
                'booking' => $booking,
                'disputa' => $disputa,
                'current_user_id' => Auth::id()
            ],
            'code' => 200
        ], 200);
    }

    /**
     * Guarda un nuevo mensaje enviado por el usuario (Turista o Anfitrión).
     */
    public function store(Request $request)
    {
        $rules = [
            'booking_id'  => 'required|exists:bookings,id',
            'message'     => 'required|string',
            'receiver_id' => 'nullable' 
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            Log::error("Error de validación al enviar mensaje frontend: " . print_r($validator->errors()->all(), true));
            return response()->json([
                'success' => false,
                'message' => __('Error de validación'),
                'mensaje_usuario' => __('El mensaje es obligatorio'),
                'respuesta' => $validator->errors(),
                'code' => 422
            ], 422);
        }

        try {
            $booking = Bookings::findOrFail($request->booking_id);
            
            $message = new Messages;
            $message->property_id = $booking->property_id;
            $message->booking_id  = $request->booking_id;
            $message->receiver_id = $request->receiver_id ?? 0; // 0 indica broadcast/grupo para mediaciones
            $message->sender_id   = Auth::id();
            $message->message     = $request->message;
            $message->type_id     = 1; // Tipo query/chat
            $message->read        = 0;
            $message->save();

            // Evitar crash en PHP 8.2 por accessors en el modelo original
            $message->makeHidden(['host_user', 'guest_user']);

            Log::info("Mensaje frontend guardado con éxito. ID: " . $message->id);

            return response()->json([
                'success' => true,
                'message' => __('Mensaje enviado'),
                'mensaje_usuario' => __('El mensaje ha sido enviado correctamente'),
                'respuesta' => $message,
                'code' => 200
            ], 200);

        } catch (\Exception $e) {
            Log::error("Excepción al guardar mensaje frontend: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'mensaje_usuario' => __('Ocurrió un error al procesar el envío del mensaje'),
                'code' => 500
            ], 500);
        }
    }
}
