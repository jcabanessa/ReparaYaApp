@extends('layouts.app')

@section('content')
<div class="home-container" style="max-width: 1000px;">
    <h1 class="hero-title">Maestro de <span>Técnicos y Servicios</span></h1>
    <p class="hero-subtitle">Panel de gestión para la plantilla de profesionales y los tipos de servicio.</p>

    @if(session('success'))
        <p style="color: #10b981; font-weight: 600; margin-bottom: 20px;">{{ session('success') }}</p>
    @endif
    @if(session('error'))
        <p style="color: #ef4444; font-weight: 600; margin-bottom: 20px;">{{ session('error') }}</p>
    @endif

    <div class="dashboard-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        
        <!-- Panel de Técnicos -->
        <div class="dashboard-card">
            <h3>Gestión de Técnicos</h3>
            <form action="{{ route('admin.tecnicos.store') }}" method="POST" style="margin-top: 15px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #2a2a2a;">
                @csrf
                <div class="form-group">
                    <input type="text" name="nombre" placeholder="Nombre completo" required style="width: 100%; padding: 8px; margin-bottom: 10px; background: #0b0b0b; border: 1px solid #2a2a2a; color: #fff; border-radius: 4px;">
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Correo electrónico" required style="width: 100%; padding: 8px; margin-bottom: 10px; background: #0b0b0b; border: 1px solid #2a2a2a; color: #fff; border-radius: 4px;">
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Contraseña de acceso" required style="width: 100%; padding: 8px; margin-bottom: 10px; background: #0b0b0b; border: 1px solid #2a2a2a; color: #fff; border-radius: 4px;">
                </div>
                <button type="submit" style="background: #ff6600; color: #fff; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; width: 100%;">+ Dar de Alta Técnico</button>
            </form>

            <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                <thead>
                    <tr style="border-bottom: 1px solid #2a2a2a; color: #ff6600; text-align: left;">
                        <th style="padding: 8px;">Nombre Completo</th>
                        <th style="padding: 8px;">Email</th>
                        <th style="padding: 8px;">Especialidad</th>
                        <th style="padding: 8px; text-align: center;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $tecnicos = App\Models\Tecnico::with(['usuario', 'especialidad'])->get(); 
                    @endphp
                    @if($tecnicos->isEmpty())
                        <tr><td colspan="4" style="padding: 10px; color: #9ca3af;">No hay técnicos registrados.</td></tr>
                    @else
                        @foreach ($tecnicos as $tec)
                            <tr style="border-bottom: 1px solid #1a1a1a;">
                                <td style="padding: 8px; color: #fff;">{{ $tec->nombre_completo }}</td>
                                <td style="padding: 8px; color: #9ca3af;">{{ $tec->usuario->email ?? 'N/A' }}</td>
                                <td style="padding: 8px; color: #38bdf8;">{{ $tec->especialidad->nombre ?? 'Sin especialidad' }}</td>
                                <td style="padding: 8px; text-align: center;">
                                    <form action="{{ route('admin.tecnicos.destroy', $tec->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de dar de baja a este técnico?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: #ef4444; color: #fff; border: none; padding: 4px 8px; border-radius: 3px; cursor: pointer;">Baja</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Panel de Especialidades (Servicios) -->
        <div class="dashboard-card">
            <h3>Tipos de Servicio y Precios Base</h3>
            <form action="{{ route('admin.especialidades.store') }}" method="POST" style="margin-top: 15px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #2a2a2a;">
                @csrf
                <div class="form-group">
                    <input type="text" name="nombre" placeholder="Ej: Fontanería, Electricidad..." required style="width: 100%; padding: 8px; margin-bottom: 10px; background: #0b0b0b; border: 1px solid #2a2a2a; color: #fff; border-radius: 4px;">
                </div>
                <div class="form-group">
                    <input type="number" step="0.01" name="precio_base" placeholder="Precio Base (€) Ej: 50.00" required style="width: 100%; padding: 8px; margin-bottom: 10px; background: #0b0b0b; border: 1px solid #2a2a2a; color: #fff; border-radius: 4px;">
                </div>
                <button type="submit" style="background: #ff6600; color: #fff; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; width: 100%;">+ Añadir Servicio</button>
            </form>

            <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                <thead>
                    <tr style="border-bottom: 1px solid #2a2a2a; color: #ff6600; text-align: left;">
                        <th style="padding: 8px;">Especialidad</th>
                        <th style="padding: 8px; text-align: right;">Precio Base</th>
                    </tr>
                </thead>
                <tbody>
                    @php $especialidades = App\Models\Especialidad::all(); @endphp
                    @if($especialidades->isEmpty())
                        <tr><td colspan="2" style="padding: 10px; color: #9ca3af;">No hay servicios configurados.</td></tr>
                    @else
                        @foreach ($especialidades as $esp)
                            <tr style="border-bottom: 1px solid #1a1a1a;">
                                <td style="padding: 8px; color: #fff;">{{ $esp->nombre }}</td>
                                <td style="padding: 8px; color: #10b981; font-weight: bold; text-align: right;">
                                    {{ number_format($esp->precio_base, 2) }} €
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection