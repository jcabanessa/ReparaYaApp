<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incidencia;
use App\Models\Comision;
use App\Models\EmpresaGestora;

class TechController extends Controller
{
    // Listar incidencias asignadas al técnico autenticado
                public function index(Request $request)
    {
        $tecnicoId = auth()->id();

        $estado = trim((string) $request->query('estado', ''));
        $urgencia = strtolower(trim((string) $request->query('urgencia', '')));
        $buscar = trim((string) $request->query('buscar', ''));

        $query = Incidencia::where('tecnico_id', $tecnicoId)
            ->with(['comunidad', 'cliente', 'especialidad']);

        if ($estado !== '') {
            $query->where('estado', $estado);
        }

        if ($urgencia === 'urgente') {
            $query->where('tipo_urgencia', 'Urgente');
        } elseif ($urgencia === 'estandar') {
            $query->whereIn('tipo_urgencia', ['Estándar', 'Estandar']);
        }

        if ($buscar !== '') {
            $like = '%' . $buscar . '%';
            $query->where(function ($q) use ($like) {
                $q->where('localizador', 'like', $like)
                    ->orWhere('direccion', 'like', $like)
                    ->orWhere('estado', 'like', $like)
                    ->orWhere('tipo_urgencia', 'like', $like)
                    ->orWhereHas('comunidad', function ($sub) use ($like) {
                        $sub->where('nombre', 'like', $like)
                            ->orWhere('zona', 'like', $like);
                    })
                    ->orWhereHas('especialidad', function ($sub) use ($like) {
                        $sub->where('nombre_especialidad', 'like', $like);
                    });
            });
        }

        $incidencias = $query->orderBy('fecha_servicio', 'asc')->get();

        return view('tecnico.agenda', compact('incidencias', 'estado', 'urgencia', 'buscar'));
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

        // Cuando la incidencia se marca como Finalizada:
        if ($request->nuevo_estado === 'Finalizada') {
            // Buscamos si la comunidad/cliente pertenece a una empresa gestora con comisión asignada
            // (Asumiendo que tu incidencia tiene una relación con la comunidad o empresa gestora)
            $gestora = EmpresaGestora::find($incidencia->empresa_gestora_id); // o el campo que corresponda

            if ($gestora && $gestora->porcentaje_comision > 0) {
                // Imagina que la incidencia tiene un campo 'precio_total' o 'coste'
                $importeBase = $incidencia->precio_total ?? 100.00;
                $importeComision = ($importeBase * $gestora->porcentaje_comision) / 100;

                Comision::create([
                    'empresa_gestora_id' => $gestora->id,
                    'incidencia_id' => $incidencia->id,
                    'importe' => $importeComision,
                    'estado' => 'Pendiente'
                ]);
            }
        }


        return redirect()->back()->with('success', 'Estado actualizado correctamente.');
    }

        public function calendario($filtro = 'todos')
    {
        $tecnicoId = auth()->id();
        $query = Incidencia::with('especialidad')->where('tecnico_id', $tecnicoId);

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
            case 'todos':
            default:
                break;
        }

        $incidencias = $query->orderBy('fecha_servicio', 'asc')->get();

        return view('tecnico.calendario', compact('incidencias', 'filtro'));
    }
}