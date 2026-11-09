<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zona extends Model
{
    protected $table = 'zonas';

    protected $fillable = [
        'nombre',
        'comunidad_id',
    ];

    // Relación: Una zona pertenece a una comunidad
    public function comunidad()
    {
        return $this->belongsTo(Comunidad::class, 'comunidad_id');
    }
}
