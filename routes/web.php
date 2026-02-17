<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ContratistaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\EvaluadorController;
use App\Http\Controllers\ColaboradorController;
use App\Http\Controllers\EvaluacionController;
use App\Http\Controllers\PreguntaController;
use App\Http\Controllers\AlternativaController;
use App\Http\Controllers\AplicacionController;
use App\Http\Controllers\DiscoController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\RecursoController;
use App\Http\Controllers\TipoRecursoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Rutas de Autenticación
|--------------------------------------------------------------------------
*/
Route::get('/', [LoginController::class, 'showLoginForm'])  ->name('login');
Route::post('/login', [LoginController::class, 'login'])    ->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])  ->name('logout');

    
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| Rutas de Usuarios y Roles
|--------------------------------------------------------------------------
*/
Route::prefix('usuario')->name('usuario.')->group(function () {
    Route::resource('usuario', UsuarioController::class);
});

Route::prefix('rol')->name('rol.')->group(function () {
    Route::resource('rol', RolController::class);
});

/*
|--------------------------------------------------------------------------
| Rutas de Clientes
|--------------------------------------------------------------------------
*/
Route::prefix('cliente')->name('cliente.')->group(function () {
    Route::resource('cliente', ClienteController::class);
    Route::resource('evaluador', EvaluadorController::class);
});

/*
|--------------------------------------------------------------------------
| Rutas de Contratistas
|--------------------------------------------------------------------------
*/
Route::prefix('contratista')->name('contratista.')->group(function () {
    Route::resource('contratista', ContratistaController::class);
    Route::resource('colaborador', ColaboradorController::class);
});

/*
|--------------------------------------------------------------------------
| Rutas de Evaluaciones
|--------------------------------------------------------------------------
*/
Route::prefix('evaluacion')->name('evaluacion.')->group(function () {
    Route::resource('evaluacion', EvaluacionController::class);
    Route::resource('pregunta', PreguntaController::class);
    Route::resource('alternativa', AlternativaController::class);
});

/*
|--------------------------------------------------------------------------
| Rutas de Aplicaciones
|--------------------------------------------------------------------------
*/
Route::prefix('aplicacion')->name('aplicacion.')->group(function () {
    Route::resource('aplicacion', AplicacionController::class)->only(['index', 'show', 'destroy']);
});


/*
|--------------------------------------------------------------------------
| Rutas de Recursos
|--------------------------------------------------------------------------
*/
Route::prefix('recurso')->name('recurso.')->group(function () {
    Route::resource('recurso', RecursoController::class);
}); 