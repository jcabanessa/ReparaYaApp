<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incidencia;
use App\Models\Comision;
use App\Models\EmpresaGestora;

class TechController extends Controller
{
    // Listar incidencias asignadas al técnico autenticado
    public function index()
    {
        $tecnicoId = auth()->id();
        
        $incidencias = Incidencia::where('tecnico_id', $tecnicoId)
            ->with(['comunidad', 'cliente'])
            ->orderBy('fecha_servicio', 'asc')
            ->get();

        return view('tecnico.dashboard', compact('incidencias'));
    }

    // Cambiar el estado de una incidencia
    public function cambiarEstado(Request $request)
    {
        $request->validate([
            'incidencia_id' => 'required|exists:incidencias,id',
            'nuevo_estado' => 'required|in:En curso,Finalizada',
        ]);

        $incidencia = Incidencia::where('id', $request->incidencia_id)
            ->where('tecnico_id', auth()->id())
            ->firstOrFail();

        $incidencia->estado = $request->nuevo_estado;
        $incidencia->save();

        // Cuando la incidencia se marca como Finalizada:
        if ($request->nuevo_estado === 'Finalizada') {
            // Buscamos si la comunidad/cliente pertenece a una empresa gestora con comisión asignada
            // (Asumiendo que tu incidencia tiene una relación con la comunidad o empresa gestora)
            $gestora = EmpresaGestora::find($incidencia->empresa_gestora_id); // o el campo que corresponda

            if ($gestora && $gestora->porcentaje_comision > 0) {
                // Imagina que la incidencia tiene un campo 'precio_total' o 'coste'
                $importeBase = $incidencia->precio_total ?? 100.00;
                $importeComision = ($importeBase * $gestora->porcentaje_comision) / 100;

                Comision::create([
                    'empresa_gestora_id' => $gestora->id,
                    'incidencia_id' => $incidencia->id,
                    'importe' => $importeComision,
                    'estado' => 'Pendiente'
                ]);
            }
        }


        return redirect()->back()->with('success', 'Estado actualizado correctamente.');
    }
}