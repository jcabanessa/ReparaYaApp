<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmpresaGestora;
use App\Models\Incidencia;
use App\Models\Comunidad;
use App\Models\Comision;
use Carbon\Carbon;

class GestoraController extends Controller
{
    // Panel principal de la gestora (restringido a sus datos)
        public function dashboard(Request $request)
    {
        $usuario = auth()->user();

        // Obtener la empresa gestora vinculada al usuario
        $gestora = EmpresaGestora::where('usuario_id', $usuario->id)->first();

        if (!$gestora) {
            return redirect()->route('inicio')->with('error', 'No tienes ninguna empresa gestora asociada.');
        }

        $estado = $request->query('estado');
        $urgencia = $request->query('urgencia');
        $buscar = trim((string) $request->query('buscar', ''));

        // Consultas estrictamente limitadas a las comunidades e incidencias de esta gestora
        $comunidades = Comunidad::where('empresa_gestora_id', $gestora->id)->get();

        // Incidencias de sus comunidades con filtros por query string
        $incidenciasQuery = Incidencia::with(['comunidad', 'especialidad'])
            ->where('empresa_gestora_id', $gestora->id);

        if (!empty($estado)) {
            $incidenciasQuery->where('estado', $estado);
        }

        if ($urgencia === 'urgente') {
            $incidenciasQuery->whereRaw('LOWER(tipo_urgencia) = ?', ['urgente']);
        } elseif ($urgencia === 'estandar') {
            $incidenciasQuery->whereRaw('LOWER(tipo_urgencia) != ?', ['urgente']);
        }

        if ($buscar !== '') {
            $like = '%' . $buscar . '%';
            $incidenciasQuery->where(function ($q) use ($like) {
                $q->where('localizador', 'like', $like)
                ->orWhere('direccion', 'like', $like)
                ->orWhereHas('comunidad', function ($sub) use ($like) {
                    $sub->where('nombre', 'like', $like)
                        ->orWhere('zona', 'like', $like);
                })
                ->orWhereHas('especialidad', function ($sub) use ($like) {
                    $sub->where('nombre_especialidad', 'like', $like);
                });
            });
        }

        $incidencias = $incidenciasQuery->orderBy('fecha_servicio', 'desc')->get();

        // Comisiones de la gestora agrupadas por mes
        $comisiones = Comision::with(['incidencia.comunidad', 'incidencia.especialidad'])
            ->where('empresa_gestora_id', $gestora->id)
            ->get();

        // Agrupamos las comisiones por periodo mensual (ej: "Noviembre 2026")
        $comisionesPorMes = $comisiones->groupBy(function ($comision) {
            $fecha = $comision->fecha_finalizacion ?? $comision->created_at;
            return $fecha ? Carbon::parse($fecha)->translatedFormat('F Y') : 'Sin fecha';
        });

        return view('gestora.dashboard', compact(
            'gestora',
            'comunidades',
            'incidencias',
            'comisiones',
            'comisionesPorMes',
            'estado',
            'urgencia',
            'buscar'
        ));
    }

    // Formulario para que la gestora cree una incidencia en nombre de un vecino de sus comunidades
    public function crearIncidenciaForm()
    {
        $usuario = auth()->user();
        $gestora = EmpresaGestora::where('usuario_id', $usuario->id)->first();

        // Solo se muestran las comunidades asignadas a esta gestora
        $comunidades = Comunidad::where('empresa_gestora_id', $gestora->id)->get();

        return view('gestora.crear_incidencia', compact('comunidades', 'gestora'));
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