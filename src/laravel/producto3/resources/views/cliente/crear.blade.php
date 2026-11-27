@extends('layouts.app')
@section('title','Solicitar asistencia')
@section('content')
<div class="p3-module home-container">
<h1>Nueva solicitud</h1><p class="muted">Estándar: mínimo 48 h de antelación. Urgente: próxima fecha futura.</p>
<div class="card narrow"><form method="post" action="{{ route('cliente.store') }}">@csrf
<label>Tipo de servicio<select name="especialidad_id" required><option value="">Selecciona uno</option>@foreach($especialidades as $e)<option value="{{ $e->id }}" @selected(old('especialidad_id')==$e->id)>{{ $e->nombre_especialidad }} ({{ number_format($e->precio_base,2,',','.') }} €)</option>@endforeach</select></label>
<div class="grid cols2"><label>Urgencia<select name="tipo_urgencia" required><option @selected(old('tipo_urgencia')==='Estándar')>Estándar</option><option @selected(old('tipo_urgencia')==='Urgente')>Urgente</option></select></label>
<label>Fecha y hora<input type="datetime-local" name="fecha_servicio" value="{{ old('fecha_servicio') }}" required></label></div>
<div class="grid cols2"><label>Franja horaria<select name="franja_horaria" required><option>Mañana 09:00-14:00</option><option>Tarde 14:00-20:00</option></select></label>
<label>Teléfono de contacto<input name="telefono_contacto" value="{{ old('telefono_contacto',auth()->user()->telefono) }}" required></label></div>
<label>Dirección<input name="direccion" value="{{ old('direccion') }}" required></label>
<label>Descripción<textarea name="descripcion" rows="4" minlength="10" required>{{ old('descripcion') }}</textarea></label>
<button class="btn">Registrar solicitud</button></form></div>
</div>
@endsection
