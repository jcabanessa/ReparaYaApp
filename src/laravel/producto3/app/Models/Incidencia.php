<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incidencia extends Model
{
    use HasFactory;

    protected function casts(): array {
        return ['fecha_servicio'=>'datetime', 'finalizada_at'=>'datetime', 'precio_aplicado'=>'decimal:2'];
    }

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
        'franja_horaria',
        'telefono_contacto',
        'finalizada_at',
    ];

    // Relación: Pertenece a un cliente (Usuario)
    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    // Relación: Pertenece a un técnico (Usuario)
    public function técnico()
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    // Relación: Pertenece a una comunidad
    public function comunidad()
    {
        return $this->belongsTo(Comunidad::class, 'comunidad_id');
    }

    // Relación: Pertenece a una especialidad (tipo de servicio y precio base)
    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
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

    public function tecnico() { return $this->belongsTo(Tecnico::class, 'tecnico_id'); }
    public function historial() { return $this->hasMany(HistorialEstado::class, 'incidencia_id')->orderBy('created_at')->orderBy('id'); }
}