<?php

namespace Reda\RedaAlojamiento\Models\Experiencia;

use Illuminate\Database\Eloquent\Model;

class PlanNegocio extends Model
{
    protected $table = 'planes_negocios';

    protected $fillable = [
        'nombre',
        'precio',
        'moneda',
        'lapso_pago',
        'beneficios',
        'destacado',
        'estatus',
        'orden'
    ];

    protected $casts = [
        'beneficios' => 'array',
        'destacado'  => 'boolean',
        'estatus'    => 'boolean',
        'precio'     => 'decimal:2'
    ];
}
