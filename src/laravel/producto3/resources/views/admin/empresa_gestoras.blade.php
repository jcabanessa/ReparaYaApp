@extends('layouts.app')

@section('content')
<div class="home-container">
    <div class="page-header">
        <div>
            <h1 class="hero-title">Gestión de <span>Empresas Gestoras</span></h1>
            <p class="hero-subtitle">Alta de nuevas empresas, credenciales de acceso y asignación de comisiones.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert-error">
            <ul style="list-style: none; margin: 0; padding: 0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Formulario de Alta -->
    <div class="dashboard-card" style="margin-bottom: 35px;">
        <h3>Registrar Nueva Empresa Gestora</h3>
        <form action="{{ route('admin.gestoras.store') }}" method="POST">
            @csrf

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label highlight">Nombre de la Empresa</label>
                    <input type="text" name="nombre" required class="form-control" placeholder="Ej. Gestiones S.L." value="{{ old('nombre') }}">
                </div>
                <div class="form-group">
                    <label class="form-label highlight">CIF de la Empresa</label>
                    <input type="text" name="cif" required class="form-control" placeholder="B12345678" value="{{ old('cif') }}">
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label highlight">Porcentaje de Comisión (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="porcentaje_comision" required class="form-control" placeholder="Ej. 15.00" value="{{ old('porcentaje_comision') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono de Contacto</label>
                    <input type="text" name="telefono" class="form-control" placeholder="930000000" value="{{ old('telefono') }}">
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid #2a2a2a; margin: 20px 0;">

            <h4 style="color: #ff6600; margin-bottom: 15px; font-size: 1.1rem;">Datos del Usuario de Acceso</h4>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Nombre del Responsable / Usuario</label>
                    <input type="text" name="nombre_usuario" required class="form-control" placeholder="Nombre completo" value="{{ old('nombre_usuario') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Correo Electrónico (Login)</label>
                    <input type="email" name="email" required class="form-control" placeholder="gestora@empresa.com" value="{{ old('email') }}">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 25px;">
                <label class="form-label">Contraseña de Acceso</label>
                <input type="password" name="password" required class="form-control" placeholder="••••••••">
            </div>

            <button type="submit" class="btn-submit">Dar de alta Empresa Gestora</button>
        </form>
    </div>

    <!-- Listado de Empresas Gestoras -->
    <div class="dashboard-card">
        <h3>Empresas Gestoras Registradas</h3>
        @if($empresas->isEmpty())
            <p class="text-empty">No hay empresas gestoras dadas de alta actualmente.</p>
        @else
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th>CIF</th>
                            <th>Comisión</th>
                            <th>Usuario Asignado</th>
                            <th>Email de Acceso</th>
                            <th>Teléfono</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($empresas as $emp)
                            <tr>
                                <td class="table-code">{{ $emp->nombre }}</td>
                                <td>{{ $emp->cif }}</td>
                                <td><strong style="color: #ff6600;">{{ number_format($emp->porcentaje_comision, 2) }}%</strong></td>
                                <td>{{ $emp->usuario->nombre ?? 'N/A' }}</td>
                                <td class="table-date">{{ $emp->usuario->email ?? 'N/A' }}</td>
                                <td class="table-subtext">{{ $emp->usuario->telefono ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection