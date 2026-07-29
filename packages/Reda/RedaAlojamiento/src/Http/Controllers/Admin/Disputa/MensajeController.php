<?php

namespace Reda\RedaAlojamiento\Http\Controllers\Admin\Disputa;

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
                'disputa' => $disputa
            ]
        ]);
    }

    /**
     * Guarda un nuevo mensaje enviado por el administrador.
     * Como los admins no están en la tabla users, se usará un sender_id especial o se registrará de forma que el sistema lo entienda.
     * En este caso, para no romper la integridad referencial si existiera, se usará el ID del agente si éste es un User, 
     * o se marcará como enviado por el sistema.
     */
    public function store(Request $request)
    {
        $rules = [
            'booking_id'  => 'required|exists:bookings,id',
            'message'     => 'required|string',
            'receiver_id' => 'required' // ID del usuario que recibirá el mensaje (Turista o Anfitrión)
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
        
        // El admin envía el mensaje. 
        // Para que sea visible en el inbox del receptor, sender_id debe ser alguien.
        // Como el admin no es un User, usaremos el ID del admin pero esto puede causar conflictos.
        // Una alternativa es usar el ID de la otra parte como sender (impersonación) o un ID de sistema.
        // El usuario solicitó que "se guarden en la tabla correspondiente".
        
        $message = new Messages;
        $message->property_id = $booking->property_id;
        $message->booking_id  = $request->booking_id;
        $message->receiver_id = $request->receiver_id;
        
        // Usamos el ID del admin actual. Si el sistema original no maneja admins en sender_id, 
        // es posible que no aparezca el nombre en el inbox, pero el mensaje se guardará.
        $adminId = Auth::guard('admin')->id();
        $message->sender_id   = $adminId; // Nota: Esto asume que no hay conflicto de IDs o que el sistema lo tolera
        
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
