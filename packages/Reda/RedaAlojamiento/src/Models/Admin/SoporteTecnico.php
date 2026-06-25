<?php

namespace Reda\RedaAlojamiento\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;

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
     * Accessor para link_error que maneja posibles múltiples codificaciones JSON de forma recursiva.
     */
    protected function linkError(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (is_null($value)) return [];
                
                $datos = $value;
                
                // Decodificación recursiva: mientras sea un string, intentamos decodificarlo
                // Esto maneja casos de doble o triple codificación JSON heredada
                while (is_string($datos) && !empty($datos)) {
                    $intento = json_decode($datos, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $datos = $intento;
                    } else {
                        // Si falla la decodificación, dejamos de intentar
                        break;
                    }
                }

                // Si al final es un array, lo devolvemos
                if (is_array($datos)) {
                    return $datos;
                }

                // Si quedó como un string no JSON, lo envolvemos en un array bajo la clave 'mensaje'
                return ['url' => $datos];
            },
        );
    }

    /**
     * Relación con el usuario que creó la solicitud.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
