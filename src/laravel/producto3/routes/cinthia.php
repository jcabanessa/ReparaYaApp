<?php
use App\Http\Controllers\{CinthiaAdminController,ClienteController};
use Illuminate\Support\Facades\Route;

Route::middleware(['auth','role:particular'])->prefix('cliente')->name('cliente.')->group(function () {
    Route::get('/nueva',[ClienteController::class,'crear'])->name('crear');
    Route::post('/nueva',[ClienteController::class,'store'])->name('store');
    Route::get('/avisos/{incidencia}',[ClienteController::class,'show'])->name('ver');
    Route::post('/avisos/{incidencia}/cancelar',[ClienteController::class,'cancelar'])->name('cancelar');
});
Route::middleware(['auth','role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/incidencias',[CinthiaAdminController::class,'incidencias'])->name('incidencias');
    Route::get('/incidencias/nueva',[CinthiaAdminController::class,'nueva'])->name('nueva');
    Route::post('/incidencias/nueva',[CinthiaAdminController::class,'guardar'])->name('guardar');
    Route::get('/incidencias/{incidencia}',[CinthiaAdminController::class,'ver'])->name('ver');
    Route::get('/incidencias/{incidencia}/editar',[CinthiaAdminController::class,'editar'])->name('editar');
    Route::put('/incidencias/{incidencia}',[CinthiaAdminController::class,'actualizar'])->name('actualizar');
    Route::post('/incidencias/{incidencia}/asignar',[CinthiaAdminController::class,'asignar'])->name('asignar');
    Route::post('/incidencias/{incidencia}/cancelar',[CinthiaAdminController::class,'cancelar'])->name('cancelar');
    Route::get('/comunidades',[CinthiaAdminController::class,'comunidades'])->name('comunidades');
    Route::post('/comunidades',[CinthiaAdminController::class,'storeComunidad'])->name('comunidades.store');
    Route::put('/comunidades/{comunidad}',[CinthiaAdminController::class,'actualizarComunidad'])->name('comunidades.update');
    Route::get('/liquidaciones',[CinthiaAdminController::class,'liquidaciones'])->name('liquidaciones');
});
