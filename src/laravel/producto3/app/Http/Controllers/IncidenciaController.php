<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incidencia;
use App\Models\User;
use App\Models\Comision;
use App\Models\EmpresaGestora;
use App\Models\Comunidad;
use App\Models\Zona;

class IncidenciaController extends Controller
{
    // Mostrar formulario para crear nueva incidencia (Cliente o Admin)
    public function create()
    {
        $comunidades = Comunidad::all();
        $zonas = Zona::all();
        $gestoras = EmpresaGestora::all();
        return view('incidencias.crear', compact('comunidades', 'zonas', 'gestoras'));
    }

    // Guardar nueva incidencia
    public function store(Request $request)
    {
        $request->validate([
            'tipo_urgencia' => ['required', 'string'],
            'fecha_servicio' => ['required', 'date'],
            'direccion' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string'],
            'comunidad_id' => ['nullable', 'exists:comunidades,id'],
            'zona_id' => ['nullable', 'exists:zonas,id'],
            'empresa_gestora_id' => ['nullable', 'exists:empresas_gestoras,id'],
        ]);

        // Generar localizador único
        $localizador = 'INC-' . strtoupper(substr(uniqid(), -6));

        Incidencia::create([
            'localizador' => $localizador,
            'cliente_id' => auth()->id(), // Si la crea el admin, se puede adaptar para permitir seleccionar cliente
            'comunidad_id' => $request->comunidad_id,
            'zona_id' => $request->zona_id,
            'empresa_gestora_id' => $request->empresa_gestora_id,
            'tipo_urgencia' => $request->tipo_urgencia,
            'fecha_servicio' => $request->fecha_servicio,
            'direccion' => $request->direccion,
            'descripcion' => $request->descripcion,
            'estado' => 'Pendiente',
        ]);

        $redirectRoute = auth()->user()->rol === 'admin' ? 'admin.dashboard' : 'cliente.avisos';
        return redirect()->route($redirectRoute)->with('success', 'Incidencia creada correctamente con localizador: ' . $localizador);
    }

    // Asignar técnico (Admin)
    public function asignarTecnico(Request $request, $id)
    {
        $request->validate([
            'tecnico_id' => ['required', 'exists:usuarios,id'],
        ]);

        $incidencia = Incidencia::findOrFail($id);
        $incidencia->update([
            'tecnico_id' => $request->tecnico_id,
            'estado' => 'Asignada',
        ]);

        return back()->with('success', 'Técnico asignado correctamente a la incidencia.');
    }

    // Cambiar estado de la incidencia (Técnico o Admin) y calcular comisión si finaliza
    public function actualizarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => ['required', 'in:Pendiente,Asignada,En curso,Finalizada,Cancelada'],
        ]);

        $incidencia = Incidencia::findOrFail($id);
        $estadoAnterior = $incidencia->estado;
        $incidencia->estado = $request->estado;
        $incidencia->save();

        // Si la incidencia pasa a 'Finalizada' y tiene una empresa gestora asociada, generamos la comisión automáticamente
        if ($request->estado === 'Finalizada' && $estadoAnterior !== 'Finalizada' && $incidencia->empresa_gestora_id) {
            $gestora = EmpresaGestora::find($incidencia->empresa_gestora_id);
            if ($gestora && $gestora->porcentaje_comision > 0) {
                // Supongamos un coste fijo base de servicio o calculado (ej: 100€ o según tarifa)
                $importeBaseServicio = 120.00; 
                $importeComision = ($importeBaseServicio * $gestora->porcentaje_comision) / 100;

                Comision::create([
                    'empresa_gestora_id' => $gestora->id,
                    'incidencia_id' => $incidencia->id,
                    'importe' => $importeComision,
                    'estado' => 'Pendiente',
                ]);
            }
        }

        return back()->with('success', 'Estado de la incidencia actualizado correctamente.');
    }

    // Listado de avisos para el cliente particular
    public function misAvisos()
    {
        $incidencias = Incidencia::where('cliente_id', auth()->id())->orderBy('fecha_servicio', 'desc')->get();
        return view('cliente.avisos', compact('incidencias'));
    }

    // Agenda de trabajo para el técnico
    public function agendaTecnico()
    {
        $incidencias = Incidencia::where('tecnico_id', auth()->id())->orderBy('fecha_servicio', 'asc')->get();
        return view('tecnico.agenda', compact('incidencias'));
    }
}