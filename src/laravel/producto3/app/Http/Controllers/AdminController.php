<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\EmpresaGestora;

class AdminController extends Controller
{
    // Panel principal de administración
    public function dashboard(Request $request)
    {
        $filtroEstado = $request->query('estado');

        // Consultar incidencias con relaciones de Eloquent
        $query = Incidencia::with(['cliente', 'tecnico', 'comunidad', 'zona']);

        if (!empty($filtroEstado)) {
            $query->where('estado', $filtroEstado);
        }

        $incidencias = $query->orderBy('fecha_servicio', 'desc')->get();

        // Obtener la lista de usuarios con rol 'tecnico' para los desplegables de asignación
        $tecnicos = User::where('rol', 'tecnico')->get();

        $totalIncidencias = $incidencias->count();
        $totalTecnicos = $tecnicos->count();

        return view('admin.dashboard', compact('incidencias', 'tecnicos', 'filtroEstado', 'totalIncidencias', 'totalTecnicos'));
    }

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
}