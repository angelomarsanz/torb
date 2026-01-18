<?php

namespace Reda\RedaAlojamiento\Models\Experiencia;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Reda\RedaAlojamiento\Models\Experiencia\Experiencia;

class ActividadExperiencia extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'actividades_experiencias';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'experiencia_id',
        'descripcion_actividad',
        'foto_actividad',
    ];

    /**
     * Get the property that owns the experience.
     */
    public function experiencia()
    {
        return $this->belongsTo(Experiencia::class, 'experiencia_id');
    }
}
