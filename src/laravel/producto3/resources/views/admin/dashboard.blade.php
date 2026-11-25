@extends('layouts.app')
@section('title','Administración')
@section('content')<p class="eyebrow">Administración</p><h1>Panel de ReparaYa</h1>
<div class="grid cols3">@foreach(['Pendiente','Asignada','En curso','Finalizada','Cancelada'] as $estado)
<div class="card stat"><span>{{ $estado }}</span><b>{{ $resumen[$estado]??0 }}</b></div>@endforeach</div>
<div class="card"><h2>Herramientas</h2><div class="actions"><a class="btn" href="{{ route('admin.incidencias') }}">Gestionar incidencias</a>
<a class="btn secondary" href="{{ route('admin.calendario') }}">Calendario</a>
<a class="btn secondary" href="{{ route('admin.gestoras') }}">Gestoras y comunidades</a>
<a class="btn secondary" href="{{ route('admin.liquidaciones') }}">Liquidaciones</a></div></div>@endsection
