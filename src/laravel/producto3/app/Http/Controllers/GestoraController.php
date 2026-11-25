<?php
namespace App\Http\Controllers;
use App\Http\Requests\IncidenciaRequest;
use App\Models\{Comunidad,EmpresaGestora,Especialidad,Incidencia,User};
use App\Services\{IncidenciaService,ComisionService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class GestoraController extends Controller {
    private function gestora():EmpresaGestora{return EmpresaGestora::where('usuario_id',auth()->id())->firstOrFail();}
    public function dashboard(){
        $gestora=$this->gestora()->load('comunidades');
        $avisos=Incidencia::with(['comunidad','especialidad'])->where('empresa_gestora_id',$gestora->id)
            ->orderByDesc('fecha_servicio')->get();
        return view('gestora.dashboard',compact('gestora','avisos'));
    }
    public function nueva(){
        $gestora=$this->gestora();$comunidades=$gestora->comunidades;$especialidades=Especialidad::all();
        return view('gestora.crear',compact('gestora','comunidades','especialidades'));
    }
    public function store(Request $r,IncidenciaService $servicio){
        $v=$r->validate([
            'comunidad_id'=>'required|integer|exists:comunidades,id','cliente_nombre'=>'required|string|max:100',
            'cliente_email'=>'required|email|max:100','cliente_telefono'=>'required|string|min:6|max:20',
            'especialidad_id'=>'required|integer|exists:especialidades,id','fecha_servicio'=>'required|date',
            'franja_horaria'=>'required|string|max:50','tipo_urgencia'=>'required|in:Estándar,Urgente',
            'descripcion'=>'required|string|min:10|max:5000',
        ]);
        $gestora=$this->gestora();$comunidad=Comunidad::where('empresa_gestora_id',$gestora->id)->findOrFail($v['comunidad_id']);
        DB::transaction(function()use($v,$gestora,$comunidad,$r,$servicio){
            // No se publica ningún listado de clientes externos a la gestora.
            $cliente=User::where('email',$v['cliente_email'])->first();
            if($cliente && $cliente->rol!=='particular')throw \Illuminate\Validation\ValidationException::withMessages(['cliente_email'=>'El correo ya pertenece a otro tipo de usuario.']);
            if(!$cliente)$cliente=User::create(['nombre'=>$v['cliente_nombre'],'email'=>$v['cliente_email'],
                'telefono'=>$v['cliente_telefono'],'password'=>Str::random(48),'rol'=>'particular']);
            $servicio->crear([
                'cliente_id'=>$cliente->id,'comunidad_id'=>$comunidad->id,
                'especialidad_id'=>$v['especialidad_id'],'fecha_servicio'=>$v['fecha_servicio'],
                'franja_horaria'=>$v['franja_horaria'],'telefono_contacto'=>$v['cliente_telefono'],
                'descripcion'=>$v['descripcion'],'direccion'=>$comunidad->direccion,
                'tipo_urgencia'=>$v['tipo_urgencia'],
            ],$r->user());
        });return redirect()->route('gestora.dashboard')->with('success','Solicitud registrada para la comunidad.');
    }
    public function ver(Incidencia $incidencia){
        $gestora=$this->gestora();abort_unless((int)$incidencia->empresa_gestora_id===(int)$gestora->id,404);
        $incidencia->load(['cliente','especialidad','comunidad','historial.autor']);
        return view('incidencias.detalle',compact('incidencia'));
    }
    public function cancelar(Incidencia $incidencia,IncidenciaService $servicio){
        $gestora=$this->gestora();abort_unless((int)$incidencia->empresa_gestora_id===(int)$gestora->id,404);
        $servicio->cancelar($incidencia->id,auth()->user());return back()->with('success','Incidencia cancelada.');
    }
    public function liquidaciones(ComisionService $servicio){
        $gestora=$this->gestora();$data=$servicio->listado($gestora->id);
        return view('liquidaciones',compact('data','gestora'));
    }
}
