<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Modelo Eloquent que representa el perfil operativo de un técnico
 * y lo vincula con su cuenta de usuario y su especialidad asignada.
 */
class Tecnico extends Model
{
    // Tabla heredada del Producto 2 (Laravel añade el prefijo 'laravel_' automáticamente)
    protected $table = 'tecnicos';

    // Desactivamos timestamps porque la tabla original no dispone de created_at ni updated_at
    public $timestamps = false;

    // Campos permitidos para asignación masiva desde el CRUD de administración
    protected $fillable = [
        'usuario_id',
        'nombre_completo',
        'especialidad_id',
        'disponible',
    ];

    /**
     * Relación inversa 1:1 -> Vincula el perfil técnico con sus credenciales en la tabla usuarios.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Relación inversa N:1 -> Indica la especialidad (tipo de servicio) asociada al técnico.
     */

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }
}
