@extends('layouts.app')

@section('content')
<div class="home-container">
    <div class="page-header">
        <div>
            <h1 class="hero-title">Gestión de <span>Comunidades</span></h1>
            <p class="hero-subtitle">Da de alta comunidades, especifica su zona interna y vincúlalas con una empresa gestora.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success" style="color: #22c55e; font-weight: 600; margin-bottom: 25px; background: #052e16; padding: 14px; border-radius: 8px; text-align: center;">
            {{ session('success') }}
        </div>
    @endif

    <!-- Formulario de Creación de Comunidad -->
    <div class="dashboard-card" style="margin-bottom: 30px;">
        <h3>Registrar Nueva Comunidad</h3>
        <form action="{{ route('admin.comunidades.store') }}" method="POST" style="margin-top: 20px;">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div class="form-group">
                    <label for="nombre" class="form-label">Nombre de la Comunidad *</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Ej: Edificio Gran Vía 45" required>
                </div>
                <div class="form-group">
                    <label for="direccion" class="form-label">Dirección Completa *</label>
                    <input type="text" name="direccion" id="direccion" class="form-control" placeholder="Calle, número..." required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div class="form-group">
                    <label for="zona" class="form-label">Zona de la Comunidad *</label>
                    <input type="text" name="zona" id="zona" class="form-control" placeholder="Ej: Jardín, Terrado, Sótano..." required>
                </div>
                <div class="form-group">
                    <label for="empresa_gestora_id" class="form-label">Empresa Gestora Asignada (Opcional)</label>
                    <select name="empresa_gestora_id" id="empresa_gestora_id" class="form-control">
                        <option value="">Sin gestora externa (Gestión directa)</option>
                        @foreach($gestoras as $gestora)
                            <option value="{{ $gestora->id }}">{{ $gestora->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-submit" style="width: 100%;">Guardar Comunidad</button>
        </form>
    </div>

    <!-- Listado de Comunidades -->
    <div class="dashboard-card">
        <h3>Listado de Comunidades Registradas</h3>
        @if($comunidades->isEmpty())
            <p class="text-empty" style="margin-top: 15px;">No hay comunidades registradas todavía.</p>
        @else
            <div class="table-responsive" style="margin-top: 20px;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nombre Comunidad</th>
                            <th>Dirección</th>
                            <th>Zona</th>
                            <th>Empresa Gestora Asociada</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($comunidades as $com)
                            <tr>
                                <td><strong>{{ $com->nombre }}</strong></td>
                                <td>{{ $com->direccion }}</td>
                                <td><span style="color: #38bdf8;">{{ $com->zona }}</span></td>
                                <td>
                                    <span style="color: #ff6600; font-weight: bold;">
                                        {{ $com->empresaGestora->nombre ?? 'Gestión Directa' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection