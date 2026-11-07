<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comision extends Model
{
    use HasFactory;

    // Indicamos explícitamente el nombre de la tabla con el prefijo laravel_
    protected $table = 'laravel_comisiones';

    protected $fillable = [
        'empresa_gestora_id',
        'incidencia_id',
        'importe',
        'estado', // 'Pendiente', 'Pagada'
    ];

    // Relación con la empresa gestora
    public function empresaGestora()
    {
        return $this->belongsTo(EmpresaGestora::class, 'empresa_gestora_id');
    }

    // Relación con la incidencia asociada
    public function incidencia()
    {
        return $this->belongsTo(Incidencia::class, 'incidencia_id');
    }
}