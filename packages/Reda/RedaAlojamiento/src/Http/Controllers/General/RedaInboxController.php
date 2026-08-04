<?php

namespace Reda\RedaAlojamiento\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\{Currency, Messages, Bookings};
use App\Models\Admin;
use Auth;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RedaInboxController extends Controller
{
    /**
     * Inbox Page - Unified Participant Chat
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        // 1. Identificar todos los bookings donde el usuario participa como huésped o anfitrión
        $involvedBookingIds = Bookings::where('user_id', $userId)
            ->orWhere('host_id', $userId)
            ->pluck('id')
            ->toArray();
        
        // 2. Obtener todos los mensajes:
        // A. Mensajes vinculados a esos bookings (incluye mediaciones donde receiver_id puede ser 0)
        // B. Mensajes sin booking donde el usuario es emisor o receptor (consultas directas)
        $allMessages = Messages::with(['bookings.users', 'bookings.host', 'properties:id,name', 'sender', 'receiver'])
            ->where(function($q) use ($involvedBookingIds, $userId) {
                $q->whereIn('booking_id', $involvedBookingIds)
                  ->orWhere('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
            })
            ->orderBy('id', 'desc')
            ->get();

        // 3. Identificar al "Chat Partner" único (Estrategia de Identidad de Hilo)
        // Agrupamos por los participantes del booking para que directos y mediaciones caigan en el mismo saco
        $allMessages->transform(function ($message) use ($userId) {
            $booking = $message->bookings;
            if ($booking) {
                // El partner es siempre la "otra" persona de la reservación (siempre de la tabla users)
                if ($booking->user_id == $userId) {
                    $message->chat_partner_id = $booking->host_id;
                    $message->chat_partner = $booking->host;
                } else {
                    $message->chat_partner_id = $booking->user_id;
                    $message->chat_partner = $booking->users;
                }
            } else {
                // Fallback para mensajes sin booking (poco probable en este sistema)
                if ($message->sender_id == $userId) {
                    $message->chat_partner_id = $message->receiver_id;
                    $message->chat_partner = $message->receiver;
                } else {
                    $message->chat_partner_id = $message->sender_id;
                    $message->chat_partner = $message->sender;
                }
            }
            return $message;
        });

        // 4. Generar lista del Sidebar: un item por cada compañero de chat real
        $data['sidebar_messages'] = $allMessages->unique('chat_partner_id')->values();

        $data['messages'] = [];
        $data['conversation'] = [];

        if (count($data['sidebar_messages']) > 0) {
            $targetBookingId = $request->id;
            $selectedPartnerId = null;

            if ($targetBookingId) {
                $targetBooking = Bookings::find($targetBookingId);
                if ($targetBooking) {
                    $selectedPartnerId = ($targetBooking->user_id == $userId) ? $targetBooking->host_id : $targetBooking->user_id;
                    $data['booking'] = $targetBooking->load('users', 'properties');
                }
            }

            if (!$selectedPartnerId) {
                $first = $data['sidebar_messages']->first();
                $selectedPartnerId = $first->chat_partner_id;
                $data['booking'] = Bookings::find($first->booking_id)->load('users', 'properties');
                $targetBookingId = $first->booking_id;
            }

            // --- LÓGICA DE PAREJA ESTRICTA Y CONTEXTO COMPARTIDO ---
            $usuarioA = $userId;
            $usuarioB = $selectedPartnerId;

            // Identificar todas las reservaciones/contextos compartidos estrictamente entre estos dos usuarios.
            $sharedBookingIds = Bookings::where(function($q) use ($usuarioA, $usuarioB) {
                $q->where(function($q2) use ($usuarioA, $usuarioB) {
                    $q2->where('user_id', $usuarioA)->where('host_id', $usuarioB);
                })->orWhere(function($q2) use ($usuarioA, $usuarioB) {
                    $q2->where('user_id', $usuarioB)->where('host_id', $usuarioA);
                });
            })->pluck('id')->toArray();

            // 4. Cargar HISTORIA UNIFICADA
            $unifiedHistory = Messages::with(['sender', 'receiver'])
                ->leftJoin('reda_mensajes_metadata', 'messages.id', '=', 'reda_mensajes_metadata.message_id')
                ->select('messages.*', 'reda_mensajes_metadata.sender_type')
                ->where(function($query) use ($usuarioA, $usuarioB, $sharedBookingIds) {
                    // Criterio 1: Intercambios directos
                    $query->where(function($q) use ($usuarioA, $usuarioB) {
                        $q->where(function($q2) use ($usuarioA, $usuarioB) {
                            $q2->where('sender_id', $usuarioA)->where('receiver_id', $usuarioB);
                        })->orWhere(function($q2) use ($usuarioA, $usuarioB) {
                            $q2->where('sender_id', $usuarioB)->where('receiver_id', $usuarioA);
                        });
                    })
                    // Criterio 2: Intervenciones en sus contextos comunes
                    ->orWhereIn('booking_id', $sharedBookingIds);
                })
                ->orderBy('messages.created_at', 'asc')
                ->get();

            // Identificar IDs de administradores para cargar sus nombres
            $adminIds = $unifiedHistory->where('sender_type', 'admin')->pluck('sender_id')->unique()->toArray();
            $admins = Admin::whereIn('id', $adminIds)->get()->keyBy('id');

            // --- VIRTUALIZACIÓN Y PROCESAMIENTO ---
            $unifiedHistory->each(function($msg) use ($targetBookingId, $admins) {
                $msg->booking_id = (int) $targetBookingId;
                // Seguridad PHP 8.2
                $msg->makeHidden(['host_user', 'guest_user']);

                if ($msg->sender_type === 'admin') {
                    $admin = $admins->get($msg->sender_id);
                    $msg->custom_sender_name = $admin ? $admin->username : __('Agente');
                    $msg->custom_sender_foto = reda_get_profile_src($admin, 'admin');
                } else {
                    $msg->custom_sender_name = $msg->sender ? ($msg->sender->first_name) : __('Usuario');
                    $msg->custom_sender_foto = reda_get_profile_src($msg->sender);
                }
            });

            $data['messages'] = $unifiedHistory; 
            $data['conversation'] = $unifiedHistory;
            
            Log::info("REDA Inbox: Historia unificada virtualizada para Partner $selectedPartnerId. Total: " . $unifiedHistory->count());

            if (isset($data['booking']) && $data['booking']) {
                $data['symbol'] = Currency::getAll()->firstWhere('code', $data['booking']->currency_code)->symbol ?? '$';
            } else {
                $data['symbol'] = '$';
            }
         }
        
        return view('reda-alojamiento::users.inbox', $data);
    }

    /**
     * Load Conversation Details via REDA Ajax Route
     */
    public function message(Request $request)
    {
        $userId = Auth::id();
        $booking_id = $request->id;
        $targetBooking = Bookings::findOrFail($booking_id);
        $partnerId = ($targetBooking->user_id == $userId) ? $targetBooking->host_id : $targetBooking->user_id;

        // Marcar como leídos todos los mensajes con este partner
        Messages::where('receiver_id', $userId)->where('sender_id', $partnerId)->update(['read' => 1]);

        // --- LÓGICA DE PAREJA ESTRICTA ---
        $usuarioA = $userId;
        $usuarioB = $partnerId;

        $sharedBookingIds = Bookings::where(function($q) use ($usuarioA, $usuarioB) {
            $q->where(function($q2) use ($usuarioA, $usuarioB) {
                $q2->where('user_id', $usuarioA)->where('host_id', $usuarioB);
            })->orWhere(function($q2) use ($usuarioA, $usuarioB) {
                $q2->where('user_id', $usuarioB)->where('host_id', $usuarioA);
            });
        })->pluck('id')->toArray();

        // Cargar HISTORIA UNIFICADA
        $unifiedHistory = Messages::with(['sender', 'receiver'])
            ->leftJoin('reda_mensajes_metadata', 'messages.id', '=', 'reda_mensajes_metadata.message_id')
            ->select('messages.*', 'reda_mensajes_metadata.sender_type')
            ->where(function($query) use ($usuarioA, $usuarioB, $sharedBookingIds) {
                $query->where(function($q) use ($usuarioA, $usuarioB) {
                    $q->where(function($q2) use ($usuarioA, $usuarioB) {
                        $q2->where('sender_id', $usuarioA)->where('receiver_id', $usuarioB);
                    })->orWhere(function($q2) use ($usuarioA, $usuarioB) {
                        $q2->where('sender_id', $usuarioB)->where('receiver_id', $usuarioA);
                    });
                })
                ->orWhereIn('booking_id', $sharedBookingIds);
            })
            ->orderBy('messages.created_at', 'asc')
            ->get();

        // Identificar administradores
        $adminIds = $unifiedHistory->where('sender_type', 'admin')->pluck('sender_id')->unique()->toArray();
        $admins = Admin::whereIn('id', $adminIds)->get()->keyBy('id');

        // --- VIRTUALIZACIÓN ---
        $unifiedHistory->each(function($msg) use ($booking_id, $admins) {
            $msg->booking_id = (int) $booking_id;
            $msg->makeHidden(['host_user', 'guest_user']);

            if ($msg->sender_type === 'admin') {
                $admin = $admins->get($msg->sender_id);
                $msg->custom_sender_name = $admin ? $admin->username : __('Agente');
                $msg->custom_sender_foto = reda_get_profile_src($admin, 'admin');
            } else {
                $msg->custom_sender_name = $msg->sender ? ($msg->sender->first_name) : __('Usuario');
                $msg->custom_sender_foto = reda_get_profile_src($msg->sender);
            }
        });

        $data['messages'] = $unifiedHistory;
        $data['conversation'] = $unifiedHistory;
        $data['booking'] = $targetBooking->load('host', 'users', 'properties');
        $data['symbol'] = Currency::getAll()->firstWhere('code', $data['booking']->currency_code)->symbol ?? '$';

        return response()->json([
             'success' => true,
             'message' => __('Detalle del mensaje cargado'),
             'mensaje_usuario' => __('Cargado con éxito'),
             'respuesta' => [
                 "inbox" => view('reda-alojamiento::users.messages', $data)->render(), 
                 "booking" => view('users.booking', $data)->render()
             ],
             'code' => 200
        ], 200);
    }

    /**
    * Unified Reply via REDA Ajax Route
    */
    public function messageReply(Request $request)
    {
        $rules = array('msg' => 'required|string');
        $validator = Validator::make($request->all(), $rules);

        if (!$validator->fails()) {
            try {
                $message = new Messages;
                $message->property_id = $request->property_id;
                $message->booking_id = $request->booking_id;
                $message->receiver_id = $request->receiver_id;
                $message->sender_id = Auth::id();
                $message->message = $request->msg;
                $message->type_id = 1;
                $message->save();

                // Seguridad PHP 8.2
                $message->makeHidden(['host_user', 'guest_user']);

                Messages::where('booking_id', $request->booking_id)
                       ->where('receiver_id', Auth::id())
                       ->update(['read' => 1]);
                       
                return response()->json([
                    'success' => true,
                    'message' => __('Respuesta enviada'),
                    'mensaje_usuario' => __('Mensaje enviado con éxito'),
                    'respuesta' => 1,
                    'code' => 200
                ], 200);
            } catch (\Exception $e) {
                Log::error("REDA Inbox Error: " . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'mensaje_usuario' => __('Error al enviar mensaje'),
                    'respuesta' => 0,
                    'code' => 500
                ], 500);
            }
        }
        return response()->json([
            'success' => false,
            'message' => __('Error de validación'),
            'mensaje_usuario' => __('El mensaje es obligatorio'),
            'respuesta' => 0,
            'code' => 422
        ], 422);
    }
}
