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
        'cif',
        'telefono',
        'email',
    ];

    // Relación: Una empresa gestora tiene muchas comisiones registradas
    public function comisiones()
    {
        return $this->hasMany(Comision::class, 'empresa_gestora_id');
    }
}