<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmpresaGestora;
use App\Models\Incidencia;
use App\Models\Comunidad;

class GestoraController extends Controller
{
    // Panel principal de la gestora (restringido a sus datos)
    public function dashboard()
    {
        $usuario = auth()->user();
        
        // Obtener la empresa gestora vinculada al usuario
        $gestora = EmpresaGestora::where('usuario_id', $usuario->id)->first();

        if (!$gestora) {
            abort(403, 'No hay ninguna empresa gestora asociada a este usuario.');
        }

        // Consultas estrictamente limitadas a las comunidades e incidencias de esta gestora
        $comunidades = Comunidad::where('empresa_gestora_id', $gestora->id)->get();
        $incidencias = Incidencia::where('empresa_gestora_id', $gestora->id)->orderBy('fecha_servicio', 'desc')->get();
        $comisones = $gestora->comisiones()->with('incidencia')->get();

        return view('gestora.dashboard', compact('gestora', 'comunidades', 'incidencias', 'comisones'));
    }

    // Formulario para que la gestora cree una incidencia en nombre de un vecino de sus comunidades
    public function crearIncidenciaForm()
    {
        $usuario = auth()->user();
        $gestora = EmpresaGestora::where('usuario_id', $usuario->id)->first();

        // Solo se muestran las comunidades asignadas a esta gestora
        $comunidades = Comunidad::where('empresa_gestora_id', $gestora->id)->get();
        $zonas = \App\Models\Zona::all();

        return view('gestora.crear_incidencia', compact('comunidades', 'zonas', 'gestora'));
    }

    // Procesar la creación de la incidencia por parte de la gestora
    public function storeIncidencia(Request $request)
    {
        $usuario = auth()->user();
        $gestora = EmpresaGestora::where('usuario_id', $usuario->id)->first();

        $request->validate([
            'comunidad_id' => ['required', 'exists:comunidades,id'],
            'tipo_urgencia' => ['required', 'string'],
            'fecha_servicio' => ['required', 'date'],
            'direccion' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string'],
        ]);

        // SEGURIDAD: Verificar que la comunidad seleccionada pertenece realmente a esta gestora
        $comunidadValida = Comunidad::where('id', $request->comunidad_id)
            ->where('empresa_gestora_id', $gestora->id)
            ->exists();

        if (!$comunidadValida) {
            return back()->withErrors(['error' => 'La comunidad seleccionada no está autorizada para esta gestora.']);
        }

        $localizador = 'INC-' . strtoupper(substr(uniqid(), -6));

        Incidencia::create([
            'localizador' => $localizador,
            'cliente_id' => $usuario->id, // O el vecino si se registra
            'comunidad_id' => $request->comunidad_id,
            'zona_id' => $request->comunidad->zona_id ?? null,
            'empresa_gestora_id' => $gestora->id, // Vinculada a la gestora
            'tipo_urgencia' => $request->tipo_urgencia,
            'fecha_servicio' => $request->fecha_servicio,
            'direccion' => $request->direccion,
            'descripcion' => $request->descripcion,
            'estado' => 'Pendiente', // Creación en estado Pendiente
        ]);

        return redirect()->route('gestora.dashboard')->with('success', 'Incidencia creada correctamente en estado Pendiente.');
    }
}