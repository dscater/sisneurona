<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class PermisoService
{
    protected $arrayPermisos = [
        "ADMINISTRADOR" => [
            "usuarios.api",
            "usuarios.index",
            "usuarios.listado",
            "usuarios.create",
            "usuarios.store",
            "usuarios.edit",
            "usuarios.show",
            "usuarios.update",
            "usuarios.destroy",
            "usuarios.password",

            "pacientes.api",
            "pacientes.listado",
            "pacientes.index",
            "pacientes.create",
            "pacientes.store",
            "pacientes.edit",
            "pacientes.show",
            "pacientes.update",
            "pacientes.destroy",

            "tipo_patologias.api",
            "tipo_patologias.listado",
            "tipo_patologias.index",
            "tipo_patologias.create",
            "tipo_patologias.store",
            "tipo_patologias.edit",
            "tipo_patologias.show",
            "tipo_patologias.update",

            "historial_pacientes.api",
            "historial_pacientes.listado",
            "historial_pacientes.index",
            "historial_pacientes.create",
            "historial_pacientes.store",
            "historial_pacientes.edit",
            "historial_pacientes.show",
            "historial_pacientes.update",
            "historial_pacientes.destroy",

            "diagnosticos.api",
            "diagnosticos.listado",
            "diagnosticos.index",
            "diagnosticos.create",
            "diagnosticos.store",
            "diagnosticos.edit",
            "diagnosticos.show",
            "diagnosticos.update",
            "diagnosticos.destroy",

            "configuracions.index",
            "configuracions.create",
            "configuracions.edit",
            "configuracions.update",
            "configuracions.destroy",

            "reportes.usuarios",
            "reportes.r_usuarios",
            "reportes.pacientes",
            "reportes.r_pacientes",
        ],
        "EMPLEADO" => [],
    ];

    public function getPermisosUser()
    {
        $user = Auth::user();
        $permisos = [];
        if ($user) {
            return $this->arrayPermisos[$user->tipo];
        }

        return $permisos;
    }
}
