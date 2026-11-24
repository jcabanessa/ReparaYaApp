<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Especialidad;

/**
 * Controlador CRUD Maestro para la gestión de tipos de servicio (especialidades)
 * y sus precios base de referencia para presupuestos y comisiones B2B.
 */ 
class EspecialidadController extends Controller
{
   /**
     * Listado general de especialidades con el conteo de técnicos asociados.
     */
    
   public function index()
   {
    $especialidades = Especialidad::withCount('tecnicos')
    ->orderBy('nombre_especialidad', 'asc')
    ->get();

    return view('admin.especialidades.index', compact('especialidades'));
   }


    /**
     * Alta de una nueva especialidad con validación en servidor.
     */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_especialidad' => 'required|string|max:100|unique:especialidades,nombre_especialidad',
            'precio_base'         => 'required|numeric|min:0',
        ], [
            'nombre_especialidad.required' => 'El nombre del servicio es obligatorio.',
            'nombre_especialidad.unique'   => 'Ya existe una especialidad con ese nombre.',
            'precio_base.required'         => 'Debes indicar un precio base.',
            'precio_base.numeric'          => 'El precio base debe ser un valor numérico.',
            'precio_base.min'              => 'El precio base no puede ser negativo.',
        ]);

        Especialidad::create($validated);

        return back()->with('success', 'Tipo de servicio creado correctamente.');
    }

    /**
     * Actualización del nombre o tarifa base de una especialidad existente.
     */
    public function update(Request $request, Especialidad $especialidad)
    {
        $validated = $request->validate([
            // Ignoramos el ID actual en la regla unique para permitir editar solo el precio base
            'nombre_especialidad' => 'required|string|max:100|unique:especialidades,nombre_especialidad,' . $especialidad->id,
            'precio_base'         => 'required|numeric|min:0',
        ], [
            'nombre_especialidad.required' => 'El nombre del servicio es obligatorio.',
            'nombre_especialidad.unique'   => 'Ya existe otra especialidad con ese nombre.',
            'precio_base.required'         => 'Debes indicar un precio base.',
            'precio_base.numeric'          => 'El precio base debe ser un valor numérico.',
            'precio_base.min'              => 'El precio base no puede ser negativo.',
        ]);

        $especialidad->update($validated);

        return back()->with('success', 'Servicio y precio base actualizados correctamente.');
    }

    /**
     * Baja controlada: impide eliminar si existen técnicos vinculados a la especialidad.
     */
    public function destroy(Especialidad $especialidad)
    {
        if ($especialidad->tecnicos()->count() > 0) {
            return back()->with('error', 'No se puede eliminar esta especialidad porque tiene técnicos asignados.');
        }

        $especialidad->delete();

        return back()->with('success', 'Especialidad eliminada correctamente.');
    }
}
