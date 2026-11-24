<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmpresaGestora;
use App\Models\Comision;
use Carbon\Carbon;

class GestoraController extends Controller
{
    public function dashboard()
    {
        $usuario = auth()->user();

        $gestora = EmpresaGestora::where('usuario_id', $usuario->id)
            ->with(['comisiones.incidencia.comunidad', 'comisiones.incidencia.especialidad'])
            ->first();

        return view('gestora.dashboard', compact('gestora'));
    }

    /**
     * Punto 6.f: Liquidaciones mensuales de servicios finalizados con comisión.
     */
    public function liquidaciones()
    {
        $usuario = auth()->user();
        $gestora = EmpresaGestora::where('usuario_id', $usuario->id)->firstOrFail();

        $comisiones = Comision::where('empresa_gestora_id', $gestora->id)
            ->whereHas('incidencia', function ($query) {
                $query->where('estado', 'Finalizada');
            })
            ->with(['incidencia.comunidad', 'incidencia.especialidad'])
            ->get()
            ->sortByDesc(function ($comision) {
                return $comision->incidencia->updated_at ?? $comision->created_at;
            });

        $liquidacionesPorMes = $comisiones->groupBy(function ($comision) {
            $fechaFin = $comision->incidencia->updated_at ?? $comision->created_at;
            return Carbon::parse($fechaFin)->format('Y-m');
        })->map(function ($items, $mesClave) use ($gestora) {
            $primerItem = $items->first();
            $fechaRef = Carbon::parse($primerItem->incidencia->updated_at ?? $primerItem->created_at);

            return [
                'mes_clave'       => $mesClave,
                'mes_etiqueta'    => ucfirst($fechaRef->translatedFormat('F Y')),
                'total_servicios' => $items->count(),
                'total_base'      => $items->sum(fn($c) => (float) ($c->incidencia->especialidad->precio_base ?? 0)),
                'total_comision'  => $items->sum(fn($c) => (float) $c->importe),
                'porcentaje'      => $gestora->porcentaje_comision,
            ];
        });

        $totalGlobalComisiones = $comisiones->sum(fn($c) => (float) $c->importe);

        return view('gestora.liquidaciones', compact('gestora', 'comisiones', 'liquidacionesPorMes', 'totalGlobalComisiones'));
    }
}