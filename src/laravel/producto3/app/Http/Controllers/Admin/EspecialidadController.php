<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Especialidad;
use Illuminate\Http\Request;
class EspecialidadController extends Controller {
    public function index(){return view('admin.especialidades', ['especialidades'=>Especialidad::withCount('tecnicos')->get()]);}
    public function store(Request $r){Especialidad::create($r->validate(['nombre_especialidad'=>'required|string|max:50',
        'precio_base'=>'required|numeric|min:0|max:99999999']));return back()->with('success','Servicio creado.');}
    public function update(Request $r,Especialidad $especialidad){$especialidad->update($r->validate([
        'nombre_especialidad'=>'required|string|max:50','precio_base'=>'required|numeric|min:0|max:99999999']));
        return back()->with('success','Servicio actualizado.');}
    public function destroy(Especialidad $especialidad){
        if($especialidad->tecnicos()->exists() || \App\Models\Incidencia::where('especialidad_id',$especialidad->id)->exists())
            return back()->withErrors(['servicio'=>'No se puede eliminar un servicio que está en uso.']);
        $especialidad->delete();return back()->with('success','Servicio eliminado.');
    }
}
