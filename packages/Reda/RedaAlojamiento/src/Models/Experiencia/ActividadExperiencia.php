<?php

namespace Reda\RedaAlojamiento\Models\Experiencia;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Reda\RedaAlojamiento\Models\Experiencia\Experiencia;
use App\Models\Currency; 

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
        'nombre_experiencia', 
        'descripcion_actividad',
        'foto_actividad',
        'orden_actividad',
        'precio',             
        'currency_id',        
        'disponibilidad'      
    ];

    /**
     * Get the property that owns the experience.
     */
    public function experiencia()
    {
        return $this->belongsTo(Experiencia::class, 'experiencia_id');
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }
}
