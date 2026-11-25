<?php
namespace App\Http\Controllers;
use App\Models\{Comunidad,EmpresaGestora,Especialidad,Incidencia,Tecnico,User};
use App\Services\{ComisionService,IncidenciaService};
use App\Http\Requests\IncidenciaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
class AdminController extends Controller {
    public function dashboard(){
        $resumen=Incidencia::selectRaw('estado, count(*) as total')->groupBy('estado')->pluck('total','estado');
        return view('admin.dashboard',compact('resumen'));
    }
    public function incidencias(Request $r){
        $q=Incidencia::with(['cliente','tecnico','especialidad','comunidad']);
        if ($r->filled('estado')) $q->where('estado',$r->query('estado'));
        $avisos=$q->orderByDesc('fecha_servicio')->paginate(20)->withQueryString();
        $tecnicos=Tecnico::with('especialidad')->where('disponible',true)->get();
        return view('admin.incidencias',compact('avisos','tecnicos'));
    }
    public function ver(Incidencia $incidencia){
        $incidencia->load(['cliente','tecnico','especialidad','historial.autor','comunidad']);
        return view('incidencias.detalle',compact('incidencia'));
    }
    public function nueva(){
        $especialidades=Especialidad::all();$clientes=User::where('rol','particular')->get();
        return view('admin.nueva',compact('especialidades','clientes'));
    }
    public function guardar(IncidenciaRequest $r,IncidenciaService $servicio){
        $servicio->crear($r->validated(),$r->user());return redirect()->route('admin.incidencias')->with('success','Incidencia creada.');
    }
    public function editar(Incidencia $incidencia){
        abort_unless(in_array($incidencia->estado,['Pendiente','Asignada'],true),403);
        $especialidades=Especialidad::all();return view('admin.editar',compact('incidencia','especialidades'));
    }
    public function actualizar(IncidenciaRequest $r,Incidencia $incidencia,IncidenciaService $servicio){
        $servicio->editar($incidencia->id,$r->validated(),$r->user());return redirect()->route('admin.incidencias')->with('success','Incidencia actualizada.');
    }
    public function asignar(Request $r,Incidencia $incidencia,IncidenciaService $servicio){
        $v=$r->validate(['tecnico_id'=>'required|integer|exists:tecnicos,id']);
        $servicio->asignar($incidencia->id,(int)$v['tecnico_id'],$r->user());return back()->with('success','Técnico asignado.');
    }
    public function cancelar(Incidencia $incidencia,IncidenciaService $servicio){
        $servicio->cancelar($incidencia->id,auth()->user());return back()->with('success','Incidencia cancelada.');
    }
    public function calendario(){
        $eventos=Incidencia::with('especialidad')->get()->map(fn($i)=>[
            'id'=>$i->id,'title'=>$i->localizador.' · '.$i->estado.' · '.$i->especialidad?->nombre_especialidad,
            'start'=>$i->fecha_servicio?->toIso8601String(),'urgencia'=>$i->tipo_urgencia,
            'estado'=>$i->estado,'url'=>route('admin.ver',$i),
        ]);return view('admin.calendario',compact('eventos'));
    }
    public function gestoras(){
        $gestoras=EmpresaGestora::with('usuario','comunidades')->orderBy('nombre')->get();
        return view('admin.gestoras',compact('gestoras'));
    }
    public function storeGestora(Request $r){
        $v=$r->validate(['nombre'=>'required|string|max:150','cif'=>'required|string|max:20|unique:empresas_gestoras,cif',
            'porcentaje_comision'=>'required|numeric|between:0,100','nombre_usuario'=>'required|string|max:100',
            'email'=>'required|email|unique:usuarios,email','password'=>['required',Password::min(10)]]);
        DB::transaction(function()use($v){
            $u=User::create(['nombre'=>$v['nombre_usuario'],'email'=>$v['email'],'password'=>$v['password'],'rol'=>'gestora']);
            EmpresaGestora::create(['usuario_id'=>$u->id,'nombre'=>$v['nombre'],'cif'=>$v['cif'],
                'porcentaje_comision'=>$v['porcentaje_comision']]);
        });return back()->with('success','Gestora y cuenta creadas.');
    }
    public function actualizarGestora(Request $r,EmpresaGestora $gestora){
        $v=$r->validate(['nombre'=>'required|string|max:150','porcentaje_comision'=>'required|numeric|between:0,100']);
        $gestora->update($v);return back()->with('success','Gestora actualizada.');
    }
    public function storeComunidad(Request $r){
        $v=$r->validate(['empresa_gestora_id'=>'required|exists:empresas_gestoras,id','nombre'=>'required|string|max:150',
            'direccion'=>'required|string|max:255','zona'=>'required|string|max:80']);
        Comunidad::create($v);return back()->with('success','Comunidad creada.');
    }
    public function actualizarComunidad(Request $r,Comunidad $comunidad){
        $v=$r->validate(['nombre'=>'required|string|max:150','direccion'=>'required|string|max:255','zona'=>'required|string|max:80']);
        $comunidad->update($v);return back()->with('success','Comunidad actualizada.');
    }
    public function liquidaciones(Request $r,ComisionService $servicio){
        $gestoras=EmpresaGestora::orderBy('nombre')->get();
        $id=$r->filled('gestora_id')?(int)$r->query('gestora_id'):null;
        $data=$servicio->listado($id);return view('liquidaciones',compact('data','gestoras','id'));
    }
}
