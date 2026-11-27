@extends('layouts.app')
@section('content')
<div class="p3-module home-container">

<h1>Comunidades · Producto 3</h1>
@if(session('success'))<p>{{ session('success') }}</p>@endif
@if($errors->any())<ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
<section class="card">
<h2>Alta de comunidad</h2>
<form method="POST" action="{{ route('admin.comunidades.store') }}">@csrf
<label>Empresa gestora<select name="empresa_gestora_id" required>
@foreach($gestoras as $g)<option value="{{ $g->id }}">{{ $g->nombre }}</option>@endforeach
</select></label>
<label>Nombre <input name="nombre" required maxlength="150"></label>
<label>Dirección <input name="direccion" required maxlength="255"></label>
<label>Zona <input name="zona" required maxlength="80"></label>
<button type="submit">Crear comunidad</button></form></section>
<section class="card"><h2>Comunidades existentes</h2>
@forelse($comunidades as $c)
<form method="POST" action="{{ route('admin.comunidades.update',$c) }}">@csrf @method('PUT')
<p><strong>{{ $c->empresaGestora?->nombre }}</strong></p>
<label>Nombre <input name="nombre" value="{{ $c->nombre }}" required></label>
<label>Dirección <input name="direccion" value="{{ $c->direccion }}" required></label>
<label>Zona <input name="zona" value="{{ $c->zona }}" required></label>
<button type="submit">Guardar cambios</button></form><hr>
@empty<p>Sin comunidades.</p>@endforelse
</section>

</div>
@endsection
