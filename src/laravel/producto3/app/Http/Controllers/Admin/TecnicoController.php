<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tecnico;
use App\Models\Especialidad;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class TecnicoController extends Controller
{
    public function index()
    {
        $tecnicos = Tecnico::with(['especialidad', 'usuario'])
            ->orderBy('nombre_completo')
            ->get();

        $especialidades = Especialidad::orderBy('nombre_especialidad')->get();

        return view('admin.tecnicos.index', compact('tecnicos', 'especialidades'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_completo' => 'required|string|max:100',
            'email'           => 'required|email|max:150|unique:usuarios,email',
            'telefono'        => 'nullable|string|max:20',
            'password'        => 'required|string|min:6',
            'especialidad_id' => 'required|exists:especialidades,id',
            'disponible'      => 'nullable|boolean',
        ]);

        try {
            DB::transaction(function () use ($validated, $request) {
                $usuario = User::create([
                    'nombre'   => $validated['nombre_completo'],
                    'email'    => $validated['email'],
                    'telefono' => $validated['telefono'] ?? null,
                    'password' => $validated['password'],
                    'rol'      => 'tecnico',
                ]);

                Tecnico::create([
                    'usuario_id'      => $usuario->id,
                    'nombre_completo' => $validated['nombre_completo'],
                    'especialidad_id' => $validated['especialidad_id'],
                    'disponible'      => $request->has('disponible') ? 1 : 0,
                ]);
            });

            return redirect()->route('admin.tecnicos.index')
                ->with('success', 'Técnico y cuenta de acceso creados correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('admin.tecnicos.index')
                ->with('error', 'Error al registrar el técnico: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Tecnico $tecnico)
    {
        $validated = $request->validate([
            'nombre_completo' => 'required|string|max:100',
            'especialidad_id' => 'required|exists:especialidades,id',
            'disponible'      => 'required|in:0,1',
        ]);

        $tecnico->update([
            'nombre_completo' => $validated['nombre_completo'],
            'especialidad_id' => $validated['especialidad_id'],
            'disponible'      => (int) $validated['disponible'],
        ]);

        if ($tecnico->usuario) {
            $tecnico->usuario->update(['nombre' => $validated['nombre_completo']]);
        }

        return redirect()->route('admin.tecnicos.index')
            ->with('success', 'Datos del técnico actualizados.');
    }

    public function destroy(Tecnico $tecnico)
    {
        try {
            DB::transaction(function () use ($tecnico) {
                $usuario = $tecnico->usuario;
                $tecnico->delete();

                if ($usuario) {
                    $usuario->delete();
                }
            });

            return redirect()->route('admin.tecnicos.index')
                ->with('success', 'Técnico eliminado correctamente.');
        } catch (QueryException $e) {
            return redirect()->route('admin.tecnicos.index')
                ->with('error', 'No se puede eliminar este técnico porque tiene incidencias históricas asignadas. Márcalo como No disponible.');
        }
    }
}
