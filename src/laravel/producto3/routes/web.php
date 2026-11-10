<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TechController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\PerfilController;

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
    Route::get('/calendario', [AdminController::class, 'calendario'])->name('admin.calendario');
});

// 5. Espacios reservados para compañeros (Técnico / Cliente particular)
Route::middleware(['auth', 'role:tecnico'])->group(function () {
    Route::get('/tecnico/agenda', function () {
        return view('tecnico.agenda');
    })->name('tecnico.agenda');
});

Route::middleware(['auth', 'role:particular'])->group(function () {
    Route::get('/cliente/avisos', function () {
        return view('cliente.avisos');
    })->name('cliente.avisos');
});
