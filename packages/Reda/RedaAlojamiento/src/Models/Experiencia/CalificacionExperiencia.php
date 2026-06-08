<?php

namespace Reda\RedaAlojamiento\Models\Experiencia;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Reda\RedaAlojamiento\Models\Experiencia\Experiencia;

class CalificacionExperiencia extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'calificaciones_experiencias';

    /**
     * Atributos asignables de forma masiva.
     *
     * @var array
     */
    protected $fillable = [
        'experiencia_id',
        'user_id',
        'estrellas',
        'comentario',
    ];

    /**
     * Relación: Una calificación pertenece a una experiencia (negocio).
     */
    public function experiencia()
    {
        return $this->belongsTo(Experiencia::class, 'experiencia_id');
    }

    /**
     * Relación: Una calificación pertenece a un usuario (quien califica).
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
