<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{LoginController,RegisterController,PerfilController,ClienteController,AdminController,TechController,GestoraController};
use App\Http\Controllers\Admin\{EspecialidadController,TecnicoController,UsuarioController};
use App\Http\Controllers\Api\ServicioZonaController;

Route::get('/',function(){
    if(!auth()->check())return redirect()->route('login');
    return redirect()->route(match(auth()->user()->rol){
        'admin'=>'admin.dashboard','tecnico'=>'tecnico.agenda','gestora'=>'gestora.dashboard', default=>'cliente.avisos'});
})->name('inicio');
Route::middleware('guest')->group(function(){
    Route::get('/login',[LoginController::class,'showLoginForm'])->name('login');
    Route::post('/login',[LoginController::class,'login'])->name('login.post');
    Route::get('/register',[RegisterController::class,'showRegistrationForm'])->name('register');
    Route::post('/register',[RegisterController::class,'register'])->name('register.post');
});
Route::post('/logout',[LoginController::class,'logout'])->middleware('auth')->name('logout');
Route::middleware('auth')->group(function(){
    Route::get('/perfil',[PerfilController::class,'index'])->name('perfil');
    Route::put('/perfil',[PerfilController::class,'actualizarDatos'])->name('perfil.actualizar');
    Route::put('/perfil/password',[PerfilController::class,'actualizarPassword'])->name('perfil.password');
});
Route::middleware(['auth','role:particular'])->prefix('cliente')->name('cliente.')->group(function(){
    Route::get('/avisos',[ClienteController::class,'index'])->name('avisos');
    Route::get('/nueva',[ClienteController::class,'crear'])->name('crear');
    Route::post('/nueva',[ClienteController::class,'store'])->name('store');
    Route::get('/avisos/{incidencia}',[ClienteController::class,'show'])->name('ver');
    Route::post('/avisos/{incidencia}/cancelar',[ClienteController::class,'cancelar'])->name('cancelar');
});
Route::middleware(['auth','role:tecnico'])->prefix('tecnico')->name('tecnico.')->group(function(){
    Route::get('/agenda',[TechController::class,'index'])->name('agenda');
    Route::post('/cambiar-estado',[TechController::class,'cambiarEstado'])->name('cambiar_estado');
});
Route::middleware(['auth','role:gestora'])->prefix('gestora')->name('gestora.')->group(function(){
    Route::get('/',[GestoraController::class,'dashboard'])->name('dashboard');
    Route::get('/nueva',[GestoraController::class,'nueva'])->name('crear');
    Route::post('/nueva',[GestoraController::class,'store'])->name('store');
    Route::get('/avisos/{incidencia}',[GestoraController::class,'ver'])->name('ver');
    Route::post('/avisos/{incidencia}/cancelar',[GestoraController::class,'cancelar'])->name('cancelar');
    Route::get('/liquidaciones',[GestoraController::class,'liquidaciones'])->name('liquidaciones');
});
Route::middleware(['auth','role:admin'])->prefix('admin')->name('admin.')->group(function(){
    Route::get('/',[AdminController::class,'dashboard'])->name('dashboard');
    Route::get('/incidencias',[AdminController::class,'incidencias'])->name('incidencias');
    Route::get('/incidencias/nueva',[AdminController::class,'nueva'])->name('nueva');
    Route::post('/incidencias/nueva',[AdminController::class,'guardar'])->name('guardar');
    Route::get('/incidencias/{incidencia}',[AdminController::class,'ver'])->name('ver');
    Route::get('/incidencias/{incidencia}/editar',[AdminController::class,'editar'])->name('editar');
    Route::put('/incidencias/{incidencia}',[AdminController::class,'actualizar'])->name('actualizar');
    Route::post('/incidencias/{incidencia}/asignar',[AdminController::class,'asignar'])->name('asignar');
    Route::post('/incidencias/{incidencia}/cancelar',[AdminController::class,'cancelar'])->name('cancelar');
    Route::get('/calendario',[AdminController::class,'calendario'])->name('calendario');
    Route::get('/usuarios',[UsuarioController::class,'index'])->name('usuarios');
    Route::put('/usuarios/{usuario}',[UsuarioController::class,'update'])->name('usuarios.update');
    Route::get('/gestoras',[AdminController::class,'gestoras'])->name('gestoras');
    Route::post('/gestoras',[AdminController::class,'storeGestora'])->name('gestoras.store');
    Route::put('/gestoras/{gestora}',[AdminController::class,'actualizarGestora'])->name('gestoras.update');
    Route::post('/comunidades',[AdminController::class,'storeComunidad'])->name('comunidades.store');
    Route::put('/comunidades/{comunidad}',[AdminController::class,'actualizarComunidad'])->name('comunidades.update');
    Route::get('/liquidaciones',[AdminController::class,'liquidaciones'])->name('liquidaciones');
    Route::resource('especialidades',EspecialidadController::class)->only(['index','store','update','destroy'])->parameters(['especialidades'=>'especialidad']);
    Route::resource('tecnicos',TecnicoController::class)->only(['index','store','update','destroy']);
});
// Ruta pública de solo lectura, sin datos personales. No requiere la sesión web.
Route::get('/api/servicios/zonas',[ServicioZonaController::class,'index'])->name('api.servicios.zonas');
