<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comunidad;
use App\Models\Incidencia;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ServicioZonaController extends Controller
{
    public function index(): JsonResponse
    {
        $tablaIncidencias = (new Incidencia)->getTable();
        $tablaComunidades = (new Comunidad)->getTable();
        $prefijo = DB::connection()->getTablePrefix();

        $incidenciasReal = $prefijo . $tablaIncidencias;
        $comunidadesReal = $prefijo . $tablaComunidades;

        $filas = Incidencia::query()
            ->join($tablaComunidades, "{$tablaIncidencias}.comunidad_id", '=', "{$tablaComunidades}.id")
            ->where("{$tablaIncidencias}.estado", 'Finalizada')
            ->whereNotNull("{$tablaIncidencias}.comunidad_id")
            ->whereNotNull("{$tablaComunidades}.zona")
            ->whereRaw("TRIM(`{$comunidadesReal}`.`zona`) != ''")
            ->select(
                "{$tablaComunidades}.zona as zona",
                DB::raw('COUNT(*) as servicios_realizados')
            )
            ->groupBy("{$tablaComunidades}.zona")
            ->orderByDesc('servicios_realizados')
            ->get();

        $totalGlobal = (int) $filas->sum('servicios_realizados');

        if ($totalGlobal === 0 || $filas->isEmpty()) {
            return response()->json([
                'data' => [],
                'total_servicios_realizados' => 0,
            ], 200)->header('Access-Control-Allow-Origin', '*');
        }

        $data = $filas->map(function ($fila) use ($totalGlobal) {
            $cantidad = (int) $fila->servicios_realizados;
            $porcentaje = round(($cantidad / $totalGlobal) * 100, 2);

            return [
                'zona' => (string) $fila->zona,
                'servicios_realizados' => $cantidad,
                'porcentaje_total' => $porcentaje,
            ];
        })->values();

        return response()->json([
            'data' => $data,
            'total_servicios_realizados' => $totalGlobal,
        ], 200)->header('Access-Control-Allow-Origin', '*');
    }
}