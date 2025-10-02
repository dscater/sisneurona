<?php

namespace App\Http\Controllers;

use App\Http\Requests\TipoPatologiaStoreRequest;
use App\Http\Requests\TipoPatologiaUpdateRequest;
use App\Models\TipoPatologia;
use App\Services\TipoPatologiaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class TipoPatologiaController extends Controller
{
    public function __construct(private TipoPatologiaService $tipoPatologiaService) {}

    /**
     * Página index
     *
     * @return Response
     */
    public function index(): InertiaResponse
    {
        return Inertia::render("Admin/TipoPatologias/Index");
    }

    /**
     * Listado de tipo_patologias
     *
     * @return JsonResponse
     */
    public function listado(): JsonResponse
    {
        return response()->JSON([
            "tipo_patologias" => $this->tipoPatologiaService->listado()
        ]);
    }

    /**
     * Listado de tipo_patologias para portal
     *
     * @return JsonResponse
     */
    public function listadoPortal(): JsonResponse
    {
        return response()->JSON([
            "tipo_patologias" => $this->tipoPatologiaService->listado()
        ]);
    }

    /**
     * Endpoint para obtener la lista de tipo_patologias paginado para datatable
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function api(Request $request): JsonResponse
    {

        $length = (int)$request->input('length', 10); // Valor de `length` enviado por DataTable
        $start = (int)$request->input('start', 0); // Índice de inicio enviado por DataTable
        $page = (int)(($start / $length) + 1); // Cálculo de la página actual
        $search = (string)$request->input('search', '');

        $usuarios = $this->tipoPatologiaService->listadoDataTable($length, $start, $page, $search);

        return response()->JSON([
            'data' => $usuarios->items(),
            'recordsTotal' => $usuarios->total(),
            'recordsFiltered' => $usuarios->total(),
            'draw' => intval($request->input('draw')),
        ]);
    }

    /**
     * Registrar un nuevo tipo_patologia
     *
     * @param TipoPatologiaStoreRequest $request
     * @return RedirectResponse|Response
     */
    public function store(TipoPatologiaStoreRequest $request): RedirectResponse|Response
    {
        DB::beginTransaction();
        try {
            // crear el TipoPatologia
            $this->tipoPatologiaService->crear($request->validated());
            DB::commit();
            return redirect()->route("tipo_patologias.index")->with("bien", "Registro realizado");
        } catch (\Exception $e) {
            DB::rollBack();
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }

    /**
     * Mostrar un tipo_patologia
     *
     * @param TipoPatologia $tipo_patologia
     * @return JsonResponse
     */
    public function show(TipoPatologia $tipo_patologia): JsonResponse
    {
        return response()->JSON($tipo_patologia);
    }

    public function update(TipoPatologia $tipo_patologia, TipoPatologiaUpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            // actualizar tipo_patologia
            $this->tipoPatologiaService->actualizar($request->validated(), $tipo_patologia);
            DB::commit();
            return redirect()->route("tipo_patologias.index")->with("bien", "Registro actualizado");
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::debug($e->getMessage());
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }

    /**
     * Eliminar tipo_patologia
     *
     * @param TipoPatologia $tipo_patologia
     * @return JsonResponse|Response
     */
    public function destroy(TipoPatologia $tipo_patologia): JsonResponse|Response
    {
        DB::beginTransaction();
        try {
            $this->tipoPatologiaService->eliminar($tipo_patologia);
            DB::commit();
            return response()->JSON([
                'sw' => true,
                'message' => 'El registro se eliminó correctamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }
}
