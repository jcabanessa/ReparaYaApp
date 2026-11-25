<?php
namespace App\Http\Controllers;
use App\Models\{Incidencia,Tecnico};
use App\Services\IncidenciaService;
use Illuminate\Http\Request;
class TechController extends Controller {
    public function index(){
        $tecnico=Tecnico::where('usuario_id',auth()->id())->firstOrFail();
        $incidencias=Incidencia::with(['cliente','especialidad'])->where('tecnico_id',$tecnico->id)->orderBy('fecha_servicio')->get();
        return view('tecnico.agenda',compact('incidencias'));
    }
    public function cambiarEstado(Request $r,IncidenciaService $servicio){
        $v=$r->validate(['incidencia_id'=>'required|integer|exists:incidencias,id','nuevo_estado'=>'required|in:En curso,Finalizada']);
        $servicio->avanzar((int)$v['incidencia_id'],$r->user(),$v['nuevo_estado']);
        return back()->with('success','Estado actualizado.');
    }
}
