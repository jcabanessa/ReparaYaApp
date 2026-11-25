<?php
namespace App\Services;
use App\Models\{Comision,Incidencia};
/** Adaptador: conserva el cálculo de comisiones del modelo implementado por el equipo. */
class ComisionService {
    public function generar(Incidencia $incidencia): ?Comision {
        return Comision::generarParaIncidencia($incidencia);
    }
}
