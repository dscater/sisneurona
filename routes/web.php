<?php

use App\Http\Controllers\PacienteController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\DiagnosticoCNNController;
use App\Http\Controllers\DiagnosticoController;
use App\Http\Controllers\HistorialPacienteController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\TipoPatologiaCOntroller;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('inicio');
    }
    return Inertia::render('Auth/Login');
});

Route::get('/clear-cache', function () {
    Artisan::call('config:cache');
    Artisan::call('config:clear');
    Artisan::call('optimize');
    return 'Cache eliminado <a href="/">Ir al inicio</a>';
})->name('clear.cache');

Route::get('/login', function () {
    if (Auth::check()) {
        return redirect()->route('inicio');
    }
    return Inertia::render('Auth/Login');
})->name("login");

Route::post('/registro/validaForm1', [RegisteredUserController::class, 'validaForm1'])->name("registro.validaForm1");
Route::get('/registro', function () {
    if (Auth::check()) {
        return redirect()->route('inicio');
    }
    return Inertia::render('Auth/Register');
})->name("registro");

Route::get("configuracions/getConfiguracion", [ConfiguracionController::class, 'getConfiguracion'])->name("configuracions.getConfiguracion");

// PORTAL

// ADMINISTRACION
Route::middleware(['auth', 'permisoUsuario'])->prefix("admin")->group(function () {
    // INICIO
    Route::get('/inicio', [InicioController::class, 'inicio'])->name('inicio');

    // CONFIGURACION
    Route::resource("configuracions", ConfiguracionController::class)->only(
        ["index", "show", "update"]
    );

    // USUARIO
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('profile/update_foto', [ProfileController::class, 'update_foto'])->name('profile.update_foto');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get("getUser", [UserController::class, 'getUser'])->name('users.getUser');
    Route::get("permisosUsuario", [UserController::class, 'permisosUsuario']);

    // USUARIOS
    Route::put("usuarios/password/{user}", [UsuarioController::class, 'actualizaPassword'])->name("usuarios.password");
    Route::get("usuarios/api", [UsuarioController::class, 'api'])->name("usuarios.api");
    Route::get("usuarios/paginado", [UsuarioController::class, 'paginado'])->name("usuarios.paginado");
    Route::get("usuarios/listado", [UsuarioController::class, 'listado'])->name("usuarios.listado");
    Route::get("usuarios/listado/byTipo", [UsuarioController::class, 'byTipo'])->name("usuarios.byTipo");
    Route::get("usuarios/show/{user}", [UsuarioController::class, 'show'])->name("usuarios.show");
    Route::put("usuarios/update/{user}", [UsuarioController::class, 'update'])->name("usuarios.update");
    Route::delete("usuarios/{user}", [UsuarioController::class, 'destroy'])->name("usuarios.destroy");
    Route::resource("usuarios", UsuarioController::class)->only(
        ["index", "store"]
    );

    // PACIENTES
    Route::get("pacientes/api", [PacienteController::class, 'api'])->name("pacientes.api");
    Route::get("pacientes/paginado", [PacienteController::class, 'paginado'])->name("pacientes.paginado");
    Route::get("pacientes/listado", [PacienteController::class, 'listado'])->name("pacientes.listado");
    Route::resource("pacientes", PacienteController::class)->only(
        ["index", "store", "edit", "show", "update", "destroy"]
    );

    // TIPO DE PATOLOGIAS
    Route::get("tipo_patologias/api", [TipoPatologiaCOntroller::class, 'api'])->name("tipo_patologias.api");
    Route::get("tipo_patologias/paginado", [TipoPatologiaCOntroller::class, 'paginado'])->name("tipo_patologias.paginado");
    Route::get("tipo_patologias/listado", [TipoPatologiaCOntroller::class, 'listado'])->name("tipo_patologias.listado");
    Route::resource("tipo_patologias", TipoPatologiaCOntroller::class)->only(
        ["index", "store", "edit", "show", "update", "destroy"]
    );

    // HISTORIAL DEL PACIENTE
    Route::get("historial_pacientes/api", [HistorialPacienteController::class, 'api'])->name("historial_pacientes.api");
    Route::get("historial_pacientes/paginado", [HistorialPacienteController::class, 'paginado'])->name("historial_pacientes.paginado");
    Route::get("historial_pacientes/listado", [HistorialPacienteController::class, 'listado'])->name("historial_pacientes.listado");
    Route::resource("historial_pacientes", HistorialPacienteController::class)->only(
        ["index", "store", "edit", "show", "update", "destroy"]
    );

    // DIAGNOSTICO CNN
    Route::post("diagnosticos/diagnosticar", [DiagnosticoCNNController::class, 'diagnosticar'])->name("diagnosticos.diagnosticar");

    // DIAGNOSTICOS
    Route::get("diagnosticos/api", [DiagnosticoController::class, 'api'])->name("diagnosticos.api");
    Route::get("diagnosticos/paginado", [DiagnosticoController::class, 'paginado'])->name("diagnosticos.paginado");
    Route::get("diagnosticos/listado", [DiagnosticoController::class, 'listado'])->name("diagnosticos.listado");
    Route::post("diagnosticos/archivo_edf", [DiagnosticoController::class, 'archivo_edf'])->name("diagnosticos.archivo_edf");
    Route::resource("diagnosticos", DiagnosticoController::class)->only(
        ["index", "store", "edit", "show", "update", "destroy"]
    );

    // REPORTES
    Route::get('reportes/usuarios', [ReporteController::class, 'usuarios'])->name("reportes.usuarios");
    Route::get('reportes/r_usuarios', [ReporteController::class, 'r_usuarios'])->name("reportes.r_usuarios");

    Route::get('reportes/pacientes', [ReporteController::class, 'pacientes'])->name("reportes.pacientes");
    Route::get('reportes/r_pacientes', [ReporteController::class, 'r_pacientes'])->name("reportes.r_pacientes");

    Route::get('reportes/historial', [ReporteController::class, 'historial'])->name("reportes.historial");
    Route::get('reportes/r_historial', [ReporteController::class, 'r_historial'])->name("reportes.r_historial");

    Route::get('reportes/diagnosticos', [ReporteController::class, 'diagnosticos'])->name("reportes.diagnosticos");
    Route::get('reportes/r_diagnosticos', [ReporteController::class, 'r_diagnosticos'])->name("reportes.r_diagnosticos");

    Route::get('reportes/gdiagnosticos', [ReporteController::class, 'gdiagnosticos'])->name("reportes.gdiagnosticos");
    Route::get('reportes/r_gdiagnosticos', [ReporteController::class, 'r_gdiagnosticos'])->name("reportes.r_gdiagnosticos");
});
require __DIR__ . '/auth.php';
