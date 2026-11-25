<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class LoginController extends Controller {
    public function showLoginForm(){return view('auth.login');}
    public function login(Request $r){
        $data=$r->validate(['email'=>'required|email','password'=>'required|string']);
        if(!Auth::attempt($data))return back()->withErrors(['email'=>'Credenciales incorrectas.'])->onlyInput('email');
        $r->session()->regenerate();return redirect()->route('inicio');
    }
    public function logout(Request $r){Auth::logout();$r->session()->invalidate();$r->session()->regenerateToken();return redirect()->route('login');}
}
