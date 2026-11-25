@extends('layouts.app')
@section('title','Técnicos')
@section('content')<h1>Maestro de técnicos</h1><div class="card narrow"><h2>Dar de alta técnico</h2><form method="post" action="{{ route('admin.tecnicos.store') }}">@csrf
<label>Nombre<input name="nombre_completo" required></label><label>Email<input name="email" type="email" required></label>
<label>Contraseña (mín. 10 caracteres)<input name="password" type="password" minlength="10" required></label>
<label>Especialidad<select name="especialidad_id" required>@foreach($especialidades as $e)<option value="{{ $e->id }}">{{ $e->nombre_especialidad }}</option>@endforeach</select></label>
<button class="btn">Crear técnico</button></form></div>
<div class="card"><h2>Técnicos registrados</h2><div class="grid cols2">@foreach($tecnicos as $t)<div class="mini-card">
<form method="post" action="{{ route('admin.tecnicos.update',$t) }}">@csrf @method('PUT')
<label>Nombre<input name="nombre_completo" value="{{ $t->nombre_completo }}" required></label>
<p class="muted">{{ $t->usuario?->email }}</p><label>Especialidad<select name="especialidad_id">@foreach($especialidades as $e)<option value="{{ $e->id }}" @selected($t->especialidad_id===$e->id)>{{ $e->nombre_especialidad }}</option>@endforeach</select></label>
<label>Disponibilidad<select name="disponible"><option value="1" @selected($t->disponible)>Disponible</option><option value="0" @selected(!$t->disponible)>No disponible</option></select></label>
<button class="btn small">Actualizar</button></form>
<form method="post" action="{{ route('admin.tecnicos.destroy',$t) }}" onsubmit="return confirm('¿Eliminar técnico?')">@csrf @method('DELETE')<button class="btn danger small">Eliminar</button></form>
</div>@endforeach</div></div>@endsection
