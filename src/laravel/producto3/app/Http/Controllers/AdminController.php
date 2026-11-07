<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incidencia;
use App\Models\User;

class AdminController extends Controller
{
    // Mostrar calendario con filtrado temporal y relaciones
    public function calendario(Request $request)
    {
        $vista = $request->input('vista', 'mensual');

        $query = Incidencia::with(['cliente', 'tecnico', 'comunidad', 'zona']);

        if ($vista === 'diaria') {
            $query->whereDate('fecha_servicio', now()->toDateString());
        } elseif ($vista === 'semanal') {
            $query->whereBetween('fecha_servicio', [now()->startOfWeek(), now()->endOfWeek()]);
        } else {
            $query->whereMonth('fecha_servicio', now()->month)
                  ->whereYear('fecha_servicio', now()->year);
        }

        $incidencias = $query->orderBy('fecha_servicio', 'asc')->get();
        
        // Obtener técnicos disponibles para los desplegables de asignación
        $tecnicos = User::where('rol', 'tecnico')->get();

        return view('admin.calendario', compact('incidencias', 'vista', 'tecnicos'));
    }

    // Asignar un técnico a una incidencia
    public function asignarTecnico(Request $request)
    {
        $request->validate([
            'incidencia_id' => 'required|exists:incidencias,id',
            'tecnico_id' => 'required|exists:usuarios,id',
        ]);

        $incidencia = Incidencia::findOrFail($request->incidencia_id);
        $incidencia->tecnico_id = $request->tecnico_id;
        $incidencia->estado = 'Asignada';
        $incidencia->save();

        return redirect()->route('admin.calendario')->with('success', 'Técnico asignado correctamente.');
    }
}