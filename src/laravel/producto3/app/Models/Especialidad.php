<?php  

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    protected $table = 'especialidades';
    public $timestamps = false;

    protected $fillable = ['nombre', 'precio_base'];

    protected $casts = [
        'precio_base' => 'decimal:2',
    ];

    public function incidencias()
    {
        return $this->hasMany(Incidencia::class, 'especialidad_id');
    }
}
