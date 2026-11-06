<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TechController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;

Route::get('/', function () {
    return response()->json([
        'proyecto' => env('APP_NAME'),
        'grupo' => env('GROUP_CODE'),
        'estudiante' => env('STUDENT_NAME'),
        'message' => 'Aplicación Laravel en ejecución',
        'laravel_environment' => app()->environment(),
        'version' => app()->version(),
        'php_version' => phpversion(),
        
    ]);
});

// Rutas de Administrador
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    get('/calendario', [AdminController::class, 'calendario'])->name('admin.calendario');
    post('/asignar', [AdminController::class, 'asignarTecnico'])->name('admin.asignar');
});

// Rutas de Técnico
Route::middleware(['auth', 'role:tecnico'])->prefix('tecnico')->group(function () {
    post('/cambiar-estado', [TechController::class, 'cambiarEstado'])->name('tech.cambiar_estado');
});

// Rutas de Login y Logout
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Rutas de Registro
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
