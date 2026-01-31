<?php

namespace Reda\RedaAlojamiento\Models\Experiencia; 

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FotoExperiencia extends Model
{
    use HasFactory;

    protected $table   = 'fotos_experiencias';
    public $timestamps = false; // Igual que en el original PropertyPhotos

    protected $fillable = [
        'experiencia_id',
        'photo',
        'message',
        'cover_photo',
        'serial'
    ];

    /**
     * Relación inversa: Una foto pertenece a una experiencia.
     */
    public function experiencia()
    {
        return $this->belongsTo(Experiencia::class, 'experiencia_id', 'id');
    }
}