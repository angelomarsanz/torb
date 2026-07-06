<?php

namespace Reda\RedaAlojamiento\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Admin;
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
        'admin_id',
        'estatus',
        'resultado_gestion',
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
     * Accessor para obtener el nombre del comercio asociado a través del link_error.
     * Prioriza el nuevo atributo id_experiencia.
     * Fallback: link_error -> id_de_la_reseña -> CalificacionExperiencia -> Experiencia -> titulo.
     */
    protected function nombreComercio(): Attribute
    {
        return Attribute::make(
            get: function () {
                $datos = $this->link_error;
                if (empty($datos) || !is_array($datos)) return null;

                // 1. Prioridad: Intentamos obtener id_experiencia directamente
                $idExperiencia = $datos['id_experiencia'] ?? null;
                if ($idExperiencia) {
                    $experiencia = \Reda\RedaAlojamiento\Models\Experiencia\Experiencia::find($idExperiencia);
                    if ($experiencia) {
                        return $experiencia->titulo;
                    }
                }

                // 2. Fallback: Lógica antigua basada en el reporte de calificación (para tickets previos)
                $vistaOrigen = $datos['vista_origen'] ?? '';
                if ($vistaOrigen === 'Reportar calificación') {
                    // Soporte para variaciones de nombre de clave y problemas de codificación JSON heredada
                    $idReseña = $datos['id_reseña'] ?? $datos['id_de_la_reseña'] ?? $datos['id_de_la_rese\u00f1a'] ?? null;

                    if ($idReseña) {
                        // Obtenemos la calificación y su comercio (experiencia) vinculado
                        $calificacion = \Reda\RedaAlojamiento\Models\Experiencia\CalificacionExperiencia::with('experiencia')->find($idReseña);
                        if ($calificacion && $calificacion->experiencia) {
                            return $calificacion->experiencia->titulo;
                        }
                    }
                }
                return null;
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

    /**
     * Relación con el administrador que gestionó el ticket.
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Verifica si el recurso vinculado en link_error aún existe en la base de datos.
     * Esto ayuda a determinar si el ticket ya fue gestionado (ej: reseña eliminada).
     */
    public function verificarExistenciaRecurso()
    {
        $datos = $this->link_error;
        if (empty($datos) || !is_array($datos)) return true;

        $vistaOrigen = $datos['vista_origen'] ?? '';

        switch ($vistaOrigen) {
            case 'Reportar calificación':
                // Buscamos el ID en las posibles claves (ñ/n)
                $id = $datos['id_reseña'] ?? $datos['id_de_la_reseña'] ?? $datos['id_de_la_rese\u00f1a'] ?? null;
                if ($id) {
                    return \Reda\RedaAlojamiento\Models\Experiencia\CalificacionExperiencia::where('id', $id)->exists();
                }
                break;

            // Aquí se pueden agregar otros casos a futuro (ej: 'Reportar negocio', 'Reportar mensaje', etc.)
        }

        return true; // Por defecto asumimos que existe si no sabemos cómo verificarlo
    }
}
