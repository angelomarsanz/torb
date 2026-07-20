<?php

namespace Reda\RedaAlojamiento\Models\Disputa;

use Illuminate\Database\Eloquent\Model;
use App\Models\Bookings;

class Disputa extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'disputas';

    /**
     * Atributos asignables de forma masiva.
     *
     * @var array
     */
    protected $fillable = [
        'booking_id',
        'estado',
        'paso_actual',
        'ultima_actividad',
        'prioridad',
        'fecha_apertura',
        'fecha_limite',
        'id_usuario_agente_asignado',
        'id_usuario_turista',
        'id_usuario_anfitrion',
        'categoria',
        'motivo',
        'descripcion',
        'documentos_turista',
        'documentos_anfitrion',
        'documentos_agente',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'fecha_apertura' => 'datetime',
        'fecha_limite'   => 'datetime',
    ];

    /**
     * Relación uno a uno con el modelo Bookings.
     */
    public function booking()
    {
        return $this->belongsTo(Bookings::class, 'booking_id');
    }

    /**
     * Relación con el agente asignado (Admin).
     */
    public function agente()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'id_usuario_agente_asignado');
    }
}
