<?php

namespace Reda\RedaAlojamiento\Models\Disputa;

use Illuminate\Database\Eloquent\Model;

class MensajeMetadata extends Model
{
    protected $table = 'reda_mensajes_metadata';

    protected $fillable = [
        'message_id',
        'sender_type'
    ];

    public function message()
    {
        return $this->belongsTo('App\Models\Messages', 'message_id', 'id');
    }
}
