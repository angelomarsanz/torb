<?php

namespace Reda\RedaAlojamiento\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class SoporteTecnico extends Model
{
    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'soportes_tecnicos';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'tema',
        'mensaje_usuario',
        'link_error',
        'asignado_a',
        'estatus',
        'fecha_cambio_estatus',
        'fecha_prometido_para',
        'mensaje_soporte_tecnico',
        'prioridad',
        'visto_por_admin',
        'visto_por_usuario'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'fecha_cambio_estatus' => 'datetime',
        'fecha_prometido_para' => 'datetime',
        'visto_por_admin'      => 'boolean',
        'visto_por_usuario'    => 'boolean',
        'link_error'           => 'array',
    ];

    /**
     * Relación con el usuario que creó la solicitud.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
