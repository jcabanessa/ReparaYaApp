@extends('layouts.app')
@section('title','Crear aviso administrativo')
@section('content')
<div class="p3-module home-container">
<h1>Alta de incidencia (administrador)</h1>
<div class="card narrow"><form method="post" action="{{ route('admin.guardar') }}">@csrf
<label>Cliente<select name="cliente_id" required><option value="">Seleccionar cliente</option>@foreach($clientes as $c)<option value="{{ $c->id }}">{{ $c->nombre }} · {{ $c->email }}</option>@endforeach</select></label>
<label>Especialidad<select name="especialidad_id" required>@foreach($especialidades as $e)<option value="{{ $e->id }}">{{ $e->nombre_especialidad }}</option>@endforeach</select></label>
<div class="grid cols2"><label>Urgencia<select name="tipo_urgencia"><option>Estándar</option><option>Urgente</option></select></label>
<label>Fecha<input type="datetime-local" name="fecha_servicio" required></label></div>
<label>Franja<input name="franja_horaria" value="Mañana 09:00-14:00" required></label>
<label>Teléfono<input name="telefono_contacto" required></label><label>Dirección<input name="direccion" required></label>
<label>Descripción<textarea name="descripcion" required minlength="10"></textarea></label><button class="btn">Crear aviso</button></form></div>
</div>
@endsection
