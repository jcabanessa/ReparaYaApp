<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Eloquent que representa los tipos de servicio (especialidades)
 * y su tarifa base para el cálculo de presupuestos y comisiones B2B.
 */
class Especialidad extends Model
{
    // Tabla heredada del Producto 2 (Laravel añade el prefijo 'laravel_' automáticamente)
    protected $table = 'especialidades';

    // Desactivamos timestamps porque la tabla original no dispone de created_at ni updated_at
    public $timestamps = false;

    // Campos permitidos para asignación masiva desde el CRUD de administración
    protected $fillable = [
        'nombre_especialidad',
        'precio_base',
    ];

    /**
     * Relación 1:N -> Una especialidad puede estar asignada a múltiples técnicos.
     */
    public function tecnicos()
    {
        return $this->hasMany(Tecnico::class, 'especialidad_id');
    }
}