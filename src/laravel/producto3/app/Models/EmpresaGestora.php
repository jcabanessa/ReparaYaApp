<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaGestora extends Model
{
    use HasFactory;

    protected $table = 'empresas_gestoras';

    protected $fillable = [
        'nombre',
        'usuario_id',
        'cif',
        'porcentaje_comision',
    ];

    // Relación: Una empresa gestora tiene muchas comisiones registradas
    public function comisiones()
    {
        return $this->hasMany(Comision::class, 'empresa_gestora_id');
    }

    // Relación: Una empresa gestora pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function comunidades() {
        return $this->hasMany(Comunidad::class, 'empresa_gestora_id');
    }

    public function incidencias() {
        return $this->hasMany(Incidencia::class, 'empresa_gestora_id');
    }
}