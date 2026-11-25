<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ServicioZonaController;

Route::get('/servicios/zonas', [ServicioZonaController::class, 'index']);