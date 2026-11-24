<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Incidencia;
use Illuminate\Support\Facades\DB;

class ServicioZonaController extends Controller
{
    public function index()
    {
        $resultados = Incidencia::query()
            ->join('comunidades', 'incidencias.comunidad_id', '=', 'comunidades.id')
            ->where('incidencias.estado', 'Finalizada')
            ->whereNotNull('comunidades.zona')
            ->select('comunidades.zona as zona', DB::raw('COUNT(incidencias.id) as total_servicios'))
            ->groupBy('comunidades.zona')
            ->orderByDesc('total_servicios')
            ->get();

        $totalGlobal = (int) $resultados->sum('total_servicios');

        if ($totalGlobal === 0) {
            return response()->json([
                'total_finalizados' => 0,
                'zonas'             => [],
            ]);
        }

        $zonas = $resultados->map(function ($item) use ($totalGlobal) {
            $cantidad = (int) $item->total_servicios;
            $porcentaje = round(($cantidad * 100) / $totalGlobal, 2);

            return [
                'zona'                  => $item->zona,
                'servicios_finalizados' => $cantidad,
                'porcentaje'            => $porcentaje,
            ];
        })->values();

        return response()->json([
            'total_finalizados' => $totalGlobal,
            'zonas'             => $zonas,
        ]);
    }
}