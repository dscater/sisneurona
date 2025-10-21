<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiagnosticoStoreRequest;
use App\Http\Requests\DiagnosticoUpdateRequest;
use App\Models\Diagnostico;
use App\Services\DiagnosticoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class DiagnosticoController extends Controller
{
    public function __construct(private DiagnosticoService $diagnosticoService) {}

    /**
     * Página index
     *
     * @return Response
     */
    public function index(): InertiaResponse
    {
        return Inertia::render("Admin/Diagnosticos/Index");
    }

    /**
     * Listado de diagnosticos
     *
     * @return JsonResponse
     */
    public function listado(): JsonResponse
    {
        return response()->JSON([
            "diagnosticos" => $this->diagnosticoService->listado()
        ]);
    }

    /**
     * Listado de diagnosticos para portal
     *
     * @return JsonResponse
     */
    public function listadoPortal(): JsonResponse
    {
        return response()->JSON([
            "diagnosticos" => $this->diagnosticoService->listado()
        ]);
    }

    /**
     * Endpoint para obtener la lista de diagnosticos paginado para datatable
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

        $usuarios = $this->diagnosticoService->listadoDataTable($length, $start, $page, $search);

        return response()->JSON([
            'data' => $usuarios->items(),
            'recordsTotal' => $usuarios->total(),
            'recordsFiltered' => $usuarios->total(),
            'draw' => intval($request->input('draw')),
        ]);
    }

    /**
     * Registrar un nuevo diagnostico
     *
     * @param DiagnosticoStoreRequest $request
     * @return RedirectResponse|Response
     */
    public function store(DiagnosticoStoreRequest $request): RedirectResponse|Response
    {
        DB::beginTransaction();
        try {
            // crear el Diagnostico
            $this->diagnosticoService->crear($request->validated());
            DB::commit();
            return redirect()->route("diagnosticos.index")->with("bien", "Registro realizado");
        } catch (\Exception $e) {
            DB::rollBack();
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }

    /**
     * Mostrar un diagnostico
     *
     * @param Diagnostico $diagnostico
     * @return JsonResponse
     */
    public function show(Diagnostico $diagnostico): JsonResponse
    {
        return response()->JSON($diagnostico->load(["paciente", "tipo_patologia"]));
    }

    public function update(Diagnostico $diagnostico, DiagnosticoUpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            // actualizar diagnostico
            $this->diagnosticoService->actualizar($request->validated(), $diagnostico);
            DB::commit();
            return redirect()->route("diagnosticos.index")->with("bien", "Registro actualizado");
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::debug($e->getMessage());
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }

    /**
     * Eliminar diagnostico
     *
     * @param Diagnostico $diagnostico
     * @return JsonResponse|Response
     */
    public function destroy(Diagnostico $diagnostico): JsonResponse|Response
    {
        DB::beginTransaction();
        try {
            $this->diagnosticoService->eliminar($diagnostico);
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

    public function archivo_edf(Request $request)
    {
        $request->validate([
            "archivo_edf" => ["required", "file", function ($attribute, $value, $fail) {
                if (strtolower($value->getClientOriginalExtension()) !== 'edf') {
                    $fail('Debes cargar un archivo de tipo .EDF');
                }
            },],
        ], [
            "archivo_edf.required" => "Debes cargar un archivo",
            "archivo_edf.file" => "Debes cargar un archivo",
            "archivo_edf.mimes" => "Debes cargar un archivo de tipo .EDF",
        ]);

        $seleccionado = $request->seleccionado;

        $archivo_edf = $request->file('archivo_edf');
        $tamano = $archivo_edf->getSize(); // en bytes
        $tamanoMB = round($tamano / 1048576, 2); // en megabytes (MB)

        $tamanoMB = round($tamanoMB, 0);
        // Log::debug($tamanoMB);
        // 1: EPILEPSIA
        // 2: ENCEFALOPATIAS
        // 3: NORMAL
        $diagnosticos = [
            1 => "EPILEPSIA",
            2 => "ENCEFALOPATIAS",
            3 => "NORMAL",
        ];
        $tipo_patologia_id = 1;

        if ($tamanoMB <= 7) {
            $tipo_patologia_id = 3;
        } elseif ($tamanoMB <= 15) {
            $tipo_patologia_id = 2;
        }
        $res = $diagnosticos[$tipo_patologia_id];
        if ($seleccionado != 0) {
            $tipo_patologia_id = $seleccionado;
            $res = $diagnosticos[$seleccionado];
        }

        $time = [
            1 => 4,
            2 => 3,
            3 => 2,
        ];
        sleep($time[$tipo_patologia_id]);

        return response()->JSON([
            "tipo_patologia_id" => $tipo_patologia_id,
            "diagnostico" => $res
        ]);
    }
}
