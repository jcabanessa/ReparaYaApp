<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TechController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\GestoraController;
use App\Http\Controllers\Admin\EspecialidadController;
use App\Http\Controllers\Admin\TecnicoController;
use App\Http\Controllers\Api\ServicioZonaController;

/*Route::get('/', function () {
    return response()->json([
        'proyecto' => env('APP_NAME'),
        'grupo' => env('GROUP_CODE'),
        'estudiante' => env('STUDENT_NAME'),
        'message' => 'Aplicación Laravel en ejecución',
        'laravel_environment' => app()->environment(),
        'version' => app()->version(),
        'php_version' => phpversion(),
        
    ]);
});*/

// 1. Ruta raíz: redirección inteligente según autenticación y rol
Route::get('/', function () {
    if (auth()->check()) {
        $rol = auth()->user()->rol;
        if ($rol === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($rol === 'tecnico') {
            return redirect()->route('tecnico.agenda');
        } elseif ($rol === 'gestora') {
            return redirect()->route('gestora.dashboard');
        } else {
            return redirect()->route('cliente.avisos');
        }
    }
    return redirect()->route('login');
});

// 2. Rutas públicas de Autenticación y Registro
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// 3. Rutas de Perfil (Accesibles para cualquier usuario autenticado)
Route::middleware(['auth'])->group(function () {
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil');
    Route::put('/perfil/actualizar', [PerfilController::class, 'actualizarDatos'])->name('perfil.actualizar');
    Route::put('/perfil/password', [PerfilController::class, 'actualizarPassword'])->name('perfil.password');
});

// 4. Rutas protegidas de Administrador
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/gestoras', [AdminController::class, 'gestoras'])->name('admin.gestoras');
    Route::post('/gestoras', [AdminController::class, 'storeGestora'])->name('admin.gestoras.store');
    Route::get('/calendario', [\App\Http\Controllers\AdminController::class, 'calendario'])->name('admin.calendario');
   // Aquí añadimos rutas del CRUD Maestro (Técnicos y Especialidades) 
    Route::resource('especialidades', EspecialidadController::class)
    ->except(['create','show', 'edit'])
    ->parameters(['especialidades' => 'especialidad'])
    ->names('admin.especialidades');
    Route::resource('tecnicos', TecnicoController::class)
    ->except(['create','show', 'edit'])
    ->parameters(['tecnicos' => 'tecnico'])
    ->names('admin.tecnicos');

});

// 5. Espacios reservados para compañeros (Técnico / Cliente particular)
Route::middleware(['auth', 'role:tecnico'])->group(function () {
    Route::get('/tecnico/agenda', function () {
        return view('tecnico.agenda');
    })->name('tecnico.agenda');
});

Route::middleware(['auth', 'role:particular'])->group(function () {
    Route::get('/cliente/avisos', [\App\Http\Controllers\ClienteController::class, 'index'])->name('cliente.avisos');
});

Route::middleware(['auth', 'role:gestora'])->prefix('gestora')->group(function () {
    Route::get('/', [GestoraController::class, 'dashboard'])->name('gestora.dashboard');
    Route::get('/liquidaciones', [GestoraController::class, 'liquidaciones'])->name('gestora.liquidaciones');
});

// 6. Servicio Web REST (API JSON) - Punto 8
Route::get('/api/servicios/zonas', [ServicioZonaController::class, 'index'])->name('api.servicios.zonas');

// Incidencias de clientes y funciones de administración del Producto 3.
Route::middleware(['auth','role:particular'])->prefix('cliente')->name('cliente.')->group(function () {
    Route::get('/nueva',[\App\Http\Controllers\ClienteController::class,'crear'])->name('crear');
    Route::post('/nueva',[\App\Http\Controllers\ClienteController::class,'store'])->name('store');
    Route::get('/avisos/{incidencia}',[\App\Http\Controllers\ClienteController::class,'show'])->name('ver');
    Route::post('/avisos/{incidencia}/cancelar',[\App\Http\Controllers\ClienteController::class,'cancelar'])->name('cancelar');
});
Route::middleware(['auth','role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/incidencias',[AdminController::class,'incidencias'])->name('incidencias');
    Route::get('/incidencias/nueva',[AdminController::class,'nueva'])->name('nueva');
    Route::post('/incidencias/nueva',[AdminController::class,'guardar'])->name('guardar');
    Route::get('/incidencias/{incidencia}',[AdminController::class,'ver'])->name('ver');
    Route::get('/incidencias/{incidencia}/editar',[AdminController::class,'editar'])->name('editar');
    Route::put('/incidencias/{incidencia}',[AdminController::class,'actualizar'])->name('actualizar');
    Route::post('/incidencias/{incidencia}/asignar',[AdminController::class,'asignar'])->name('asignar');
    Route::post('/incidencias/{incidencia}/cancelar',[AdminController::class,'cancelar'])->name('cancelar');
    Route::get('/comunidades',[AdminController::class,'comunidades'])->name('comunidades');
    Route::post('/comunidades',[AdminController::class,'storeComunidad'])->name('comunidades.store');
    Route::put('/comunidades/{comunidad}',[AdminController::class,'actualizarComunidad'])->name('comunidades.update');
    Route::get('/liquidaciones',[AdminController::class,'liquidaciones'])->name('liquidaciones');
});
