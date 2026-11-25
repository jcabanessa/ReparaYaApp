<?php
namespace App\Http\Controllers;
use App\Http\Requests\IncidenciaRequest;
use App\Models\{Incidencia,Especialidad};
use App\Services\IncidenciaService;
use Illuminate\Http\Request;
class ClienteController extends Controller {
    public function index(){
        $avisos=Incidencia::with('especialidad')->where('cliente_id',auth()->id())->orderByDesc('fecha_servicio')->paginate(15);
        return view('cliente.avisos',compact('avisos'));
    }
    public function crear(){ $especialidades=Especialidad::orderBy('nombre_especialidad')->get();return view('cliente.crear',compact('especialidades')); }
    public function store(IncidenciaRequest $r,IncidenciaService $servicio){
        $data=$r->validated();unset($data['cliente_id'],$data['comunidad_id']);$servicio->crear($data,$r->user());
        return redirect()->route('cliente.avisos')->with('success','Solicitud creada correctamente.');
    }
    public function show(Incidencia $incidencia){
        abort_unless((int)$incidencia->cliente_id===(int)auth()->id(),404);
        $incidencia->load(['especialidad','tecnico','historial.autor','comunidad']);
        return view('incidencias.detalle',compact('incidencia'));
    }
    public function cancelar(Incidencia $incidencia,IncidenciaService $servicio){
        $servicio->cancelar($incidencia->id,auth()->user());return back()->with('success','Incidencia cancelada.');
    }
}
