<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
class User extends Authenticatable {
    use Notifiable;
    protected $table='usuarios';
    protected $fillable=['nombre','email','telefono','password','rol'];
    protected $hidden=['password','remember_token'];
    protected function casts():array {return ['password'=>'hashed'];}
    public function getAuthPasswordName(){return 'password';}
    public function gestora():HasOne {return $this->hasOne(EmpresaGestora::class,'usuario_id');}
    public function tecnico():HasOne {return $this->hasOne(Tecnico::class,'usuario_id');}
    public function avisos():HasMany {return $this->hasMany(Incidencia::class,'cliente_id');}
}
