<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class HistorialEstado extends Model {
    protected $table='historial_estados';public $timestamps=false;
    protected $fillable=['incidencia_id','estado_anterior','nuevo_estado','usuario_id','observaciones','created_at'];
    protected function casts():array{return ['created_at'=>'datetime'];}
    public function autor():BelongsTo{return $this->belongsTo(User::class,'usuario_id');}
    public function incidencia():BelongsTo{return $this->belongsTo(Incidencia::class,'incidencia_id');}
}
