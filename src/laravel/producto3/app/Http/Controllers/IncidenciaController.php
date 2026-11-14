<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incidencia;
use App\Models\User;
use App\Models\Comision;
use App\Models\EmpresaGestora;
use App\Models\Comunidad;
use DateTime;
use App\Models\Especialidad;

class IncidenciaController extends Controller
{
    // Mostrar formulario para crear nueva incidencia (Cliente o Admin)
    public function create()
    {
        $user = auth()->user();
        if ($user->rol === 'gestora') {
            // Buscar la gestora asociada al usuario y sus comunidades exclusivas
            $gestora = EmpresaGestora::where('usuario_id', $user->id)->first();
            $comunidades = $gestora ? Comunidad::where('empresa_gestora_id', $gestora->id)->get() : collect();
        } else {
            $comunidades = Comunidad::all();
        }
        $especialidades = Especialidad::all();
        $gestoras = EmpresaGestora::all();
        return view('incidencias.crear', compact('comunidades', 'especialidades', 'gestoras'));
    }

    // Guardar nueva incidencia
    public function store(Request $request)
    {
        $request->validate([
            'especialidad_id' => ['required', 'exists:especialidades,id'],
            'tipo_urgencia'   => ['required', 'string'],
            'fecha_solicitud' => ['required', 'date'],
            'direccion'       => ['required', 'string', 'max:255'],
            'descripcion'     => ['required', 'string'],
        ]);

        $cliente_id     = auth()->id();
        $tipo_urgencia  = $request->tipo_urgencia;
        $fecha_servicio = $request->fecha_solicitud . ' 09:00:00';
        $especialidad_id = $request->especialidad_id;

        $fechaActual = new DateTime();
        $fechaCita   = new DateTime($fecha_servicio);

        // Normalizamos el tipo de urgencia (quitando tildes y pasando a minúsculas)
        $tipoMinuscula = strtolower(str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $tipo_urgencia));
        $esEstandar = ($tipoMinuscula === 'estandar');

        // Validaciones de fecha y antelación
        if ($fechaCita < $fechaActual) {
            return back()->with('error_incidencia', "La fecha de la cita no puede ser anterior a la actual.");
        }

        if ($esEstandar && ($fechaCita->getTimestamp() - $fechaActual->getTimestamp()) < (48 * 3600)) {
            return back()->with('error_incidencia', "Error de validación: Las solicitudes estándar deben realizarse con un mínimo de 48 horas de antelación.");
        }

        // Obtenemos el precio base histórico de la especialidad
        $especialidad = Especialidad::findOrFail($especialidad_id);
        $precioBaseAplicado = $especialidad->precio_base ?? 0.00;

        $localizador = 'INC-' . strtoupper(substr(uniqid(), -6));
        $estado_inicial = 'Pendiente';

        // Creamos la incidencia
        $incidencia = Incidencia::create([
            'localizador'      => $localizador,
            'cliente_id'       => $cliente_id,
            'especialidad_id'  => $especialidad_id,
            'empresa_gestora_id' => $request->empresa_gestora_id, // Vinculada a la gestora si aplica
            'descripcion'      => $request->descripcion,
            'direccion'        => $request->direccion,
            'fecha_servicio'   => $fecha_servicio,
            'tipo_urgencia'    => $tipo_urgencia,
            'estado'           => $estado_inicial,
            'precio_aplicado'  => $precioBaseAplicado,
        ]);

        // Registrar en historial si tienes la función implementada
        if (function_exists('registrarHistorial')) {
            registrarHistorial(app('db')->getPdo(), $incidencia->id, 'Ninguno', 'Pendiente', $cliente_id, 'Aviso registrado.');
        }

        session(['exito_incidencia' => "¡Aviso creado con éxito! Localizador asignado: " . $localizador]);

        return auth()->user()->rol === 'admin' 
            ? redirect()->route('admin.dashboard')
            : redirect()->route('cliente.avisos');
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

        $incidencia = Incidencia::with('especialidad')->findOrFail($id);
        $estadoAnterior = $incidencia->estado;
        $incidencia->estado = $request->estado;
        $incidencia->save();

        // Si la incidencia pasa a 'Finalizada' y tiene una empresa gestora asociada, generamos la comisión automáticamente
        if ($request->estado === 'Finalizada' && $estadoAnterior !== 'Finalizada' && $incidencia->empresa_gestora_id) {
            $gestora = EmpresaGestora::find($incidencia->empresa_gestora_id);
            if ($gestora && $gestora->porcentaje_comision > 0 && $incidencia->especialidad) {
                $precio_base = (float) $incidencia->especialidad->precio_base; 
                $importeComision = round(($precio_base * $gestora->porcentaje_comision) / 100, 2);

                Comision::create([
                    'empresa_gestora_id' => $gestora->id,
                    'incidencia_id' => $incidencia->id,
                    'importe' => $importeComision,
                    'estado' => 'Pendiente',
                    'updated_at' => now(),
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