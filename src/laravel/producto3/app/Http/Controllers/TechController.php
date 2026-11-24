<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incidencia;
use App\Models\Comision;
use App\Models\Tecnico;
use Illuminate\Support\Facades\DB;

class TechController extends Controller
{
    // Listar incidencias asignadas al técnico autenticado
    public function index()
    {
        $incidencias = Incidencia::where('tecnico_id', auth()->id())
            ->with(['comunidad', 'especialidad', 'cliente'])
            ->orderBy('fecha_servicio', 'asc')
            ->get();

        return view('tecnico.dashboard', compact('incidencias'));
    }

    // Cambiar el estado de una incidencia y generar comisión B2B si pasa a Finalizada
    public function cambiarEstado(Request $request)
    {
        $request->validate([
            'incidencia_id' => 'required|exists:incidencias,id',
            'nuevo_estado'  => 'required|in:En curso,Finalizada',
        ]);

        $incidencia = Incidencia::where('id', $request->incidencia_id)
            ->where('tecnico_id', auth()->id())
            ->firstOrFail();

        DB::transaction(function () use ($incidencia, $request) {
            $incidencia->estado = $request->nuevo_estado;
            $incidencia->save();

            if ($request->nuevo_estado === 'Finalizada') {
                Comision::generarParaIncidencia($incidencia);
            }
        });

        return redirect()->back()->with('success', 'Estado actualizado correctamente.');
}
}