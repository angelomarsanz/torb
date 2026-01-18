<?php

namespace Reda\RedaAlojamiento\Models\Experiencia;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Reda\RedaAlojamiento\Models\Experiencia\Experiencia;

class InformacionExperiencia extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'informaciones_experiencias';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'experiencia_id',
        'requisitos_viajero',
        'nivel_actividad',
        'que_debes_traer',
        'accesibilidad',
    ];

    /**
     * Get the property that owns the experience.
     */
    public function experiencia()
    {
        return $this->belongsTo(Experiencia::class, 'experiencia_id');
    }
}
