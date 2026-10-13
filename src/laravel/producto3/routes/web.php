<?php

use Illuminate\Support\Facades\Route;

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
