<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmpresaGestora;

class GestoraController extends Controller
{
    public function dashboard()
    {
        $usuario = auth()->user();
        
        // Obtenemos la empresa gestora vinculada a este usuario de acceso con sus comisiones
        $gestora = EmpresaGestora::where('usuario_id', $usuario->id)
            ->with(['comisiones.incidencia'])
            ->first();

        return view('gestora.dashboard', compact('gestora'));
    }
}