<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PerfilController extends Controller
{
    // Mostrar la vista del perfil del usuario autenticado
    public function index()
    {
        $usuario = auth()->user();
        return view('perfil', compact('usuario'));
    }

    // Actualizar datos personales
    public function actualizarDatos(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:usuarios,email,' . $user->id],
            'telefono' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'telefono' => $request->telefono,
        ]);

        return redirect()->route('perfil')->with('success', 'Información personal actualizada correctamente.');
    }

    // Actualizar contraseña de forma segura
    public function actualizarPassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'password_actual' => ['required'],
            'password_nueva' => ['required', 'string', 'min:6'],
            'password_confirmar' => ['required', 'same:password_nueva'],
        ]);

        // Verificar que la contraseña actual introducida sea correcta
        if (!Hash::check($request->password_actual, $user->password)) {
            return back()->with('error', 'La contraseña actual no es correcta.');
        }

        $user->update([
            'password' => Hash::make($request->password_nueva),
        ]);

        return redirect()->route('perfil')->with('success', 'Contraseña actualizada correctamente.');
    }
}
