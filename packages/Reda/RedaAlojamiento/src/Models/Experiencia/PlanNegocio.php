<?php

namespace Reda\RedaAlojamiento\Models\Experiencia;

use Illuminate\Database\Eloquent\Model;

class PlanNegocio extends Model
{
    protected $table = 'planes_negocios';

    protected $fillable = [
        'nombre',
        'planes_pago',
        'beneficios',
        'destacado',
        'estatus',
        'orden'
    ];

    protected $casts = [
        'planes_pago' => 'array',
        'beneficios'  => 'array',
        'destacado'   => 'boolean',
        'estatus'     => 'boolean'
    ];
}
