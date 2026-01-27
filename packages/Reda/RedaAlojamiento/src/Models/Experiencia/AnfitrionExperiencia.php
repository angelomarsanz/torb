<?php

namespace Reda\RedaAlojamiento\Models\Experiencia;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class AnfitrionExperiencia extends Model
{
    use HasFactory, SoftDeletes;

    // Nombre de la tabla en plural en español
    protected $table = 'anfitriones_experiencias';

    // Campos asignables en masa
    protected $fillable = [
        'user_id',
        'experiencia_id',
        'fecha_nacimiento',
        'identidad_verificada',
        'descripcion_anfitrion',
        'trayectoria_profesional',
        'me_dedico_a',
        'intereses_anfitrion',
    ];

    /**
     * Relación: un anfitrión pertenece a un usuario.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function experiencia()
    {
        return $this->belongsTo(Experiencia::class, 'experiencia_id');
    }
}
