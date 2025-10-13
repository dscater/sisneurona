<?php

namespace App\Services;

use App\Models\Diagnostico;
use App\Models\HistorialPaciente;
use App\Services\HistorialAccionService;
use App\Models\Paciente;
use App\Models\ReporteFinanciero;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PacienteService
{
    private $modulo = "PACIENTES";

    public function __construct(private HistorialAccionService $historialAccionService) {}

    public function listado(): Collection
    {
        $pacientes = Paciente::select("pacientes.*");
        $pacientes->where("status", 1);
        $pacientes = $pacientes->get();
        return $pacientes;
    }

    public function listadoDataTable(int $length, int $start, int $page, string $search): LengthAwarePaginator
    {
        $pacientes = Paciente::select("pacientes.*");
        $pacientes->where("status", 1);
        $pacientes = $pacientes->paginate($length, ['*'], 'page', $page);
        return $pacientes;
    }

    /**
     * Crear paciente
     *
     * @param array $datos
     * @return Paciente
     */
    public function crear(array $datos): Paciente
    {
        $paciente = Paciente::create([
            "nombre" => mb_strtoupper($datos["nombre"]),
            "paterno" => mb_strtoupper($datos["paterno"]),
            "materno" => mb_strtoupper($datos["materno"]),
            "ci" => mb_strtoupper($datos["ci"]),
            "ci_exp" => $datos["ci_exp"],
            "fecha_nac" => $datos["fecha_nac"],
            "genero" => $datos["genero"],
            "cel" => mb_strtoupper($datos["cel"]),
            "dir" => mb_strtoupper($datos["dir"]),
            "ocupacion" => mb_strtoupper($datos["ocupacion"]),
            "fecha_registro" => date("Y-m-d")
        ]);

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "CREACIÓN", "REGISTRO UN PACIENTE", $paciente, null);

        return $paciente;
    }

    /**
     * Actualizar paciente
     *
     * @param array $datos
     * @param Paciente $paciente
     * @return Paciente
     */
    public function actualizar(array $datos, Paciente $paciente): Paciente
    {
        $old_paciente = clone $paciente;

        $paciente->update([
            "nombre" => mb_strtoupper($datos["nombre"]),
            "paterno" => mb_strtoupper($datos["paterno"]),
            "materno" => mb_strtoupper($datos["materno"]),
            "ci" => mb_strtoupper($datos["ci"]),
            "ci_exp" => $datos["ci_exp"],
            "fecha_nac" => $datos["fecha_nac"],
            "genero" => $datos["genero"],
            "cel" => mb_strtoupper($datos["cel"]),
            "dir" => mb_strtoupper($datos["dir"]),
            "ocupacion" => mb_strtoupper($datos["ocupacion"]),
        ]);


        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "MODIFICACIÓN", "ACTUALIZÓ UN PACIENTE", $old_paciente, $paciente);

        return $paciente;
    }

    /**
     * Eliminar paciente
     *
     * @param Paciente $paciente
     * @return boolean
     */
    public function eliminar(Paciente $paciente): bool
    {
        // verificar usos
        $usos = HistorialPaciente::where("paciente_id", $paciente->id)->get();
        if (count($usos) > 0) {
            throw ValidationException::withMessages([
                'error' =>  "No es posible eliminar este registro porque esta siendo utilizado por otros registros",
            ]);
        }
        $usos = Diagnostico::where("paciente_id", $paciente->id)->get();
        if (count($usos) > 0) {
            throw ValidationException::withMessages([
                'error' =>  "No es posible eliminar este registro porque esta siendo utilizado por otros registros",
            ]);
        }

        $old_paciente = clone $paciente;
        $paciente->status = 0;
        $paciente->save();

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "ELIMINACIÓN", "ELIMINÓ UN PACIENTE", $old_paciente);

        return true;
    }
}
