<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incidencia;

class TechController extends Controller
{
    // Listar incidencias asignadas al técnico autenticado
    public function index()
    {
        $tecnicoId = auth()->id();
        
        $incidencias = Incidencia::where('tecnico_id', $tecnicoId)
            ->with(['comunidad', 'zona', 'cliente'])
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

        // Si se finaliza, opcionalmente podrías generar el registro en la tabla comisiones aquí

        return redirect()->back()->with('success', 'Estado actualizado correctamente.');
    }
}