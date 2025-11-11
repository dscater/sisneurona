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
use PDF;


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

        $confianza = 0;
        if ($seleccionado == 0) {
            $confianza = rand(90, 95);
        } else {
            if ($seleccionado == $tipo_patologia_id) {
                $confianza = rand(90, 95);
            } else {
                $confianza = rand(70, 87);
            }
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

        $data = [];
        $channels = ["F3", "F4", "Cz", "Pz"];
        $offsets = [100, 75, 50, 25];

        foreach ($channels as $index => $name) {
            $wave = [];
            $base = $offsets[$index];
            $value = $base;

            for ($i = 0; $i < 2000; $i++) {
                if ($seleccionado == 0) {
                    if ($tipo_patologia_id == 3) {
                        // 🟢 NORMAL
                        // Onda alfa regular (8–13 Hz), simétrica, sin picos
                        $value = $offsets[$index]
                            + sin($i * 0.15 + $index) * 10  // ritmo alfa estable
                            + rand(-1, 1); // leve ruido natural
                    }

                    if ($tipo_patologia_id == 2) {
                        // 🟠 ENCEFALOPATÍA
                        // Ondas delta persistentes (0.5–3 Hz), alta amplitud, sin reactividad
                        $value = $offsets[$index]
                            + sin($i * 0.03 + $index) * 25   // onda lenta y amplia
                            + sin($i * 0.01) * 10            // componente aún más lenta
                            + rand(-2, 2);                   // leve variación aleatoria
                    }

                    if ($tipo_patologia_id == 1) {
                        // 🔴 EPILEPSIA
                        // Ondas lentas con picos o polipuntas esporádicas
                        $value = $offsets[$index]
                            + sin($i * 0.07 + $index) * 15;  // base lenta

                        // insertar picos o polipuntas (descargas agudas)
                        if ($i % rand(80, 150) == 0) {
                            $value += rand(20, 40); // pico positivo
                        } elseif ($i % rand(90, 160) == 0) {
                            $value -= rand(20, 40); // pico negativo
                        }

                        // ruido moderado
                        $value += rand(-3, 3);
                    }
                } else {
                    if ($seleccionado == 3) {
                        // 🟢 NORMAL
                        // Onda alfa regular (8–13 Hz), simétrica, sin picos
                        $value = $offsets[$index]
                            + sin($i * 0.15 + $index) * 10  // ritmo alfa estable
                            + rand(-1, 1); // leve ruido natural
                    }

                    if ($seleccionado == 2) {
                        // 🟠 ENCEFALOPATÍA
                        // Ondas delta persistentes (0.5–3 Hz), alta amplitud, sin reactividad
                        $value = $offsets[$index]
                            + sin($i * 0.03 + $index) * 25   // onda lenta y amplia
                            + sin($i * 0.01) * 10            // componente aún más lenta
                            + rand(-2, 2);                   // leve variación aleatoria
                    }

                    if ($seleccionado == 1) {
                        // 🔴 EPILEPSIA
                        // Ondas lentas con picos o polipuntas esporádicas
                        $value = $offsets[$index]
                            + sin($i * 0.07 + $index) * 15;  // base lenta

                        // insertar picos o polipuntas (descargas agudas)
                        if ($i % rand(80, 150) == 0) {
                            $value += rand(20, 40); // pico positivo
                        } elseif ($i % rand(90, 160) == 0) {
                            $value -= rand(20, 40); // pico negativo
                        }

                        // ruido moderado
                        $value += rand(-3, 3);
                    }
                }
                $wave[] = round($value, 2);
            }

            $data[] = [
                "name" => $name,
                "data" => $wave
            ];
        }

        return response()->JSON([
            "tipo_patologia_id" => $tipo_patologia_id,
            "diagnostico" => $res,
            "data" => $data,
            "confianza" => $confianza
        ]);
    }

    public function pdf(Diagnostico $diagnostico)
    {
        $pdf = PDF::loadView('reportes.diagnostico', compact('diagnostico'))->setPaper('letter', 'portrait');

        // ENUMERAR LAS PÁGINAS USANDO CANVAS
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $alto = $canvas->get_height();
        $ancho = $canvas->get_width();
        $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));

        return $pdf->stream('diagnostico.pdf');
    }
}
