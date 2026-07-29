<?php

namespace Reda\RedaAlojamiento\Http\Controllers\Disputa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Messages;
use App\Models\Bookings;
use App\Models\User;
use Reda\RedaAlojamiento\Models\Disputa\Disputa;
use Auth;
use Validator;

class MensajeController extends Controller
{
    /**
     * Obtiene los mensajes de una mediación basada en el booking_id.
     */
    public function getMessages($booking_id)
    {
        $messages = Messages::with(['sender', 'receiver'])
            ->where('booking_id', $booking_id)
            ->orderBy('created_at', 'asc')
            ->get();

        $booking = Bookings::with(['users', 'host'])->find($booking_id);
        $disputa = Disputa::where('booking_id', $booking_id)->first();

        return response()->json([
            'success' => true,
            'respuesta' => [
                'messages' => $messages,
                'booking' => $booking,
                'disputa' => $disputa,
                'current_user_id' => Auth::id()
            ]
        ]);
    }

    /**
     * Guarda un nuevo mensaje enviado por el usuario (Turista o Anfitrión).
     */
    public function store(Request $request)
    {
        $rules = [
            'booking_id'  => 'required|exists:bookings,id',
            'message'     => 'required|string',
            'receiver_id' => 'required' 
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => __('Error de validación'),
                'mensaje_usuario' => __('El mensaje es obligatorio'),
                'respuesta' => $validator->errors(),
                'code' => 422
            ], 422);
        }

        $booking = Bookings::findOrFail($request->booking_id);
        
        $message = new Messages;
        $message->property_id = $booking->property_id;
        $message->booking_id  = $request->booking_id;
        $message->receiver_id = $request->receiver_id;
        $message->sender_id   = Auth::id();
        $message->message     = $request->message;
        $message->type_id     = 1; // Tipo query/chat
        $message->read        = 0;
        $message->save();

        return response()->json([
            'success' => true,
            'message' => __('Mensaje enviado'),
            'mensaje_usuario' => __('El mensaje ha sido enviado correctamente'),
            'respuesta' => $message,
            'code' => 200
        ], 200);
    }
}
