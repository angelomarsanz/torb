<?php

// CAMBIO CRUCIAL 1: Nuevo namespace del paquete + la subcarpeta Experiencia
namespace Reda\RedaAlojamiento\Models\Experiencia; 

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Importamos el modelo User original

// CAMBIO CRUCIAL 2: Los modelos relacionados deben usar el nuevo namespace del paquete y la subcarpeta
use Reda\RedaAlojamiento\Models\Experiencia\ActividadExperiencia;
use Reda\RedaAlojamiento\Models\Experiencia\HorarioExperiencia;
use Reda\RedaAlojamiento\Models\Experiencia\ReservacionExperiencia;
use Reda\RedaAlojamiento\Models\Experiencia\InformacionExperiencia;

class Experiencia extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    // NOTA: Si ya ejecutaste la migración con 'experiencias', déjalo así. 
    // Si quieres usar el nombre recomendado del paquete, debería ser 'reda_experiencias'.
    protected $table = 'experiencias'; 

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', // ¡No olvides añadirlo al fillable!
        'titulo',
        'descripcion',
        'ruta_imagenes',
        'latitud_encuentro',
        'longitud_encuentro',
        'tipo_moneda',
        'precio_persona',
        'precio_grupo',
        'minimo_personas_grupo',
        'reglas_cancelacion'
    ];

    /**
     * Relación: Una experiencia pertenece a un Usuario (Host).
     */
    public function owner()
    {
        // Definimos la relación inversa del uno a muchos
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación: fotos (Coincide con tu búsqueda)
     */
    public function fotos()
    {
        return $this->hasMany(FotoExperiencia::class, 'experiencia_id');
    }

    /**
     * Get the activities for the experience.
     */
    public function actividades()
    {
        // Las relaciones usan ActividadesExperiencia::class, que ahora apunta a la clase correcta importada arriba.
        return $this->hasMany(ActividadExperiencia::class, 'experiencia_id');
    }

    /**
     * Get the schedules for the experience.
     */
    public function horarios()
    {
        return $this->hasMany(HorarioExperiencia::class, 'experiencia_id');
    }

    /**
     * Relación: una experiencia puede tener muchas reservaciones.
     */
    public function informaciones()
    {
        // Usa el modelo InformacionExperiencia que ahora está importado.
        return $this->hasMany(InformacionExperiencia::class, 'experiencia_id');
    }

    /**
     * Relación: una experiencia puede tener muchas reservaciones.
     */
    public function anfitrion()
    {
        // Si una experiencia solo tiene un registro de anfitrión:
        return $this->hasOne(AnfitrionExperiencia::class, 'experiencia_id');
    }
     public function reservaciones()
    {
        return $this->hasMany(ReservacionExperiencia::class, 'experiencia_id');
    }
}