@extends('layouts.app')
@section('title','Usuarios')
@section('content')<h1>Gestión de usuarios</h1><p class="muted">Los roles especiales se asignan mediante el alta de técnicos o gestoras.</p>
<div class="card"><div class="grid cols2">@foreach($usuarios as $u)<div class="mini-card"><h3>{{ $u->rol }} · #{{ $u->id }}</h3>
<form method="post" action="{{ route('admin.usuarios.update',$u) }}">@csrf @method('PUT')
<label>Nombre<input name="nombre" value="{{ $u->nombre }}" required></label>
<label>Email<input name="email" type="email" value="{{ $u->email }}" required></label>
<label>Teléfono<input name="telefono" value="{{ $u->telefono }}"></label><button class="btn small">Guardar cambios</button></form></div>@endforeach</div></div>
<div class="pagination">{{ $usuarios->links() }}</div>@endsection
