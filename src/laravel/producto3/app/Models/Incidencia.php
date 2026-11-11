<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incidencia extends Model
{
    use HasFactory;

    protected $table = 'incidencias';

    protected $fillable = [
        'localizador',
        'cliente_id',
        'tecnico_id',
        'comunidad_id',
        'zona_id',
        'especialidad_id',
        'empresa_gestora_id',
        'tipo_urgencia',
        'fecha_servicio',
        'estado',
        'direccion',
        'precio_aplicado',
        'descripcion',
    ];

    // Relación: Pertenece a un cliente (Usuario)
    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    // Relación: Pertenece a un técnico (Usuario)
    public function tecnico()
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    // Relación: Pertenece a una comunidad
    public function comunidad()
    {
        return $this->belongsTo(Comunidad::class, 'comunidad_id');
    }

    // Relación: Pertenece a una zona
    public function zona()
    {
        return $this->belongsTo(Zona::class, 'zona_id');
    }

    // Relación: Tiene una comisión asociada (si aplica)
    public function comision()
    {
        return $this->hasOne(Comision::class, 'incidencia_id');
    }

    // Relación: Pertenece a una empresa gestora
    public function empresaGestora()
    {
        return $this->belongsTo(EmpresaGestora::class, 'empresa_gestora_id');
    }
}