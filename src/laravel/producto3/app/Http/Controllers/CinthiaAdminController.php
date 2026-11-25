<?php
namespace App\Http\Controllers;

use App\Http\Requests\IncidenciaRequest;
use App\Models\{Comision,Comunidad,EmpresaGestora,Especialidad,Incidencia,Tecnico,User};
use App\Services\IncidenciaService;
use Illuminate\Http\Request;

/** Funcionalidades de Cinthia P3 (sin reemplazar el AdminController del equipo). */
class CinthiaAdminController extends Controller
{
    public function incidencias(Request $r)
    {
        $q = Incidencia::with(['cliente','especialidad','comunidad','tecnico']);
        if ($r->filled('estado')) $q->where('estado', $r->query('estado'));
        $avisos = $q->orderByDesc('fecha_servicio')->paginate(20)->withQueryString();
        $tecnicos = Tecnico::with('especialidad')->where('disponible',true)->get();
        return view('admin.incidencias',compact('avisos','tecnicos'));
    }
    public function nueva()
    {
        $especialidades=Especialidad::all();$clientes=User::where('rol','particular')->get();
        return view('admin.nueva',compact('especialidades','clientes'));
    }
    public function guardar(IncidenciaRequest $r, IncidenciaService $service)
    {
        $service->crear($r->validated(),$r->user());
        return redirect()->route('admin.incidencias')->with('success','Incidencia creada.');
    }
    public function ver(Incidencia $incidencia)
    {
        $incidencia->load(['cliente','especialidad','comunidad','tecnico','historial.autor']);
        return view('incidencias.detalle',compact('incidencia'));
    }
    public function editar(Incidencia $incidencia)
    {
        abort_unless(in_array($incidencia->estado,['Pendiente','Asignada'],true),403);
        $especialidades=Especialidad::all();
        return view('admin.editar',compact('incidencia','especialidades'));
    }
    public function actualizar(IncidenciaRequest $r, Incidencia $incidencia,IncidenciaService $service)
    {
        $service->editar($incidencia->id,$r->validated(),$r->user());
        return redirect()->route('admin.incidencias')->with('success','Incidencia actualizada.');
    }
    public function asignar(Request $r, Incidencia $incidencia,IncidenciaService $service)
    {
        $v=$r->validate(['tecnico_id'=>'required|integer|exists:tecnicos,id']);
        $service->asignar($incidencia->id,(int)$v['tecnico_id'],$r->user());
        return back()->with('success','Técnico asignado.');
    }
    public function cancelar(Incidencia $incidencia,IncidenciaService $service)
    {
        $service->cancelar($incidencia->id,auth()->user());
        return back()->with('success','Incidencia cancelada.');
    }
    public function calendario()
    {
        $eventos=Incidencia::with('especialidad')->get()->map(fn($i)=>[
            'id'=>$i->id, 'title'=>$i->localizador.' · '.$i->estado.' · '.$i->especialidad?->nombre_especialidad,
            'start'=>$i->fecha_servicio?->toIso8601String(), 'urgencia'=>$i->tipo_urgencia,
            'estado'=>$i->estado, 'url'=>route('admin.ver',$i),
        ]);
        return view('admin.calendario',compact('eventos'));
    }
    public function comunidades()
    {
        $gestoras=EmpresaGestora::orderBy('nombre')->get();
        $comunidades=Comunidad::with('empresaGestora')->orderBy('nombre')->get();
        return view('admin.cinthia_comunidades',compact('gestoras','comunidades'));
    }
    public function storeComunidad(Request $r)
    {
        $v=$r->validate([
            'empresa_gestora_id'=>'required|integer|exists:empresas_gestoras,id',
            'nombre'=>'required|string|max:150', 'direccion'=>'required|string|max:255',
            'zona'=>'required|string|max:80',
        ]);
        Comunidad::create($v);
        return redirect()->route('admin.comunidades')->with('success','Comunidad creada.');
    }
    public function actualizarComunidad(Request $r, Comunidad $comunidad)
    {
        $v=$r->validate([
            'nombre'=>'required|string|max:150','direccion'=>'required|string|max:255',
            'zona'=>'required|string|max:80',
        ]);
        $comunidad->update($v);
        return redirect()->route('admin.comunidades')->with('success','Comunidad actualizada.');
    }
    public function liquidaciones(Request $r)
    {
        $gestoras=EmpresaGestora::orderBy('nombre')->get();
        $id=$r->filled('gestora_id')?(int)$r->query('gestora_id'):null;
        $q=Comision::with(['incidencia.comunidad','incidencia.especialidad','empresaGestora'])
            ->whereHas('incidencia',fn($q)=>$q->where('estado','Finalizada'));
        if ($id!==null) $q->where('empresa_gestora_id',$id);
        $comisiones=$q->get()->sortByDesc(fn($c)=>$c->incidencia?->finalizada_at ?? $c->incidencia?->updated_at ?? $c->created_at);
        $porMes=$comisiones->groupBy(function($c){
            $fecha=$c->incidencia?->finalizada_at ?? $c->incidencia?->updated_at ?? $c->created_at;
            return $fecha ? \Carbon\Carbon::parse($fecha)->format('Y-m') : 'Sin fecha';
        })->map(fn($g)=>['servicios'=>$g->count(),'importe'=>$g->sum(fn($c)=>(float)$c->importe)]);
        $total=$comisiones->sum(fn($c)=>(float)$c->importe);
        return view('admin.cinthia_liquidaciones',compact('gestoras','id','comisiones','porMes','total'));
    }
}
