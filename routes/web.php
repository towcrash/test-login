<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Usuario\UsuarioController;
use App\Http\Controllers\Cliente\ClienteController;
use App\Http\Controllers\Cliente\EvaluadorController;
use App\Http\Controllers\Contratista\ContratistaController;
use App\Http\Controllers\Contratista\ColaboradorController;
use App\Http\Controllers\Evaluacion\EvaluacionController;
use App\Http\Controllers\Evaluacion\PreguntaController;
use App\Http\Controllers\Evaluacion\AlternativaController;
use App\Http\Controllers\Aplicacion\AplicacionController;
use App\Http\Controllers\Recurso\RecursoController;

/*
|--------------------------------------------------------------------------
| Autenticación — rutas públicas (sin auth)
|--------------------------------------------------------------------------
*/
Route::get('/',       [LoginController::class, 'showLoginForm'])->name('login')     ->withoutMiddleware('auth:usuario');
Route::post('/login', [LoginController::class, 'login'])        ->name('login.post')->withoutMiddleware('auth:usuario');
Route::post('/logout',[LoginController::class, 'logout'])       ->name('auth.logout');

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| Usuarios y Roles
|--------------------------------------------------------------------------
*/
Route::prefix('usuario')->name('usuario.')->group(function () {
    Route::resource('usuario', UsuarioController::class);
});

/*
|--------------------------------------------------------------------------
| Clientes
|--------------------------------------------------------------------------
*/
Route::prefix('cliente')->name('cliente.')->group(function () {
    Route::resource('cliente', ClienteController::class);

    Route::post('cliente/{cliente}/usuario',
        [ClienteController::class, 'asignarUsuario'])
        ->name('cliente.asignarUsuario');

    Route::delete('cliente/{cliente}/usuario/{usuario}',
        [ClienteController::class, 'desasignarUsuario'])
        ->name('cliente.desasignarUsuario');
    
    Route::post('cliente/{cliente}/contratista',
        [ClienteController::class, 'asignarContratista'])
        ->name('cliente.asignarContratista');

    Route::delete('cliente/{cliente}/contratista/{contratista}',
        [ClienteController::class, 'desasignarContratista'])
        ->name('cliente.desasignarContratista');

    Route::post('cliente/{cliente}/evaluador',
        [ClienteController::class, 'asignarEvaluador'])
        ->name('cliente.asignarEvaluador');

    Route::delete('cliente/{cliente}/evaluador/{evaluador}',
        [ClienteController::class, 'desasignarEvaluador'])
        ->name('cliente.desasignarEvaluador');

    Route::resource('evaluador', EvaluadorController::class);

    Route::post('cliente/{cliente}/evaluacion',
        [ClienteController::class, 'asignarEvaluacion'])
        ->name('cliente.asignarEvaluacion');

    Route::delete('cliente/{cliente}/evaluacion/{evaluacion}',
        [ClienteController::class, 'desasignarEvaluacion'])
        ->name('cliente.desasignarEvaluacion');

    Route::post('cliente/{cliente}/evaluacion/{evaluacion}/evaluador',
        [ClienteController::class, 'asignarEvaluadorEvaluacion'])
        ->name('cliente.asignarEvaluadorEvaluacion');

    Route::delete('cliente/{cliente}/evaluacion/{evaluacion}/evaluador/{evaluador}',
        [ClienteController::class, 'desasignarEvaluadorEvaluacion'])
        ->name('cliente.desasignarEvaluadorEvaluacion');
});

/*
|--------------------------------------------------------------------------
| Contratistas
|--------------------------------------------------------------------------
*/
Route::prefix('contratista')->name('contratista.')->group(function () {
    Route::resource('contratista', ContratistaController::class);

    Route::post('contratista/{contratista}/colaborador',
        [ContratistaController::class, 'asignarColaborador'])
        ->name('contratista.asignarColaborador');

    Route::delete('contratista/{contratista}/colaborador/{colaborador}',
        [ContratistaController::class, 'desasignarColaborador'])
        ->name('contratista.desasignarColaborador');

    Route::resource('colaborador', ColaboradorController::class);
});

/*
|--------------------------------------------------------------------------
| Evaluaciones
|--------------------------------------------------------------------------
*/
Route::prefix('evaluacion')->name('evaluacion.')->group(function () {
    Route::resource('evaluacion', EvaluacionController::class);
    Route::resource('pregunta',   PreguntaController::class);
    Route::resource('alternativa',AlternativaController::class);
});

/*
|--------------------------------------------------------------------------
| Aplicaciones
|--------------------------------------------------------------------------
*/
Route::prefix('aplicacion')->name('aplicacion.')->group(function () {
    Route::resource('aplicacion', AplicacionController::class)->only(['index', 'show', 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Recursos
|--------------------------------------------------------------------------
*/
Route::prefix('recurso')->name('recurso.')->group(function () {
    Route::resource('recurso', RecursoController::class);
});