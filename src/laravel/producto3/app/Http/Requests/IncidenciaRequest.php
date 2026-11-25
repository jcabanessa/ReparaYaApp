<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class IncidenciaRequest extends FormRequest {
    public function authorize():bool{return auth()->check() && in_array(auth()->user()->rol,['particular','gestora','admin'],true);}
    public function rules():array{return [
        'cliente_id'=>'nullable|integer|exists:usuarios,id',
        'comunidad_id'=>'nullable|integer|exists:comunidades,id',
        'especialidad_id'=>'required|integer|exists:especialidades,id',
        'fecha_servicio'=>'required|date','franja_horaria'=>'required|string|max:50',
        'telefono_contacto'=>'required|string|min:6|max:20',
        'descripcion'=>'required|string|min:10|max:5000','direccion'=>'required|string|min:5|max:255',
        'tipo_urgencia'=>'required|in:Estándar,Urgente',
    ];}
}
