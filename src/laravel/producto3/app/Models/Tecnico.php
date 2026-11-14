<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tecnico extends Model
{
    protected $table = 'laravel_tecnicos';
    public $timestamps = false; // Cambiar a true si tu tabla tiene created_at/updated_at

    protected $fillable = [
        'usuario_id',
        'nombre_completo',
        'especialidad_id',
        'disponible'
    ];

    // Relación con el usuario (para email y contraseña de acceso)
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Relación con la especialidad del técnico
    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }
}