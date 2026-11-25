<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
class Incidencia extends Model {
    protected $table='incidencias';
    const CREATED_AT='created_at';const UPDATED_AT='updated_at';
    protected $fillable=['localizador','cliente_id','tecnico_id','especialidad_id','comunidad_id',
        'empresa_gestora_id','descripcion','direccion','fecha_servicio','franja_horaria','telefono_contacto',
        'tipo_urgencia','estado','precio_aplicado','finalizada_at'];
    protected function casts():array{return ['fecha_servicio'=>'datetime','finalizada_at'=>'datetime','precio_aplicado'=>'decimal:2'];}
    public function cliente():BelongsTo{return $this->belongsTo(User::class,'cliente_id');}
    public function tecnico():BelongsTo{return $this->belongsTo(Tecnico::class,'tecnico_id');}
    public function especialidad():BelongsTo{return $this->belongsTo(Especialidad::class,'especialidad_id');}
    public function comunidad():BelongsTo{return $this->belongsTo(Comunidad::class,'comunidad_id');}
    public function gestora():BelongsTo{return $this->belongsTo(EmpresaGestora::class,'empresa_gestora_id');}
    public function historial():HasMany{return $this->hasMany(HistorialEstado::class,'incidencia_id')->orderBy('created_at')->orderBy('id');}
    public function comision():HasOne{return $this->hasOne(Comision::class,'incidencia_id');}
}
