<?php

namespace Reda\RedaAlojamiento\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\{Currency, Messages, Bookings};
use Auth;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Log;

class RedaInboxController extends Controller
{
    /**
    * Inbox Page - Unified Participant Chat
    */
    public function index(Request $request)
    {
        $userId = Auth::id();
        
        // 1. Get all messages involving the user or linked to their bookings
        // Ordenamos por ID desc para que el unique() tome el mensaje más reciente
        $allMessages = Messages::with(['bookings', 'properties:id,name', 'sender', 'receiver'])
            ->where(function($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId)
                  ->orWhereHas('bookings', function($query) use ($userId) {
                      $query->where('user_id', $userId)->orWhere('host_id', $userId);
                  });
            })
            ->orderBy('id', 'desc')
            ->get();

        // 2. Identify the real "Chat Partner" for each message
        $allMessages->transform(function ($message) use ($userId) {
            $partnerId = null;
            
            // Prioridad 1: Si es mensaje directo (no sistema/admin), el otro es el partner
            if ($message->sender_id != $userId && $message->sender_id != 0 && $message->sender_id != 1) {
                $partnerId = $message->sender_id;
            } elseif ($message->receiver_id != $userId && $message->receiver_id != 0 && $message->receiver_id != 1) {
                $partnerId = $message->receiver_id;
            } 
            
            // Prioridad 2: Si es mensaje de sistema/admin o no se resolvió, usar el booking
            if (!$partnerId && $message->bookings) {
                $partnerId = ($message->bookings->user_id == $userId) ? $message->bookings->host_id : $message->bookings->user_id;
            }

            // Fallback: Si sigue sin partner (raro), usar el receiver/sender literal
            if (!$partnerId) {
                $partnerId = ($message->sender_id == $userId) ? $message->receiver_id : $message->sender_id;
            }
            
            $message->chat_partner_id = $partnerId;
            return $message;
        });

        // 3. Group by the resolved Chat Partner
        // Esto crea un sidebar con una entrada por persona
        $data['messages'] = $allMessages->unique('chat_partner_id')->values();

        if (count($data['messages']) > 0) {
            $targetBookingId = $request->id;
            $selectedPartnerId = null;

            if ($targetBookingId) {
                $targetBooking = Bookings::where('id', $targetBookingId)
                    ->where(function($query) use ($userId) {
                        $query->where('user_id', $userId)->orWhere('host_id', $userId);
                    })->first();

                if ($targetBooking) {
                    $selectedPartnerId = ($targetBooking->user_id == $userId) ? $targetBooking->host_id : $targetBooking->user_id;
                    $data['booking'] = $targetBooking->load('users', 'properties');
                }
            }

            // Si no hay booking específico en la URL, tomar el primero del sidebar
            if (!$selectedPartnerId) {
                $first = $data['messages']->first();
                $selectedPartnerId = $first->chat_partner_id;
                // Intentamos cargar el booking real si existe, sino el de consulta
                $data['booking'] = Bookings::where('id', $first->booking_id)->with('users', 'properties')->first();
            }

            // 4. Load the ENTIRE history with this partner (UNIFIED)
            // Usamos el mismo nombre de variable que en el AJAX para consistencia ($messages)
            // Pero también pasamos $conversation para compatibilidad con la vista index
            $data['messages_unified'] = Messages::where(function($query) use ($userId, $selectedPartnerId) {
                // Mensajes directos entre ambos
                $query->where(function($q) use ($userId, $selectedPartnerId) {
                    $q->where('sender_id', $userId)->where('receiver_id', $selectedPartnerId);
                })->orWhere(function($q) use ($userId, $selectedPartnerId) {
                    $q->where('sender_id', $selectedPartnerId)->where('receiver_id', $userId);
                })
                // O mensajes vinculados a CUALQUIER reserva que compartan
                ->orWhereHas('bookings', function($q) use ($userId, $selectedPartnerId) {
                    $q->where(function($sub) use ($userId, $selectedPartnerId) {
                        $sub->where('user_id', $userId)->where('host_id', $selectedPartnerId);
                    })->orWhere(function($sub) use ($userId, $selectedPartnerId) {
                        $sub->where('user_id', $selectedPartnerId)->where('host_id', $userId);
                    });
                });
            })->orderBy('id', 'asc')->get();

            $data['conversation'] = $data['messages_unified'];
            
            Log::info("REDA Inbox Index: User $userId with Partner $selectedPartnerId. Unified history: " . $data['conversation']->count());

            if ($data['booking']) {
                $data['symbol'] = Currency::getAll()->firstWhere('code', $data['booking']->currency_code)->symbol ?? '$';
            }
         }
        
