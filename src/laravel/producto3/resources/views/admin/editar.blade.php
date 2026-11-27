@extends('layouts.app')
@section('title','Editar incidencia')
@section('content')
<div class="p3-module home-container">
<h1>Editar {{ $incidencia->localizador }}</h1><div class="card narrow"><form method="post" action="{{ route('admin.actualizar',$incidencia) }}">@csrf @method('PUT')
<label>Especialidad<select name="especialidad_id">@foreach($especialidades as $e)<option value="{{ $e->id }}" @selected($incidencia->especialidad_id===$e->id)>{{ $e->nombre_especialidad }}</option>@endforeach</select></label>
<div class="grid cols2"><label>Urgencia<select name="tipo_urgencia"><option @selected($incidencia->tipo_urgencia==='Estándar')>Estándar</option><option @selected($incidencia->tipo_urgencia==='Urgente')>Urgente</option></select></label>
<label>Fecha<input type="datetime-local" name="fecha_servicio" value="{{ $incidencia->fecha_servicio?->format('Y-m-d\TH:i') }}" required></label></div>
<label>Franja<input name="franja_horaria" value="{{ $incidencia->franja_horaria }}" required></label>
<label>Teléfono<input name="telefono_contacto" value="{{ $incidencia->telefono_contacto }}" required></label>
<label>Dirección<input name="direccion" value="{{ $incidencia->direccion }}" required></label>
<label>Descripción<textarea name="descripcion" required minlength="10">{{ $incidencia->descripcion }}</textarea></label><button class="btn">Guardar cambios</button></form></div>
</div>
@endsection
