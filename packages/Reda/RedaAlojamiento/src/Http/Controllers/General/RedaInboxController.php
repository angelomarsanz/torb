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
        
        // 1. Obtener todos los mensajes donde participa el usuario
        $allMessages = Messages::with(['bookings', 'properties:id,name', 'sender', 'receiver'])
            ->where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->orderBy('id', 'desc')
            ->get();

        // 2. Identificar al "Chat Partner" único
        $allMessages->transform(function ($message) use ($userId) {
            $message->chat_partner_id = ($message->sender_id == $userId) ? $message->receiver_id : $message->sender_id;
            return $message;
        });

        // 3. Generar lista del Sidebar
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

            // 4. Cargar HISTORIA UNIFICADA con carga ansiosa de remitentes
            $unifiedHistory = Messages::with(['sender', 'receiver'])
                ->where(function($query) use ($userId, $selectedPartnerId) {
                    $query->where(function($q) use ($userId, $selectedPartnerId) {
                        $q->where('sender_id', $userId)->where('receiver_id', $selectedPartnerId);
                    })->orWhere(function($q) use ($userId, $selectedPartnerId) {
                        $q->where('sender_id', $selectedPartnerId)->where('receiver_id', $userId);
                    });
                })->orderBy('id', 'asc')->get();

            // --- VIRTUALIZACIÓN DE BOOKING ID (Estrategia Clave) ---
            // Forzamos a que todos los mensajes parezcan pertenecer a la reserva activa en la vista
            // para evitar que el JS los filtre por booking_id discrepante.
            $unifiedHistory->each(function($msg) use ($targetBookingId) {
                $msg->booking_id = (int) $targetBookingId;
            });

            // Sincronización de nombres para las vistas originales
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

        // Cargar HISTORIA UNIFICADA para el AJAX con carga ansiosa
        $unifiedHistory = Messages::with(['sender', 'receiver'])
            ->where(function($query) use ($userId, $partnerId) {
                $query->where(function($q) use ($userId, $partnerId) {
                    $q->where('sender_id', $userId)->where('receiver_id', $partnerId);
                })->orWhere(function($q) use ($userId, $partnerId) {
                    $q->where('sender_id', $partnerId)->where('receiver_id', $userId);
                });
            })->orderBy('id', 'asc')->get();

        // --- VIRTUALIZACIÓN DE BOOKING ID PARA AJAX ---
        $unifiedHistory->each(function($msg) use ($booking_id) {
            $msg->booking_id = (int) $booking_id;
        });

        $data['messages'] = $unifiedHistory;
        $data['conversation'] = $unifiedHistory;
        $data['booking'] = $targetBooking->load('host', 'users', 'properties');
        $data['symbol'] = Currency::getAll()->firstWhere('code', $data['booking']->currency_code)->symbol ?? '$';

        return response()->json([
             "inbox" => view('users.messages', $data)->render(), 
             "booking" => view('users.booking', $data)->render()
        ]);
    }

    /**
    * Unified Reply via REDA Ajax Route
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

            Messages::where('booking_id', $request->booking_id)
                   ->where('receiver_id', Auth::id())
                   ->update(['read' => 1]);
                   
            return 1;
        }
        return 0;
    }
}
