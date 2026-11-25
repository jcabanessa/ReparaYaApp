<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Especialidad extends Model {
    protected $table='especialidades';public $timestamps=false;
    protected $fillable=['nombre_especialidad','precio_base'];
    protected function casts():array{return ['precio_base'=>'decimal:2'];}
    public function tecnicos():HasMany{return $this->hasMany(Tecnico::class,'especialidad_id');}
}
