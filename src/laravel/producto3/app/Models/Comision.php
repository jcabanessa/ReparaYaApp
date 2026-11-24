<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comision extends Model
{
    use HasFactory;

    protected $table = 'comisiones';

    protected $fillable = [
        'empresa_gestora_id',
        'incidencia_id',
        'importe',
        'estado', // 'Pendiente', 'Pagada'
    ];

    public function empresaGestora()
    {
        return $this->belongsTo(EmpresaGestora::class, 'empresa_gestora_id');
    }

    public function incidencia()
    {
        return $this->belongsTo(Incidencia::class, 'incidencia_id');
    }

    /**
     * Punto 6.e: Genera automáticamente la comisión B2B aplicando el porcentaje de la gestora
     * sobre el precio_base del servicio, solo cuando la incidencia alcanza 'Finalizada'.
     */
    public static function generarParaIncidencia(Incidencia $incidencia): ?self
    {
        if ($incidencia->estado !== 'Finalizada') {
            return null;
        }

        $incidencia->loadMissing(['especialidad', 'empresaGestora', 'comunidad.empresaGestora']);

        $gestora = $incidencia->empresaGestora
            ?? ($incidencia->comunidad ? $incidencia->comunidad->empresaGestora : null);

        if (!$gestora || (float) $gestora->porcentaje_comision <= 0) {
            return null;
        }

        $precioBase = (float) ($incidencia->especialidad->precio_base ?? 0);

        if ($precioBase <= 0) {
            return null;
        }

        $importeComision = round(($precioBase * (float) $gestora->porcentaje_comision) / 100, 2);

        return self::firstOrCreate(
            ['incidencia_id' => $incidencia->id],
            [
                'empresa_gestora_id' => $gestora->id,
                'importe'            => $importeComision,
                'estado'             => 'Pendiente',
            ]
        );
    }
}