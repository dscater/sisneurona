<?php

namespace App\Http\Controllers;

use App\Http\Requests\HistorialPacienteStoreRequest;
use App\Http\Requests\HistorialPacienteUpdateRequest;
use App\Models\HistorialPaciente;
use App\Services\HistorialPacienteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class HistorialPacienteController extends Controller
{
    public function __construct(private HistorialPacienteService $historialHistorialPacienteService) {}

    /**
     * Página index
     *
     * @return Response
     */
    public function index(): InertiaResponse
    {
        return Inertia::render("Admin/HistorialPacientes/Index");
    }

    /**
     * Listado de historial_pacientes
     *
     * @return JsonResponse
     */
    public function listado(): JsonResponse
    {
        return response()->JSON([
            "historial_pacientes" => $this->historialHistorialPacienteService->listado()
        ]);
    }

    /**
     * Listado de historial_pacientes para portal
     *
     * @return JsonResponse
     */
    public function listadoPortal(): JsonResponse
    {
        return response()->JSON([
            "historial_pacientes" => $this->historialHistorialPacienteService->listado()
        ]);
    }

    /**
     * Endpoint para obtener la lista de historial_pacientes paginado para datatable
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

        $usuarios = $this->historialHistorialPacienteService->listadoDataTable($length, $start, $page, $search);

        return response()->JSON([
            'data' => $usuarios->items(),
            'recordsTotal' => $usuarios->total(),
            'recordsFiltered' => $usuarios->total(),
            'draw' => intval($request->input('draw')),
        ]);
    }

    /**
     * Registrar un nuevo historial_paciente
     *
     * @param HistorialPacienteStoreRequest $request
     * @return RedirectResponse|Response
     */
    public function store(HistorialPacienteStoreRequest $request): RedirectResponse|Response
    {
        DB::beginTransaction();
        try {
            // crear el HistorialPaciente
            $this->historialHistorialPacienteService->crear($request->validated());
            DB::commit();
            return redirect()->route("historial_pacientes.index")->with("bien", "Registro realizado");
        } catch (\Exception $e) {
            DB::rollBack();
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }

    /**
     * Mostrar un historial_paciente
     *
     * @param HistorialPaciente $historial_paciente
     * @return JsonResponse
     */
    public function show(HistorialPaciente $historial_paciente): JsonResponse
    {
        return response()->JSON($historial_paciente->load(["paciente", "historial_archivos"]));
    }

    public function update(HistorialPaciente $historial_paciente, HistorialPacienteUpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            // actualizar historial_paciente
            $this->historialHistorialPacienteService->actualizar($request->validated(), $historial_paciente);
            DB::commit();
            return redirect()->route("historial_pacientes.index")->with("bien", "Registro actualizado");
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::debug($e->getMessage());
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }

    /**
     * Eliminar historial_paciente
     *
     * @param HistorialPaciente $historial_paciente
     * @return JsonResponse|Response
     */
    public function destroy(HistorialPaciente $historial_paciente): JsonResponse|Response
    {
        DB::beginTransaction();
        try {
            $this->historialHistorialPacienteService->eliminar($historial_paciente);
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
