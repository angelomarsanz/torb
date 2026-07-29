<?php

namespace Reda\RedaAlojamiento\Observers;

use App\Models\Messages;
use Reda\RedaAlojamiento\Models\Disputa\MensajeMetadata;
use Reda\RedaAlojamiento\Models\Disputa\Disputa;
use Illuminate\Support\Facades\DB;
use Auth;

class MensajeObserver
{
    /**
     * Se ejecuta antes de que un mensaje sea creado.
     */
    public function creating(Messages $message)
    {
        if ($message->booking_id) {
            $disputa = Disputa::where('booking_id', $message->booking_id)->first();

            // Si existe una mediación y no está cerrada
            if ($disputa && !in_array(strtolower($disputa->estado), ['cerrado', 'cerrada'])) {
                $type = DB::table('message_type')->where('name', 'disputas')->first();
                if ($type) {
                    $message->type_id = $type->id;
                }
            }
        }
    }

    /**
     * Se ejecuta después de que un mensaje es creado en cualquier parte del sistema.
     */
    public function created(Messages $message)
    {
        // Evitar duplicados si el controlador ya lo guardó manualmente
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
