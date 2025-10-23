<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Diagnostico;
use App\Models\Material;
use App\Models\Paciente;
use App\Models\Producto;
use App\Models\Publicacion;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{

    public function permisosUsuario(Request $request)
    {
        return response()->JSON([
            "permisos" => Auth::user()->permisos
        ]);
    }

    public function getUser()
    {
        return response()->JSON([
            "user" => Auth::user()
        ]);
    }

    public static function getInfoBoxUser()
    {
        $permisos = [];
        $array_infos = [];
        if (Auth::check()) {
            $oUser = new User();
            $permisos = $oUser->permisos;
            if ($permisos == '*' || (is_array($permisos) && in_array('usuarios.index', $permisos))) {
                $array_infos[] = [
                    'label' => 'USUARIOS',
                    'cantidad' => User::where('id', '!=', 1)->count(),
                    'color' => 'bg-principal',
                    'icon' => "fa-users",
                    "url" => "usuarios.index"
                ];
            }

            if ($permisos == '*' || (is_array($permisos) && in_array('usuarios.index', $permisos))) {
                $pacientes = Paciente::where("status", 1)->count();
                $array_infos[] = [
                    'label' => 'PACIENTES',
                    'cantidad' => $pacientes,
                    'color' => 'bg-principal',
                    'icon' => "fa-user-friends",
                    "url" => "usuarios.index"
                ];
            }

            if ($permisos == '*' || (is_array($permisos) && in_array('usuarios.index', $permisos))) {
                $diagnosticos = Diagnostico::where("status", 1)->count();
                $array_infos[] = [
                    'label' => 'DIAGNOSTICOS',
                    'cantidad' => $diagnosticos,
                    'color' => 'bg-principal',
                    'icon' => "fa-clipboard-list",
                    "url" => "usuarios.index"
                ];
            }
        }


        return $array_infos;
    }
}
