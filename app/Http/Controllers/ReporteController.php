<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Diagnostico;
use App\Models\HistorialOferta;
use App\Models\HistorialPaciente;
use App\Models\Paciente;
use App\Models\Publicacion;
use App\Models\PublicacionDetalle;
use App\Models\SubastaCliente;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use PDF;
use Carbon\Carbon;

class ReporteController extends Controller
{
    public function usuarios()
    {
        return Inertia::render("Admin/Reportes/Usuarios");
    }

    public function r_usuarios(Request $request)
    {
        $tipo =  $request->tipo;
        $usuarios = User::select("users.*")
            ->where('id', '!=', 1);

        if ($tipo != 'todos') {
            $request->validate([
                'tipo' => 'required',
            ]);
            $usuarios->where('tipo', $tipo);
        }

        $usuarios = $usuarios->orderBy("paterno", "ASC")->get();

        $pdf = PDF::loadView('reportes.usuarios', compact('usuarios'))->setPaper('legal', 'landscape');

        // ENUMERAR LAS PÁGINAS USANDO CANVAS
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $alto = $canvas->get_height();
        $ancho = $canvas->get_width();
        $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));

        return $pdf->stream('usuarios.pdf');
    }

    public function pacientes()
    {
        return Inertia::render("Admin/Reportes/Pacientes");
    }
    public function r_pacientes(Request $request)
    {
        $fecha_ini =  $request->fecha_ini;
        $fecha_fin =  $request->fecha_fin;
        $pacientes = Paciente::select("pacientes.*");

        if ($fecha_ini && $fecha_fin) {
            $pacientes->whereBetween('fecha_registro', [$fecha_ini, $fecha_fin]);
        }

        $pacientes = $pacientes->where("status", 1)->get();

        $pdf = PDF::loadView('reportes.pacientes', compact('pacientes'))->setPaper('letter', 'landscape');

        // ENUMERAR LAS PÁGINAS USANDO CANVAS
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $alto = $canvas->get_height();
        $ancho = $canvas->get_width();
        $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));

        return $pdf->stream('pacientes.pdf');
    }

    public function historial()
    {
        return Inertia::render("Admin/Reportes/Historial");
    }
    public function r_historial(Request $request)
    {
        $paciente_id =  $request->paciente_id;
        $fecha_ini =  $request->fecha_ini;
        $fecha_fin =  $request->fecha_fin;
        $historial_pacientes = HistorialPaciente::select("historial_pacientes.*");

        if ($paciente_id != 'todos') {
            $historial_pacientes->where('paciente_id', $paciente_id);
        }
        if ($fecha_ini && $fecha_fin) {
            $historial_pacientes->whereBetween('fecha_registro', [$fecha_ini, $fecha_fin]);
        }

        $historial_pacientes = $historial_pacientes->get();

        $pdf = PDF::loadView('reportes.historial_pacientes', compact('historial_pacientes'))->setPaper('legal', 'landscape');

        // ENUMERAR LAS PÁGINAS USANDO CANVAS
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $alto = $canvas->get_height();
        $ancho = $canvas->get_width();
        $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));

        return $pdf->stream('historial_pacientes.pdf');
    }

    public function diagnosticos()
    {
        return Inertia::render("Admin/Reportes/Diagnosticos");
    }
    public function r_diagnosticos(Request $request)
    {
        $paciente_id =  $request->paciente_id;
        $tipo_patologia_id =  $request->tipo_patologia_id;
        $fecha_ini =  $request->fecha_ini;
        $fecha_fin =  $request->fecha_fin;
        $diagnosticos = Diagnostico::select("diagnosticos.*");

        if ($paciente_id != 'todos') {
            $diagnosticos->where('paciente_id', $paciente_id);
        }
        if ($tipo_patologia_id != 'todos') {
            $diagnosticos->where('tipo_patologia_id', $tipo_patologia_id);
        }
        if ($fecha_ini && $fecha_fin) {
            $diagnosticos->whereBetween('fecha_registro', [$fecha_ini, $fecha_fin]);
        }

        $diagnosticos = $diagnosticos->get();

        $pdf = PDF::loadView('reportes.diagnosticos', compact('diagnosticos'))->setPaper('letter', 'portrait');

        // ENUMERAR LAS PÁGINAS USANDO CANVAS
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $alto = $canvas->get_height();
        $ancho = $canvas->get_width();
        $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));

        return $pdf->stream('diagnosticos.pdf');
    }

    public function gdiagnosticos()
    {
        return Inertia::render("Admin/Reportes/GDiagnosticos");
    }

    public function r_gdiagnosticos(Request $request)
    {
        $paciente_id =  $request->paciente_id;
        $tipo_patologia_id =  $request->tipo_patologia_id;
        $fecha_ini =  $request->fecha_ini;
        $fecha_fin =  $request->fecha_fin;


        $tipos = ["EPILEPSIA", "ENCEFALOPATIAS", "NORMAL"];
        $listTipos =  [
            1 => "EPILEPSIA",
            2 => "ENCEFALOPATIAS",
            3 => "NORMAL"
        ];
        if ($tipo_patologia_id != 'todos') {
            $tipos = [$listTipos[$tipo_patologia_id]];
        }

        $colores = [
            "EPILEPSIA" => "#28a745",   // verde
            "ENCEFALOPATIAS" => "#ffc107",  // amarillo
            "NORMAL" => "#fd7e14",   // naranja
        ];

        $data = [];
        foreach ($tipos as $key => $tipo) {
            $diagnosticos = Diagnostico::where("status", 1);
            if ($fecha_ini && $fecha_fin) {
                $diagnosticos->whereBetween("fecha_registro", [$fecha_ini, $fecha_fin]);
            }
            if ($paciente_id != 'todos') {
                $diagnosticos->where("paciente_id", $paciente_id);
            }

            $diagnosticos = $diagnosticos->where("diagnostico", $tipo)->count();

            $data[] = [
                'name' => $tipo,
                'y' => (float) $diagnosticos,
                'color' => $colores[$tipo] ?? '#000000'
            ];
        }

        return response()->JSON([
            "categories" => $tipos,
            "data" => $data,
        ]);
    }
}
