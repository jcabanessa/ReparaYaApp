<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class EmpresaGestora extends Model {
    protected $table='empresas_gestoras';
    protected $fillable=['usuario_id','nombre','cif','porcentaje_comision'];
    protected function casts():array{return ['porcentaje_comision'=>'decimal:2'];}
    public function usuario():BelongsTo{return $this->belongsTo(User::class,'usuario_id');}
    public function comunidades():HasMany{return $this->hasMany(Comunidad::class,'empresa_gestora_id');}
    public function comisiones():HasMany{return $this->hasMany(Comision::class,'empresa_gestora_id');}
    public function incidencias():HasMany{return $this->hasMany(Incidencia::class,'empresa_gestora_id');}
}
