<?php

namespace App\Services;

use App\Models\Diagnostico;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DiagnosticoService
{
    private $modulo = "DIAGNOSTICO";

    public function __construct(private  CargarArchivoService $cargarArchivoService, private HistorialAccionService $historialAccionService) {}

    public function listado(): Collection
    {
        $diagnosticos = Diagnostico::with(["paciente", "tipo_patologia"])->select("diagnosticos.*");
        $diagnosticos = $diagnosticos->get();
        return $diagnosticos;
    }

    public function listadoDataTable(int $length, int $start, int $page, string $search): LengthAwarePaginator
    {
        $diagnosticos = Diagnostico::with(["paciente", "tipo_patologia"])->select("diagnosticos.*");
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

            $diagnosticos->where(function ($query) use ($search, $fecha) {
                // Buscar por nombre completo del paciente
                $query->orWhereHas('paciente', function ($q) use ($search) {
                    $q->where(DB::raw("CONCAT_WS(' ', nombre, paterno, materno)"), 'LIKE', "%{$search}%");
                });

                // Buscar por fecha si es válida
                if ($fecha) {
                    $query->orWhereDate('diagnosticos.fecha_registro', $fecha);
                }
            });
        }
        $diagnosticos = $diagnosticos->paginate($length, ['*'], 'page', $page);
        return $diagnosticos;
    }

    /**
     * Crear diagnostico
     *
     * @param array $datos
     * @return Diagnostico
     */
    public function crear(array $datos): Diagnostico
    {
        $diagnostico = Diagnostico::create([
            "paciente_id" => $datos["paciente_id"],
            "diagnostico" => mb_strtoupper($datos["diagnostico"]),
            "tipo_patologia_id" => $datos["tipo_patologia_id"],
            "fecha_registro" => date("Y-m-d")
        ]);

        // registrar archivo
        $this->cargarArchivo($diagnostico, $datos["archivo_edf"]);

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "CREACIÓN", "REGISTRO UN DIAGNOSTICO", $diagnostico, null, ['paciente']);

        return $diagnostico;
    }

    /**
     * Actualizar diagnostico
     *
     * @param array $datos
     * @param Diagnostico $diagnostico
     * @return Diagnostico
     */
    public function actualizar(array $datos, Diagnostico $diagnostico): Diagnostico
    {
        $old_diagnostico = clone $diagnostico;
        $old_diagnostico->loadMissing(["paciente"]);

        $diagnostico->update([
            "paciente_id" => $datos["paciente_id"],
            "diagnostico" => mb_strtoupper($datos["diagnostico"]),
            "tipo_patologia_id" => $datos["tipo_patologia_id"],
        ]);

        // actualizar archivo
        // cargar archivo_edf
        if ($datos["archivo_edf"] && !is_string($datos["archivo_edf"])) {
            $this->cargarArchivo($diagnostico, $datos["archivo_edf"]);
        }

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "MODIFICACIÓN", "ACTUALIZÓ UN DIAGNOSTICO", $old_diagnostico, $diagnostico, ["paciente"]);

        return $diagnostico;
    }

    /**
     * Eliminar diagnostico
     *
     * @param Diagnostico $diagnostico
     * @return boolean
     */
    public function eliminar(Diagnostico $diagnostico): bool
    {
        $old_diagnostico = clone $diagnostico;

        $diagnostico->status = 0;
        $diagnostico->save();

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "ELIMINACIÓN", "ELIMINÓ UN DIAGNOSTICO", $old_diagnostico, $diagnostico);

        return true;
    }

    /**
     * Cargar archivo
     *
     * @param Diagnostico $diagnostico
     * @param UploadedFile $archivo
     * @return void
     */
    public function cargarArchivo(Diagnostico $diagnostico, UploadedFile $archivo): void
    {
        if ($diagnostico->archivo_edf) {
            \File::delete(public_path("files/diagnosticos/" . $diagnostico->archivo_edf));
        }

        $nombre = $diagnostico->id . time();
        $diagnostico->archivo_edf = $this->cargarArchivoService->cargarArchivo($archivo, public_path("files/diagnosticos"), $nombre);
        $diagnostico->save();
    }
}
