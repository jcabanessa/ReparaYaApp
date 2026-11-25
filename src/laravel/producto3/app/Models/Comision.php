<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Comision extends Model {
    protected $table='comisiones';
    protected $fillable=['empresa_gestora_id','incidencia_id','precio_base_aplicado',
        'porcentaje_aplicado','importe','fecha_finalizacion','estado'];
    protected function casts():array{return ['precio_base_aplicado'=>'decimal:2','porcentaje_aplicado'=>'decimal:2',
        'importe'=>'decimal:2','fecha_finalizacion'=>'datetime'];}
    public function incidencia():BelongsTo{return $this->belongsTo(Incidencia::class,'incidencia_id');}
    public function gestora():BelongsTo{return $this->belongsTo(EmpresaGestora::class,'empresa_gestora_id');}
}
