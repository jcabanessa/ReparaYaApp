<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReparaYa App</title>
    <!-- Enlace a tu archivo CSS principal ubicado en public/css/style.css -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <a href="{{ url('/') }}">ReparaYa<span>App</span></a>
        </div>
        <ul class="nav-list">
            @auth
                <li><span class="nav-link">Hola, {{ auth()->user()->nombre }}</span></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-logout" style="background:none; border:none; cursor:pointer;">Cerrar Sesión</button>
                    </form>
                </li>
            @else
                <li><a href="{{ route('login') }}" class="nav-link">Iniciar Sesión</a></li>
                <li><a href="{{ route('register') }}" class="btn-orange">Registrarse</a></li>
            @endauth
        </ul>
    </nav>

    <main>
        @yield('content')
    </main>
</body>
</html>