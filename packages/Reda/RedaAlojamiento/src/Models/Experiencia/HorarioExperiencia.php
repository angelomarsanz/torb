<?php

namespace Reda\RedaAlojamiento\Models\Experiencia;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Reda\RedaAlojamiento\Models\Experiencia\Experiencia;
use Reda\RedaAlojamiento\Models\Experiencia\ReservacionExperiencia;

class HorarioExperiencia extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'horarios_experiencias';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'experiencia_id',
        'fecha_hora_desde',
        'fecha_hora_hasta',
        'cupos_planificados',
    ];

    /**
     * Get the property that owns the experience.
     */
    public function experiencia()
    {
        return $this->belongsTo(Experiencia::class, 'experiencia_id');
    }

    /**
     * Relación: un horario puede tener muchas reservaciones.
     */
    public function reservaciones()
    {
        return $this->hasMany(ReservacionExperiencia::class, 'horarios_experiencia_id');
    }
}
