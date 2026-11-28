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
use App\Models\Tecnico;

class IncidenciaController extends Controller
{
    // Mostrar formulario para crear nueva incidencia (Cliente o Admin)
    public function create()
    {
        $user = auth()->user();
        $comunidades = collect();
        $clientes = collect();
        $tecnicos = collect();

        if ($user->rol === 'gestora') {
            $gestora = EmpresaGestora::where('usuario_id', $user->id)->first();
            $comunidades = $gestora ? Comunidad::where('empresa_gestora_id', $gestora->id)->get() : collect();
        } elseif ($user->rol === 'admin') {
            $comunidades = Comunidad::all();
            // Cargamos los usuarios con rol 'particular' para el selector de clientes
            $clientes = User::where('rol', 'particular')->orderBy('nombre', 'asc')->get();
            // Cargamos los técnicos vinculados de tu tabla laravel_tecnicos
            $tecnicos = Tecnico::with(['usuario', 'especialidad'])->get();
        } else {
            $comunidades = Comunidad::all();
        }

        $especialidades = Especialidad::all();
        $gestoras = EmpresaGestora::all();

        return view('incidencias.crear', compact('comunidades', 'especialidades', 'gestoras', 'clientes', 'tecnicos'));
    }

    
    // Guardar nueva incidencia (Soporta Admin y Clientes/Gestoras)
    public function store(Request $request)
    {
        $user = auth()->user();

        // 1. Definir reglas de validación base
        $rules = [
            'especialidad_id' => ['required', 'exists:especialidades,id'],
            'tipo_urgencia'   => ['required', 'string'],
            'fecha_solicitud' => ['required', 'date'],
            'direccion'       => ['required', 'string', 'max:255'],
            'descripcion'     => ['required', 'string'],
            'comunidad_id'    => ['nullable', 'exists:comunidades,id'],
        ];

        // Si es admin, exigimos seleccionar el cliente al que se le crea el aviso
        if ($user->rol === 'admin') {
            $rules['cliente_id'] = ['required', 'exists:usuarios,id'];
            $rules['tecnico_id'] = ['nullable', 'exists:usuarios,id'];
        }

        $request->validate($rules);

        // 2. Determinar el cliente_id según el rol
        $cliente_id = ($user->rol === 'admin') ? $request->cliente_id : $user->id;
        
        $tipo_urgencia   = $request->tipo_urgencia;
        $fecha_servicio  = $request->fecha_solicitud . ' 09:00:00';
        $especialidad_id = $request->especialidad_id;
        $comunidad_id    = $request->comunidad_id;
        $tecnico_id      = ($user->rol === 'admin') ? $request->tecnico_id : null;

        // Si se seleccionó una comunidad, podemos rescatar su empresa gestora automáticamente si la tiene
        $empresa_gestora_id = $request->empresa_gestora_id;
        if ($comunidad_id) {
            $comunidad = Comunidad::find($comunidad_id);
            if ($comunidad && $comunidad->empresa_gestora_id) {
                $empresa_gestora_id = $comunidad->empresa_gestora_id;
            }
        }

        $fechaActual = new DateTime();
        $fechaCita   = new DateTime($fecha_servicio);

        // Normalizar tipo de urgencia para la regla de las 48h
        $tipoMinuscula = strtolower(str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $tipo_urgencia));
        $esEstandar = ($tipoMinuscula === 'estandar');

        // Validaciones de fecha y antelación
        if ($fechaCita < $fechaActual) {
            return back()->with('error_incidencia', "La fecha de la cita no puede ser anterior a la actual.")->withInput();
        }

        if ($esEstandar && ($fechaCita->getTimestamp() - $fechaActual->getTimestamp()) < (48 * 3600)) {
            return back()->with('error_incidencia', "Error de validación: Las solicitudes estándar deben realizarse con un mínimo de 48 horas de antelación.")->withInput();
        }

        // Obtener precio base de la especialidad
        $especialidad = Especialidad::findOrFail($especialidad_id);
        $precioBaseAplicado = $especialidad->precio_base ?? 0.00;

        $localizador = 'INC-' . strtoupper(substr(uniqid(), -6));
        $estado_inicial = !empty($tecnico_id) ? 'Asignada' : 'Pendiente';

        // 3. Crear la incidencia
        $incidencia = Incidencia::create([
            'localizador'        => $localizador,
            'cliente_id'         => $cliente_id,
            'tecnico_id'         => $tecnico_id,
            'comunidad_id'       => $comunidad_id,
            'especialidad_id'    => $especialidad_id,
            'empresa_gestora_id' => $empresa_gestora_id,
            'descripcion'        => $request->descripcion,
            'direccion'          => $request->direccion,
            'fecha_servicio'     => $fecha_servicio,
            'tipo_urgencia'      => $tipo_urgencia,
            'estado'             => $estado_inicial,
            'precio_aplicado'    => $precioBaseAplicado,
        ]);

        // Registrar en historial si existe la función
        if (function_exists('registrarHistorial')) {
            registrarHistorial(app('db')->getPdo(), $incidencia->id, 'Ninguno', $estado_inicial, $user->id, 'Aviso registrado.');
        }

        session(['exito_incidencia' => "¡Aviso creado con éxito! Localizador asignado: " . $localizador]);

        return $user->rol === 'admin' 
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
    
        // 1. Guardamos el estado anterior antes de modificarlo
        $estadoAnterior = $incidencia->estado;
        $nuevoEstado = $request->estado;

        // Solo registramos si realmente hay un cambio de estado
        if ($estadoAnterior !== $nuevoEstado) {
            $incidencia->estado = $nuevoEstado;
            $incidencia->save();

            // 2. Llamada a la función global registrarHistorial con los parámetros correctos
            if (function_exists('registrarHistorial')) {
                registrarHistorial(
                    app('db')->getPdo(), 
                    $incidencia->id, 
                    $estadoAnterior, 
                    $nuevoEstado, 
                    auth()->id(), 
                    "Estado actualizado de '{$estadoAnterior}' a '{$nuevoEstado}'."
                );
            }

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
    /*public function agendaTecnico()
    {
        $incidencias = Incidencia::where('tecnico_id', auth()->id())->orderBy('fecha_servicio', 'asc')->get();
        return view('tecnico.agenda', compact('incidencias'));
    }*/
}