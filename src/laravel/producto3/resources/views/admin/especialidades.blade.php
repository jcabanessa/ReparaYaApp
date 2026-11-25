@extends('layouts.app')
@section('title','Servicios y tarifas')
@section('content')<h1>Servicios y precios base</h1><div class="card narrow"><h2>Añadir especialidad</h2><form method="post" action="{{ route('admin.especialidades.store') }}">@csrf
<label>Nombre<input name="nombre_especialidad" required></label><label>Precio base (€)<input type="number" name="precio_base" min="0" step="0.01" required></label><button class="btn">Crear servicio</button></form></div>
<div class="card"><h2>Catálogo</h2><div class="grid cols2">@foreach($especialidades as $e)<div class="mini-card"><form method="post" action="{{ route('admin.especialidades.update',$e) }}">@csrf @method('PUT')
<label>Especialidad<input name="nombre_especialidad" value="{{ $e->nombre_especialidad }}" required></label>
<label>Precio base<input type="number" name="precio_base" min="0" step="0.01" value="{{ $e->precio_base }}" required></label>
<p class="muted">{{ $e->tecnicos_count }} técnicos</p><button class="btn small">Actualizar</button></form>
<form method="post" action="{{ route('admin.especialidades.destroy',$e) }}" onsubmit="return confirm('¿Eliminar el servicio?')">@csrf @method('DELETE')<button class="btn danger small">Eliminar</button></form>
</div>@endforeach</div></div>@endsection
