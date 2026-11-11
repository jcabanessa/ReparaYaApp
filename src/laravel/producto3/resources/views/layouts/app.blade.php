<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReparaYaApp</title>
    <!-- Carga del archivo CSS corporativo mediante la función asset() de Laravel -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

    <!-- ==========================================
         CABECERA / NAVBAR
         ========================================== -->
    <nav class="navbar">
        <div class="logo">
            <a href="{{ url('/') }}">ReparaYaApp</a>
        </div>
        <ul class="nav-list">
            <li><a href="{{ url('/') }}" class="nav-link">Inicio</a></li>

            @auth
                {{-- Menú específico según el rol del usuario autenticado --}}
                @if(auth()->user()->rol === 'admin')
                    <li><a href="{{ route('admin.dashboard') }}" class="nav-link">Panel Admin</a></li>
                    <li><a href="{{ route('admin.gestoras') }}" class="nav-link">Empresas Gestoras</a></li>
                    <li><a href="{{ route('admin.calendario') }}" class="nav-link">Calendario</a></li>
                @elseif(auth()->user()->rol === 'gestora')
                    <li><a href="{{ route('gestora.dashboard') }}" class="nav-link">Panel Gestora</a></li>
                @elseif(auth()->user()->rol === 'tecnico')
                    <li><a href="{{ route('tecnico.agenda') }}" class="nav-link">Mi Agenda</a></li>
                @elseif(auth()->user()->rol === 'particular')
                    <li><a href="{{ route('cliente.avisos') }}" class="nav-link">Mis Avisos</a></li>
                @endif

                <li><a href="{{ route('perfil') }}" class="nav-link">Mi Perfil</a></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn-logout" style="background: none; border: none; cursor: pointer; padding: 0;">Cerrar Sesión</button>
                    </form>
                </li>
            @else
                {{-- Opciones para usuarios no autenticados --}}
                <li><a href="{{ route('login') }}" class="nav-link">Iniciar Sesión</a></li>
                <li><a href="{{ route('register') }}" class="btn-orange">Registrarse</a></li>
            @endauth
        </ul>
    </nav>

    <!-- ==========================================
         CONTENIDO DINÁMICO DE CADA VISTA
         ========================================== -->
    <main>
        @yield('content')
    </main>

    <!-- ==========================================
         PIE DE PÁGINA COMÚN
         ========================================== -->
    <footer>
        <p>&copy; {{ date('Y') }} ReparaYa App - UOC | Todos los derechos reservados.</p>
    </footer>

</body>
</html>