        return view('users.inbox', $data);
    }

    /**
     * Load Conversation Details via Ajax
     */
    public function message(Request $request)
    {
        $userId = Auth::id();
        $booking_id = $request->id;
        $targetBooking = Bookings::findOrFail($booking_id);
        
        $partnerId = ($targetBooking->user_id == $userId) ? $targetBooking->host_id : $targetBooking->user_id;

        Log::info("REDA Inbox AJAX Trace: User $userId, Booking $booking_id, Partner $partnerId");

        // Mark all related messages as read
        Messages::whereHas('bookings', function($q) use ($userId, $partnerId) {
            $q->where(function($sub) use ($userId, $partnerId) {
                $sub->where('user_id', $userId)->where('host_id', $partnerId);
            })->orWhere(function($sub) use ($userId, $partnerId) {
                $sub->where('user_id', $partnerId)->where('host_id', $userId);
            });
        })->where('receiver_id', $userId)->update(['read' => 1]);

        // Cargar historia unificada
        $messages_query = Messages::where(function($query) use ($userId, $partnerId) {
                $query->where(function($q) use ($userId, $partnerId) {
                    $q->where('sender_id', $userId)->where('receiver_id', $partnerId);
                })->orWhere(function($q) use ($userId, $partnerId) {
                    $q->where('sender_id', $partnerId)->where('receiver_id', $userId);
                })
                ->orWhereHas('bookings', function($q) use ($userId, $partnerId) {
                    $q->where(function($sub) use ($userId, $partnerId) {
                        $sub->where('user_id', $userId)->where('host_id', $partnerId);
                    })->orWhere(function($sub) use ($userId, $partnerId) {
                        $sub->where('user_id', $partnerId)->where('host_id', $userId);
                    });
                });
            })->orderBy('id', 'asc');

        $data['messages'] = $messages_query->get();

        Log::info("REDA Inbox AJAX Trace: Messages found count: " . $data['messages']->count() . ". SQL: " . $messages_query->toSql() . " - Bindings: " . json_encode($messages_query->getBindings()));

        $data['booking'] = $targetBooking->load('host', 'users', 'properties');
        $data['symbol'] = Currency::getAll()->firstWhere('code', $data['booking']->currency_code)->symbol ?? '$';

        return response()->json([
             "inbox" => view('users.messages', $data)->render(), 
             "booking" => view('users.booking', $data)->render()
        ]);
    }

    /**
    * Unified Reply
    */
    public function messageReply(Request $request)
    {
        $rules = array('msg' => 'required|string');
        $validator = Validator::make($request->all(), $rules);

        if (!$validator->fails()) {
            $message = new Messages;
            $message->property_id = $request->property_id;
            $message->booking_id = $request->booking_id;
            $message->receiver_id = $request->receiver_id;
            $message->sender_id = Auth::id();
            $message->message = $request->msg;
            $message->type_id = 1;
            $message->save();

            // Marcar como leídos los recibidos en este contexto
            Messages::where('booking_id', $request->booking_id)
                   ->where('receiver_id', Auth::id())
                   ->update(['read' => 1]);
                   
            return 1;
        }
        return 0;
    }
}
