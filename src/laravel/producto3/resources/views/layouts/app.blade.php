<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>@yield('title','ReparaYa') · ReparaYa</title><link rel="stylesheet" href="{{ asset('css/reparaya.css') }}"></head>
<body><header class="top"><a class="brand" href="{{ route('inicio') }}">Repara<span>Ya</span></a>
<nav><a href="{{ route('inicio') }}">Inicio</a>
@auth
@if(auth()->user()->rol==='particular')<a href="{{ route('cliente.avisos') }}">Mis avisos</a><a href="{{ route('cliente.crear') }}">Nueva solicitud</a>@endif
@if(auth()->user()->rol==='tecnico')<a href="{{ route('tecnico.agenda') }}">Mi agenda</a>@endif
@if(auth()->user()->rol==='admin')<a href="{{ route('admin.incidencias') }}">Incidencias</a><a href="{{ route('admin.calendario') }}">Calendario</a><a href="{{ route('admin.especialidades.index') }}">Servicios</a><a href="{{ route('admin.tecnicos.index') }}">Técnicos</a><a href="{{ route('admin.gestoras') }}">Gestoras</a><a href="{{ route('admin.usuarios') }}">Usuarios</a><a href="{{ route('admin.liquidaciones') }}">Liquidaciones</a>@endif
@if(auth()->user()->rol==='gestora')<a href="{{ route('gestora.dashboard') }}">Comunidades</a><a href="{{ route('gestora.crear') }}">Crear incidencia</a><a href="{{ route('gestora.liquidaciones') }}">Liquidaciones</a>@endif
<a href="{{ route('perfil') }}">Mi perfil</a><form method="post" action="{{ route('logout') }}">@csrf<button class="link-button">Salir</button></form>
@else<a href="{{ route('login') }}">Acceder</a><a href="{{ route('register') }}">Registro</a>@endauth
</nav></header><main class="container">
@if(session('success'))<div class="alert ok">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert error"><strong>Revisa los siguientes datos:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('content')</main><footer>ReparaYaApp · Producto 3 · Backeners · Laravel MVC</footer></body></html>
