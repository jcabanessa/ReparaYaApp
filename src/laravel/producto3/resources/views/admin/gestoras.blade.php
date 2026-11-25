@extends('layouts.app')
@section('title','Empresas gestoras y comunidades')
@section('content')<p class="eyebrow">Panel del administrador</p><h1>Gestoras y comunidades</h1>
<div class="grid cols2"><div class="card"><h2>Alta de empresa gestora</h2><form method="post" action="{{ route('admin.gestoras.store') }}">@csrf
<label>Empresa<input name="nombre" required></label><label>CIF<input name="cif" required></label>
<label>Comisión (%)<input name="porcentaje_comision" type="number" step="0.01" min="0" max="100" required></label>
<label>Nombre responsable<input name="nombre_usuario" required></label><label>Email de acceso<input type="email" name="email" required></label>
<label>Contraseña de acceso<input type="password" name="password" minlength="10" required></label>
<button class="btn">Crear empresa gestora</button></form></div>
<div class="card"><h2>Añadir comunidad</h2><form method="post" action="{{ route('admin.comunidades.store') }}">@csrf
<label>Gestora<select name="empresa_gestora_id" required>@foreach($gestoras as $g)<option value="{{ $g->id }}">{{ $g->nombre }}</option>@endforeach</select></label>
<label>Nombre de comunidad<input name="nombre" required></label><label>Dirección<input name="direccion" required></label>
<label>Zona<input name="zona" placeholder="Norte, Sur, Este, Oeste…" required></label>
<button class="btn">Crear comunidad</button></form></div></div>
@forelse($gestoras as $g)<div class="card"><div class="heading"><div><h2>{{ $g->nombre }}</h2><p class="muted">CIF {{ $g->cif }} · Acceso {{ $g->usuario?->email }}</p></div></div>
<form class="inline-form" method="post" action="{{ route('admin.gestoras.update',$g) }}">@csrf @method('PUT')
<label>Nombre<input name="nombre" value="{{ $g->nombre }}" required></label>
<label>Comisión %<input type="number" name="porcentaje_comision" value="{{ $g->porcentaje_comision }}" min="0" max="100" step="0.01" required></label><button class="btn small">Guardar</button></form>
<h3>Comunidades asignadas</h3><div class="grid cols2">@forelse($g->comunidades as $c)<form class="mini-card" method="post" action="{{ route('admin.comunidades.update',$c) }}">@csrf @method('PUT')
<label>Nombre<input name="nombre" value="{{ $c->nombre }}" required></label>
<label>Dirección<input name="direccion" value="{{ $c->direccion }}" required></label>
<label>Zona<input name="zona" value="{{ $c->zona }}" required></label><button class="btn small secondary">Actualizar comunidad</button></form>
@empty<p class="muted">Todavía no tiene comunidades.</p>@endforelse</div></div>
@empty<div class="card">Todavía no hay empresas gestoras.</div>@endforelse
@endsection
