<?php

namespace Reda\RedaAlojamiento\Http\Controllers\Admin\Disputa;

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

        // Obtener metadatos de los mensajes para diferenciar admins de users
        $messageIds = $messages->pluck('id');
        $metadata = MensajeMetadata::whereIn('message_id', $messageIds)->get()->keyBy('message_id');

        // Identificar IDs de administradores para cargar sus nombres
        $adminIds = [];
        foreach ($messages as $message) {
            // Previsión: Si no hay metadata (mensajes antiguos o de bandeja global), default a 'user'
            $message->sender_type = isset($metadata[$message->id]) ? $metadata[$message->id]->sender_type : 'user';
            if ($message->sender_type === 'admin') {
                $adminIds[] = $message->sender_id;
            }
        }

        $admins = Admin::whereIn('id', array_unique($adminIds))->get()->keyBy('id');

        foreach ($messages as $message) {
            if ($message->sender_type === 'admin') {
                $admin = $admins->get($message->sender_id);
                $message->sender_name = $admin ? $admin->username : __('Administrador');
            } else {
                // Previsión para nombres de usuario en mensajes sin metadata o antiguos
                $message->sender_name = $message->sender ? ($message->sender->first_name . ' ' . $message->sender->last_name) : __('Sistema');
            }
        }

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
        $message->receiver_id = $request->receiver_id ?? 0; // 0 indica broadcast/grupo para mediaciones
        
        $adminId = Auth::guard('admin')->id();
        $message->sender_id   = $adminId; 
        
        $message->message     = $request->message;
        $message->type_id     = 1; // Tipo query/chat
        $message->read        = 0;
        $message->save();

        // Nota: La metadata se crea automáticamente mediante MensajeObserver

        return response()->json([
            'success' => true,
            'message' => __('Mensaje enviado'),
            'mensaje_usuario' => __('El mensaje ha sido enviado correctamente'),
            'respuesta' => $message,
            'code' => 200
        ], 200);
    }
}
