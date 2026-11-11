<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Comunidad extends Model {
    protected $table = 'comunidades';
    protected $fillable = [
        'nombre', 
        'direccion',
        'empresa_gestora_id',];

    // Relación: Una comunidad pertenece a una empresa gestora
    public function empresaGestora() {
        return $this->belongsTo(EmpresaGestora::class, 'empresa_gestora_id');
    }

    
}
