<?php

namespace Reda\RedaAlojamiento\Models\Experiencia;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

use Reda\RedaAlojamiento\Models\Experiencia\Experiencia; 
use Reda\RedaAlojamiento\Models\Experiencia\HorarioExperiencia;

class ReservacionExperiencia extends Model
{
    use HasFactory;

    // Nombre de tabla en plural en español
    protected $table = 'reservaciones_experiencias';

    // Campos asignables en masa
    protected $fillable = [
        'experiencia_id',
        'user_id',
        'horarios_experiencia_id',
        'tipo_reservacion',
        'cantidad_personas',
        'monto_pagado',
        'cancelado',
        'devolucion_dinero',
    ];

    /**
     * Relación: una reservación pertenece a una experiencia.
     */
    public function experiencia()
    {
        return $this->belongsTo(Experiencia::class, 'experiencia_id');
    }

    /**
     * Relación: una reservación pertenece a un usuario.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación: una reservación pertenece a un horario de experiencia.
     */
    public function horario()
    {
        return $this->belongsTo(HorarioExperiencia::class, 'horarios_experiencia_id');
    }
}
