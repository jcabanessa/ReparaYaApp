<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
class PerfilController extends Controller {
    public function index(){return view('perfil');}
    public function actualizarDatos(Request $r){
        $v=$r->validate(['nombre'=>'required|string|max:100','email'=>'required|email|max:100|unique:usuarios,email,'.auth()->id(),
            'telefono'=>'nullable|string|max:20']);auth()->user()->update($v);return back()->with('success','Perfil actualizado.');
    }
    public function actualizarPassword(Request $r){
        $v=$r->validate(['actual'=>'required|string','password'=>['required','confirmed',Password::min(10)]]);
        if(!Hash::check($v['actual'],auth()->user()->password))throw ValidationException::withMessages(['actual'=>'Contraseña actual incorrecta.']);
        auth()->user()->update(['password'=>$v['password']]);return back()->with('success','Contraseña actualizada.');
    }
}
