<?php

namespace App\Services;

use App\Models\HistorialArchivo;
use App\Models\HistorialPaciente;
use App\Services\HistorialAccionService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class HistorialPacienteService
{
    private $modulo = "HISTORIAL DE PACIENTE";

    public function __construct(private HistorialAccionService $historialAccionService, private HistorialArchivoService $historialArchivoService) {}

    public function listado(): Collection
    {
        $historial_pacientes = HistorialPaciente::with(["paciente", "historial_archivos"])->select("historial_pacientes.*");
        $historial_pacientes = $historial_pacientes->get();
        return $historial_pacientes;
    }

    public function listadoDataTable(int $length, int $start, int $page, string $search): LengthAwarePaginator
    {
        $historial_pacientes = HistorialPaciente::with(["paciente", "historial_archivos"])
            ->select("historial_pacientes.*");
        if (!empty($search)) {
            // Detectar si el texto parece una fecha (ej: 12/10/2025 o 12-10-2025)
            $fecha = null;
            if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', trim($search), $matches)) {
                try {
                    $fecha = Carbon::createFromFormat('d/m/Y', str_replace('-', '/', $search))->format('Y-m-d');
                } catch (\Exception $e) {
                    // Si falla el formato anterior, intentar con d-m-Y
                    try {
                        $fecha = Carbon::createFromFormat('d-m-Y', $search)->format('Y-m-d');
                    } catch (\Exception $e2) {
                        $fecha = null;
                    }
                }
            }

            $historial_pacientes->where(function ($query) use ($search, $fecha) {
                // Buscar por nombre completo del paciente
                $query->orWhereHas('paciente', function ($q) use ($search) {
                    $q->where(DB::raw("CONCAT_WS(' ', nombre, paterno, materno)"), 'LIKE', "%{$search}%");
                });

                // Buscar por fecha si es válida
                if ($fecha) {
                    $query->orWhereDate('historial_pacientes.fecha_registro', $fecha);
                }
            });
        }
        $historial_pacientes = $historial_pacientes->paginate($length, ['*'], 'page', $page);
        return $historial_pacientes;
    }

    /**
     * Crear historial_paciente
     *
     * @param array $datos
     * @return HistorialPaciente
     */
    public function crear(array $datos): HistorialPaciente
    {
        $historial_paciente = HistorialPaciente::create([
            "paciente_id" => $datos["paciente_id"],
            "motivo_consulta" => mb_strtoupper($datos["motivo_consulta"]),
            "historial_enfermedad" => mb_strtoupper($datos["historial_enfermedad"]),
            "antecedentes_personales" => mb_strtoupper($datos["antecedentes_personales"]),
            "antecedentes_familiares" => mb_strtoupper($datos["antecedentes_familiares"]),
            "antecedentes_no_personales" => mb_strtoupper($datos["antecedentes_no_personales"]),
            "examenes_neurologicos" => mb_strtoupper($datos["examenes_neurologicos"]),
            "tratamientos" => mb_strtoupper($datos["tratamientos"]),
            "evoluciones" => mb_strtoupper($datos["evoluciones"]),
            "consultas" => mb_strtoupper($datos["consultas"]),
            "fecha_registro" => date("Y-m-d")
        ]);

        // registrar archivos
        if (!empty($datos["historial_archivos"])) {
            foreach ($datos["historial_archivos"] as $key => $archivo) {
                $this->historialArchivoService->guardarHistorialArchivo($historial_paciente, $archivo["file"], $key);
            }
        }
        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "CREACIÓN", "REGISTRO UN HISTORIAL DE PACIENTE", $historial_paciente, null);

        return $historial_paciente;
    }

    /**
     * Actualizar historial_paciente
     *
     * @param array $datos
     * @param HistorialPaciente $historial_paciente
     * @return HistorialPaciente
     */
    public function actualizar(array $datos, HistorialPaciente $historial_paciente): HistorialPaciente
    {
        $old_historial_paciente = clone $historial_paciente;
        $old_historial_paciente->loadMissing(["historial_archivos"]);

        $historial_paciente->update([
            "paciente_id" => $datos["paciente_id"],
            "motivo_consulta" => mb_strtoupper($datos["motivo_consulta"]),
            "historial_enfermedad" => mb_strtoupper($datos["historial_enfermedad"]),
            "antecedentes_personales" => mb_strtoupper($datos["antecedentes_personales"]),
            "antecedentes_familiares" => mb_strtoupper($datos["antecedentes_familiares"]),
            "antecedentes_no_personales" => mb_strtoupper($datos["antecedentes_no_personales"]),
            "examenes_neurologicos" => mb_strtoupper($datos["examenes_neurologicos"]),
            "tratamientos" => mb_strtoupper($datos["tratamientos"]),
            "evoluciones" => mb_strtoupper($datos["evoluciones"]),
            "consultas" => mb_strtoupper($datos["consultas"]),
        ]);

        // actualizar archivos
        if (!empty($datos["historial_archivos"])) {
            foreach ($datos["historial_archivos"] as $key => $imagen) {
                if ($imagen["id"] == 0) {
                    $this->historialArchivoService->guardarHistorialArchivo($historial_paciente, $imagen["file"], $key);
                }
            }
        }

        // archivos eliminados
        if (!empty($datos["eliminados_archivos"])) {
            foreach ($datos["eliminados_archivos"] as $key => $eliminado) {
                $historialArchivo = HistorialArchivo::find($eliminado);
                if ($historialArchivo) {
                    $this->historialArchivoService->eliminarHistorialArchivo($historialArchivo);
                }
            }
        }
        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "MODIFICACIÓN", "ACTUALIZÓ UN HISTORIAL DE PACIENTE", $old_historial_paciente, $historial_paciente, ["historial_archivos"]);

        return $historial_paciente;
    }

    /**
     * Eliminar historial_paciente
     *
     * @param HistorialPaciente $historial_paciente
     * @return boolean
     */
    public function eliminar(HistorialPaciente $historial_paciente): bool
    {
        $old_historial_paciente = clone $historial_paciente;
        $old_historial_paciente->loadMissing(["historial_archivos"]);

        $historial_paciente->status = 0;
        $historial_paciente->save();

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "ELIMINACIÓN", "ELIMINÓ UN HISTORIAL DE PACIENTE", $old_historial_paciente, $historial_paciente, ["historial_archivos"]);

        return true;
    }
}
