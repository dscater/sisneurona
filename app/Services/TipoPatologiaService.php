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
    private $modulo = "TIPO DE DOCUMENTOS";

    public function __construct(private HistorialAccionService $historialAccionService) {}

    public function listado(): Collection
    {
        $tipo_documentos = TipoPatologia::select("tipo_documentos.*");
        $tipo_documentos = $tipo_documentos->get();
        return $tipo_documentos;
    }

    public function listadoDataTable(int $length, int $start, int $page, string $search): LengthAwarePaginator
    {
        $tipo_documentos = TipoPatologia::select("tipo_documentos.*");
        if ($search && trim($search) != '') {
            $tipo_documentos->where("nombre", "LIKE", "%$search%");
        }
        $tipo_documentos = $tipo_documentos->paginate($length, ['*'], 'page', $page);
        return $tipo_documentos;
    }

    /**
     * Crear tipo_documento
     *
     * @param array $datos
     * @return TipoPatologia
     */
    public function crear(array $datos): TipoPatologia
    {
        $tipo_documento = TipoPatologia::create([
            "nombre" => mb_strtoupper($datos["nombre"]),
            "descripcion" => mb_strtoupper($datos["descripcion"]),
            "fecha_registro" => date("Y-m-d")
        ]);
        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "CREACIÓN", "REGISTRO UN TIPO DE DOCUMENTO", $tipo_documento, null);

        return $tipo_documento;
    }

    /**
     * Actualizar tipo_documento
     *
     * @param array $datos
     * @param TipoPatologia $tipo_documento
     * @return TipoPatologia
     */
    public function actualizar(array $datos, TipoPatologia $tipo_documento): TipoPatologia
    {
        $old_tipo_documento = clone $tipo_documento;
        $tipo_documento->update([
            "nombre" => mb_strtoupper($datos["nombre"]),
            "descripcion" => mb_strtoupper($datos["descripcion"]),
        ]);
        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "MODIFICACIÓN", "ACTUALIZÓ UN TIPO DE DOCUMENTO", $old_tipo_documento, $tipo_documento);

        return $tipo_documento;
    }

    /**
     * Eliminar tipo_documento
     *
     * @param TipoPatologia $tipo_documento
     * @return boolean
     */
    public function eliminar(TipoPatologia $tipo_documento): bool
    {
        // verificar usos
        $usos = ReporteFinanciero::where("tipo_documento_id", $tipo_documento->id)->get();
        if (count($usos) > 0) {
            throw ValidationException::withMessages([
                'error' =>  "No es posible eliminar este registro porque esta siendo utilizado por otros registros",
            ]);
        }
        $old_tipo_documento = clone $tipo_documento;
        $tipo_documento->delete();

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "ELIMINACIÓN", "ELIMINÓ UN TIPO DE DOCUMENTO", $old_tipo_documento);

        return true;
    }
}
