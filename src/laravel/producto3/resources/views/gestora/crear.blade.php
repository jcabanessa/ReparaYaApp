@extends('layouts.app')
@section('title','Incidencia de comunidad')
@section('content')<h1>Solicitar reparación para una comunidad</h1><p class="muted">Solo puedes crear incidencias en las comunidades de {{ $gestora->nombre }}.</p>
<div class="card narrow"><form method="post" action="{{ route('gestora.store') }}">@csrf
<label>Comunidad<select name="comunidad_id" required>@foreach($comunidades as $c)<option value="{{ $c->id }}">{{ $c->nombre }} · {{ $c->zona }}</option>@endforeach</select></label>
<div class="grid cols2"><label>Nombre del vecino<input name="cliente_nombre" value="{{ old('cliente_nombre') }}" required></label>
<label>Email del vecino<input name="cliente_email" type="email" value="{{ old('cliente_email') }}" required></label></div>
<label>Teléfono del vecino<input name="cliente_telefono" value="{{ old('cliente_telefono') }}" required></label>
<label>Tipo de servicio<select name="especialidad_id" required>@foreach($especialidades as $e)<option value="{{ $e->id }}">{{ $e->nombre_especialidad }} · {{ number_format($e->precio_base,2,',','.') }} €</option>@endforeach</select></label>
<div class="grid cols2"><label>Urgencia<select name="tipo_urgencia"><option>Estándar</option><option>Urgente</option></select></label>
<label>Fecha y hora<input name="fecha_servicio" type="datetime-local" required></label></div>
<label>Franja<input name="franja_horaria" value="Mañana 09:00-14:00" required></label>
<label>Descripción<textarea name="descripcion" rows="4" minlength="10" required>{{ old('descripcion') }}</textarea></label>
<button class="btn">Crear incidencia en Pendiente</button></form></div>@endsection
