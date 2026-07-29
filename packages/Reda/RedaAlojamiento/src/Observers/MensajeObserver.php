<?php

namespace Reda\RedaAlojamiento\Observers;

use App\Models\Messages;
use Reda\RedaAlojamiento\Models\Disputa\MensajeMetadata;
use Auth;

class MensajeObserver
{
    /**
     * Se ejecuta después de que un mensaje es creado en cualquier parte del sistema.
     */
    public function created(Messages $message)
    {
        // Evitar duplicados si el controlador ya lo guardó manualmente (aunque lo simplificaremos)
        $exists = MensajeMetadata::where('message_id', $message->id)->exists();
        
        if (!$exists) {
            // Si hay una sesión de admin activa, el remitente es un administrador
            $senderType = Auth::guard('admin')->check() ? 'admin' : 'user';

            MensajeMetadata::create([
                'message_id' => $message->id,
                'sender_type' => $senderType
            ]);
        }
    }
}
