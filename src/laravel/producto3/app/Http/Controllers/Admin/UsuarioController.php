<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
class UsuarioController extends Controller {
    public function index(){return view('admin.usuarios',['usuarios'=>User::orderBy('nombre')->paginate(30)]);}
    public function update(Request $r,User $usuario){
        $v=$r->validate(['nombre'=>'required|string|max:100','email'=>'required|email|max:100|unique:usuarios,email,'.$usuario->id,
            'telefono'=>'nullable|string|max:20']);
        $usuario->update($v);return back()->with('success','Usuario actualizado.');
    }
}
