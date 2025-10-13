<?php

namespace App\Http\Controllers;

use App\Http\Requests\PacienteStoreRequest;
use App\Http\Requests\PacienteUpdateRequest;
use App\Models\Paciente;
use App\Services\PacienteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PacienteController extends Controller
{
    public function __construct(private PacienteService $pacienteService) {}

    /**
     * Página index
     *
     * @return Response
     */
    public function index(): InertiaResponse
    {
        return Inertia::render("Admin/Pacientes/Index");
    }

    /**
     * Listado de pacientes
     *
     * @return JsonResponse
     */
    public function listado(): JsonResponse
    {
        return response()->JSON([
            "pacientes" => $this->pacienteService->listado()
        ]);
    }

    /**
     * Listado de pacientes para portal
     *
     * @return JsonResponse
     */
    public function listadoPortal(): JsonResponse
    {
        return response()->JSON([
            "pacientes" => $this->pacienteService->listado()
        ]);
    }

    /**
     * Endpoint para obtener la lista de pacientes paginado para datatable
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

        $usuarios = $this->pacienteService->listadoDataTable($length, $start, $page, $search);

        return response()->JSON([
            'data' => $usuarios->items(),
            'recordsTotal' => $usuarios->total(),
            'recordsFiltered' => $usuarios->total(),
            'draw' => intval($request->input('draw')),
        ]);
    }

    /**
     * Registrar un nuevo paciente
     *
     * @param PacienteStoreRequest $request
     * @return RedirectResponse|Response
     */
    public function store(PacienteStoreRequest $request): RedirectResponse|Response
    {
        DB::beginTransaction();
        try {
            // crear el Paciente
            $this->pacienteService->crear($request->validated());
            DB::commit();
            return redirect()->route("pacientes.index")->with("bien", "Registro realizado");
        } catch (\Exception $e) {
            DB::rollBack();
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }

    /**
     * Mostrar un paciente
     *
     * @param Paciente $paciente
     * @return JsonResponse
     */
    public function show(Paciente $paciente): JsonResponse
    {
        return response()->JSON($paciente);
    }

    public function update(Paciente $paciente, PacienteUpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            // actualizar paciente
            $this->pacienteService->actualizar($request->validated(), $paciente);
            DB::commit();
            return redirect()->route("pacientes.index")->with("bien", "Registro actualizado");
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::debug($e->getMessage());
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }

    /**
     * Eliminar paciente
     *
     * @param Paciente $paciente
     * @return JsonResponse|Response
     */
    public function destroy(Paciente $paciente): JsonResponse|Response
    {
        DB::beginTransaction();
        try {
            $this->pacienteService->eliminar($paciente);
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
