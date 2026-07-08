<?php

namespace Reda\RedaAlojamiento\Models\Experiencia;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class FavoritoComercio extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'favoritos_comercios';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'experiencia_id'
    ];

    /**
     * Relación: Un favorito pertenece a un Usuario.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación: Un favorito pertenece a una Experiencia (Comercio).
     */
    public function experiencia()
    {
        return $this->belongsTo(Experiencia::class, 'experiencia_id');
    }
}
