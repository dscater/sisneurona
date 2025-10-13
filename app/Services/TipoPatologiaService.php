<?php

namespace App\Services;

use App\Models\ReporteFinanciero;
use App\Services\HistorialAccionService;
use App\Models\TipoPatologia;
use App\Models\TipoPatologiaMaterial;
use App\Models\TipoPatologiaOperario;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TipoPatologiaService
{
    private $modulo = "TIPO DE PATOLOGIAS";

    public function __construct(private HistorialAccionService $historialAccionService) {}

    public function listado(): Collection
    {
        $tipo_patologias = TipoPatologia::select("tipo_patologias.*");
        $tipo_patologias = $tipo_patologias->get();
        return $tipo_patologias;
    }

    public function listadoDataTable(int $length, int $start, int $page, string $search): LengthAwarePaginator
    {
        $tipo_patologias = TipoPatologia::select("tipo_patologias.*");
        if ($search && trim($search) != '') {
            $tipo_patologias->where("nombre", "LIKE", "%$search%");
        }
        $tipo_patologias = $tipo_patologias->paginate($length, ['*'], 'page', $page);
        return $tipo_patologias;
    }

    /**
     * Crear tipo_patologia
     *
     * @param array $datos
     * @return TipoPatologia
     */
    public function crear(array $datos): TipoPatologia
    {
        $tipo_patologia = TipoPatologia::create([
            "nombre" => mb_strtoupper($datos["nombre"]),
            "descripcion" => mb_strtoupper($datos["descripcion"]),
            "fecha_registro" => date("Y-m-d")
        ]);
        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "CREACIÓN", "REGISTRO UN TIPO DE PATOLOGIA", $tipo_patologia, null);

        return $tipo_patologia;
    }

    /**
     * Actualizar tipo_patologia
     *
     * @param array $datos
     * @param TipoPatologia $tipo_patologia
     * @return TipoPatologia
     */
    public function actualizar(array $datos, TipoPatologia $tipo_patologia): TipoPatologia
    {
        $old_tipo_patologia = clone $tipo_patologia;
        $tipo_patologia->update([
            "nombre" => mb_strtoupper($datos["nombre"]),
            "descripcion" => mb_strtoupper($datos["descripcion"]),
        ]);
        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "MODIFICACIÓN", "ACTUALIZÓ UN TIPO DE PATOLOGIA", $old_tipo_patologia, $tipo_patologia);

        return $tipo_patologia;
    }

    /**
     * Eliminar tipo_patologia
     *
     * @param TipoPatologia $tipo_patologia
     * @return boolean
     */
    public function eliminar(TipoPatologia $tipo_patologia): bool
    {
        // verificar usos
        $usos = ReporteFinanciero::where("tipo_patologia_id", $tipo_patologia->id)->get();
        if (count($usos) > 0) {
            throw ValidationException::withMessages([
                'error' =>  "No es posible eliminar este registro porque esta siendo utilizado por otros registros",
            ]);
        }
        $old_tipo_patologia = clone $tipo_patologia;
        $tipo_patologia->delete();

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "ELIMINACIÓN", "ELIMINÓ UN TIPO DE PATOLOGIA", $old_tipo_patologia);

        return true;
    }
}
