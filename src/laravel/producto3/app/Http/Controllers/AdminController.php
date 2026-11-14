<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\EmpresaGestora;
use App\Models\Zona;
use App\Models\Comunidad;
use App\Models\Tecnico;
use App\Models\Especialidad;

class AdminController extends Controller
{
    // Panel principal de administración
    public function dashboard(Request $request)
    {
        $filtroEstado = $request->query('estado');

        // Consultar incidencias con relaciones de Eloquent
        $query = Incidencia::with(['cliente', 'tecnico', 'comunidad', 'especialidad', 'empresaGestora']);

        if (!empty($filtroEstado)) {
            $query->where('estado', $filtroEstado);
        }

        $incidencias = $query->orderBy('fecha_servicio', 'desc')->get();

        // Obtener la lista de usuarios con rol 'tecnico' para los desplegables de asignación
        $tecnicos = User::where('rol', 'tecnico')->get();

        return view('admin.dashboard', compact('incidencias', 'tecnicos', 'filtroEstado'));
    }

    // Mostrar calendario con filtrado temporal y relaciones
    public function calendario(Request $request)
    {
        $filtro = $request->query('filtro', 'todos');
        $query = Incidencia::with(['cliente', 'tecnico', 'especialidad', 'comunidad']);

        switch ($filtro) {
            case 'mensual':
                $query->whereMonth('fecha_servicio', now()->month)
                    ->whereYear('fecha_servicio', now()->year);
                break;
            case 'semanal':
                $query->whereBetween('fecha_servicio', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'diaria':
                $query->whereDate('fecha_servicio', today());
                break;
            default:
                // 'todos' o cualquier otro valor
                break;
        }

        $incidencias = $query->orderBy('fecha_servicio', 'asc')->get();

        return view('admin.calendario', compact('incidencias', 'filtro'));
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

    // Mostrar vista de gestión y listado de empresas gestoras
    public function gestoras()
    {
        $empresas = EmpresaGestora::with('usuario')->orderBy('nombre', 'asc')->get();
        return view('admin.empresas_gestoras', compact('empresas'));
    }

    // Procesar el alta de una nueva empresa gestora y su usuario de acceso
    public function storeGestora(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'cif' => ['required', 'string', 'max:20', 'unique:empresas_gestoras,cif'],
            'porcentaje_comision' => ['required', 'numeric', 'min:0', 'max:100'],
            'nombre_usuario' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:usuarios,email'],
            'password' => ['required', 'string', 'min:6'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ]);

        try {
            DB::beginTransaction();

            // 1. Crear el usuario de acceso con rol 'gestora'
            $usuario = User::create([
                'nombre' => $request->nombre_usuario,
                'email' => $request->email,
                'telefono' => $request->telefono,
                'password' => Hash::make($request->password),
                'rol' => 'gestora',
            ]);

            // 2. Crear la empresa gestora vinculada al usuario y asignar el porcentaje de comisión
            EmpresaGestora::create([
                'usuario_id' => $usuario->id,
                'nombre' => $request->nombre,
                'cif' => $request->cif,
                'porcentaje_comision' => $request->porcentaje_comision,
            ]);

            DB::commit();

            return redirect()->route('admin.gestoras')->with('success', 'Empresa gestora y usuario de acceso creados correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Error al registrar la empresa gestora: ' . $e->getMessage()]);
        }
    }

    // Guardar nueva especialidad / tipo de servicio
    public function storeEspecialidad(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'precio_base' => ['required', 'numeric', 'min:0'],
        ]);

        Especialidad::create([
            'nombre_especialidad' => $request->nombre,
            'precio_base' => $request->precio_base,
        ]);

        return back()->with('success', 'Especialidad creada correctamente.');
    }

    public function liquidaciones()
    {
        // Cargar empresas gestoras con sus comisiones, incidencias, especialidades y comunidades
        $gestoras = EmpresaGestora::with([
            'comisiones.incidencia.especialidad', 
            'comisiones.incidencia.comunidad'
        ])->get();

        return view('admin.liquidaciones', compact('gestoras'));
    }


    // ==================== GESTIÓN DE COMUNIDADES ====================
    public function comunidades()
    {
        $comunidades = Comunidad::with(['empresaGestora'])->get();
        $gestoras = EmpresaGestora::all();
        return view('admin.comunidades', compact('comunidades', 'gestoras'));
    }

    public function storeComunidad(Request $request)
    {
        $request->validate([
            'nombre'             => ['required', 'string', 'max:150'],
            'direccion'          => ['required', 'string', 'max:255'],
            'zona'            => ['required', 'string', 'max:150'],
            'empresa_gestora_id' => ['nullable', 'exists:empresas_gestoras,id'],
        ]);

        Comunidad::create($request->all());

        return back()->with('success', 'Comunidad registrada y vinculada correctamente.');
    }

        // Listado en el panel maestro
    public function tecnicosServicios()
    {
        $tecnicos = Tecnico::with(['usuario', 'especialidad'])->get();
        $especialidades = Especialidad::all();
        return view('admin.tecnicos_servicios', compact('tecnicos', 'especialidades'));
    }

    // Alta de técnico (Transacción: usuarios + laravel_tecnico)
    public function storeTecnico(Request $request)
    {
        $request->validate([
            'nombre'          => ['required', 'string', 'max:100'],
            'email'           => ['required', 'string', 'email', 'max:255', 'unique:usuarios,email'],
            'password'        => ['required', 'string', 'min:6'],
            'especialidad_id' => ['required', 'exists:especialidades,id'],
            'telefono'        => ['nullable', 'string', 'max:20'],
        ]);

        DB::beginTransaction();
        try {
            // 1. Creamos el usuario con rol 'tecnico'
            $user = User::create([
                'nombre'   => $request->nombre,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'rol'      => 'tecnico',
                'telefono' => $request->telefono,
            ]);

            // 2. Creamos el registro en laravel_tecnico
            Tecnico::create([
                'usuario_id'      => $user->id,
                'nombre_completo' => $request->nombre,
                'especialidad_id' => $request->especialidad_id,
                'disponible'      => 1,
            ]);

            DB::commit();
            return back()->with('success', "¡Técnico registrado correctamente en el sistema!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Error al crear el técnico: " . $e->getMessage());
        }
    }

    // Baja de técnico (Eliminación en cascada segura)
    public function destroyTecnico($id)
    {
        DB::beginTransaction();
        try {
            $tecnico = Tecnico::findOrFail($id);
            
            // Borramos el usuario asociado si existe
            if ($tecnico->usuario_id) {
                User::where('id', $tecnico->usuario_id)->delete();
            }

            $tecnico->delete();

            DB::commit();
            return back()->with('success', "Técnico eliminado completamente de la plantilla.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "No se puede eliminar el técnico (posiblemente tenga incidencias asociadas en el historial).");
        }
    }

}