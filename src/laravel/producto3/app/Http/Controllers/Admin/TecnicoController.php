<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Especialidad,Tecnico,User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
class TecnicoController extends Controller {
    public function index(){return view('admin.tecnicos',['tecnicos'=>Tecnico::with(['usuario','especialidad'])->get(),
        'especialidades'=>Especialidad::all()]);}
    public function store(Request $r){
        $v=$r->validate(['nombre_completo'=>'required|string|max:100','email'=>'required|email|unique:usuarios,email',
            'password'=>['required',Password::min(10)],'especialidad_id'=>'required|exists:especialidades,id']);
        DB::transaction(function()use($v){$u=User::create(['nombre'=>$v['nombre_completo'],'email'=>$v['email'],
            'password'=>$v['password'],'rol'=>'tecnico']);
            Tecnico::create(['usuario_id'=>$u->id,'nombre_completo'=>$v['nombre_completo'],
                'especialidad_id'=>$v['especialidad_id'],'disponible'=>true]);});
        return back()->with('success','Técnico creado.');
    }
    public function update(Request $r,Tecnico $tecnico){
        $v=$r->validate(['nombre_completo'=>'required|string|max:100','especialidad_id'=>'required|exists:especialidades,id',
            'disponible'=>'required|boolean']);$tecnico->update($v);return back()->with('success','Técnico actualizado.');
    }
    public function destroy(Tecnico $tecnico){
        if($tecnico->incidencias()->exists())return back()->withErrors(['tecnico'=>'No se puede eliminar un técnico con historial de incidencias.']);
        DB::transaction(function()use($tecnico){$usuario=$tecnico->usuario;$tecnico->delete();$usuario?->delete();});
        return back()->with('success','Técnico eliminado.');
    }
}
