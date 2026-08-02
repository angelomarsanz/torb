<?php

namespace Reda\RedaAlojamiento\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\{Bookings, Messages, Properties};
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function iniciarChat($property_id)
    {
        try {
            $user_id = Auth::id();
            $property = Properties::with('property_price')->findOrFail($property_id);
            $host_id = $property->host_id;

            Log::info("REDA Chat: User $user_id starting chat for Property $property_id (Host $host_id)");

            if ($user_id == $host_id) {
                $respuesta = [
                    'success' => false,
                    'message' => 'Host cannot message themselves',
                    'mensaje_usuario' => __('No puedes enviarte un mensaje a ti mismo.'),
                    'respuesta' => '',
                    'code' => 400
                ];
                return request()->ajax() ? response()->json($respuesta, 400) : redirect()->back()->with('error', $respuesta['mensaje_usuario']);
            }

            // 1. Check if there is already a conversation with this host
            $existingMessage = Messages::where(function($query) use ($user_id, $host_id) {
                    $query->where('sender_id', $user_id)->where('receiver_id', $host_id);
                })
                ->orWhere(function($query) use ($user_id, $host_id) {
                    $query->where('sender_id', $host_id)->where('receiver_id', $user_id);
                })
                ->orderBy('id', 'desc')
                ->first();

            if ($existingMessage) {
                $booking_id = $existingMessage->booking_id;
                Log::info("REDA Chat: Reusing existing conversation with Booking ID: $booking_id");
            } else {
                Log::info("REDA Chat: First time chat with this host. Creating Inquiry...");
                
                // 2. No previous conversation, create a new Inquiry booking
                $booking = new Bookings;
                $booking->property_id = $property_id;
                $booking->host_id = $host_id;
                $booking->user_id = $user_id;
                $booking->start_date = date('Y-m-d');
                $booking->end_date = date('Y-m-d', strtotime('+1 day'));
                $booking->status = ''; // Inquiry
                $booking->guest = 1;
                $booking->currency_code = $property->property_price->currency_code ?? 'USD';
                $booking->total_night = 1;
                $booking->per_night = $property->property_price->price ?? 0;
                $booking->total = $booking->per_night;
                $booking->code = 'INQ' . time();
                
                if (!$booking->save()) {
                    Log::error("REDA Chat: Failed to save booking Inquiry.");
                    $respuesta = [
                        'success' => false,
                        'message' => 'Failed to save booking inquiry',
                        'mensaje_usuario' => __('No se pudo iniciar el chat. Por favor intente más tarde.'),
                        'respuesta' => '',
                        'code' => 500
                    ];
                    return request()->ajax() ? response()->json($respuesta, 500) : redirect()->back()->with('error', $respuesta['mensaje_usuario']);
                }

                $booking_id = $booking->id;
                Log::info("REDA Chat: New Inquiry created with ID: $booking_id");

                // 3. Create the first message so it shows up in the inbox
                $message = new Messages;
                $message->property_id = $property_id;
                $message->booking_id = $booking_id;
                $message->receiver_id = $host_id;
                $message->sender_id = $user_id;
                $message->message = __('Hola, estoy interesado en tu propiedad: ') . $property->name;
                $message->type_id = 1; // Standard message
                $message->save();
                
                Log::info("REDA Chat: Initial message created.");
            }

            // Redirect to inbox focusing on this conversation
            $inboxUrl = url('inbox?id=' . $booking_id);
            $respuesta = [
                'success' => true,
                'message' => 'Chat initiated successfully',
                'mensaje_usuario' => __('Iniciando chat...'),
                'respuesta' => $inboxUrl,
                'code' => 200
            ];
            
            return request()->ajax() ? response()->json($respuesta, 200) : redirect($inboxUrl);

        } catch (\Exception $e) {
            Log::error("REDA Chat Error: " . $e->getMessage());
            $respuesta = [
                'success' => false,
                'message' => $e->getMessage(),
                'mensaje_usuario' => __('Error al iniciar chat: ') . $e->getMessage(),
                'respuesta' => '',
                'code' => 500
            ];
            return request()->ajax() ? response()->json($respuesta, 500) : redirect()->back()->with('error', $respuesta['mensaje_usuario']);
        }
    }
}
