<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Tecnico extends Model {
    protected $table='tecnicos';public $timestamps=false;
    protected $fillable=['usuario_id','nombre_completo','especialidad_id','disponible'];
    protected function casts():array{return ['disponible'=>'boolean'];}
    public function usuario():BelongsTo{return $this->belongsTo(User::class,'usuario_id');}
    public function especialidad():BelongsTo{return $this->belongsTo(Especialidad::class,'especialidad_id');}
    public function incidencias():HasMany{return $this->hasMany(Incidencia::class,'tecnico_id');}
}
