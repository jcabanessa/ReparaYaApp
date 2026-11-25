<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
class RegisterController extends Controller {
    public function showRegistrationForm(){return view('auth.register');}
    public function register(Request $r){
        $v=$r->validate(['nombre'=>'required|string|max:100','email'=>'required|email|max:100|unique:usuarios,email',
            'telefono'=>'nullable|string|max:20','password'=>['required','confirmed',Password::min(10)]]);
        $v['rol']='particular';$user=User::create($v);Auth::login($user);$r->session()->regenerate();return redirect()->route('cliente.avisos');
    }
}
