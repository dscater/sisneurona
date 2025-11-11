<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Diagnosticos</title>
    <style type="text/css">
        * {
            font-family: sans-serif;
        }

        @page {
            margin-top: 1.5cm;
            margin-bottom: 0.3cm;
            margin-left: 0.3cm;
            margin-right: 0.3cm;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 20px;
            page-break-before: avoid;
        }

        table thead tr th,
        tbody tr td {
            padding: 3px;
            word-wrap: break-word;
        }

        table thead tr th {
            font-size: 9pt;
        }

        table tbody tr td {
            font-size: 10pt;
        }


        .encabezado {
            width: 100%;
        }

        .logo img {
            position: absolute;
            height: 70px;
            top: -20px;
            left: 0px;
        }

        h2.titulo {
            width: 450px;
            margin: auto;
            margin-top: 0PX;
            margin-bottom: 15px;
            text-align: center;
            font-size: 14pt;
        }

        .texto {
            width: 250px;
            text-align: center;
            margin: auto;
            margin-top: 15px;
            font-weight: bold;
            font-size: 1.1em;
        }

        .fecha {
            width: 250px;
            text-align: center;
            margin: auto;
            margin-top: 15px;
            font-weight: normal;
            font-size: 0.85em;
        }

        .total {
            text-align: right;
            padding-right: 15px;
            font-weight: bold;
        }

        table {
            width: 100%;
        }

        table thead {
            background: rgb(236, 236, 236)
        }

        tr {
            page-break-inside: avoid !important;
        }

        .centreado {
            padding-left: 0px;
            text-align: center;
        }

        .datos {
            margin-left: 15px;
            border-top: solid 1px;
            border-collapse: collapse;
            width: 250px;
        }

        .txt {
            font-weight: bold;
            text-align: right;
            padding-right: 5px;
        }

        .txt_center {
            font-weight: bold;
            text-align: center;
        }

        .cumplimiento {
            position: absolute;
            width: 150px;
            right: 0px;
            top: 86px;
        }

        .b_top {
            border-top: solid 1px black;
        }

        .gray {
            background: rgb(202, 202, 202);
        }

        .bg-principal {
            background: #153f59;
            color: white;
        }

        .bold {
            font-weight: bold;
        }

        .derecha {
            text-align: right;
        }

        .img_celda img {
            width: 45px;
        }

        .crema {
            background: rgb(255, 252, 213);
        }
    </style>
</head>

<body>
    @inject('configuracion', 'App\Models\Configuracion')
    <div class="encabezado">
        <div class="logo">
            <img src="{{ $configuracion->first()->logo_b64 }}">
        </div>
        <h2 class="titulo">
            {{ $configuracion->first()->nombre_sistema }}
        </h2>
        <h4 class="texto">REPORTE DE DIAGNÓSTICO</h4>
        <h4 class="fecha">Expedido: {{ date('d-m-Y') }}</h4>
    </div>

    <table style="border-collapse: separate; border-spacing:10px 10px;">
        <tbody>
            <tr>
                <td width="45%" class="derecha bold">Nombre(s):</td>
                <td>{{ $diagnostico->paciente->nombre }}</td>
            </tr>
            <tr>
                <td class="derecha bold">Apellidos:</td>
                <td>{{ $diagnostico->paciente->paterno }} {{ $diagnostico->paciente->matenro }}</td>
            </tr>
            <tr>
                <td class="derecha bold">Nro. de C.I.:</td>
                <td>{{ $diagnostico->paciente->full_ci }}</td>
            </tr>
            <tr>
                <td class="derecha bold">Fecha de Nacimiento:</td>
                <td>{{ $diagnostico->paciente->fecha_nac_t }}</td>
            </tr>
            <tr>
                <td class="derecha bold">Género:</td>
                <td>{{ $diagnostico->paciente->genero }}</td>
            </tr>
            <tr>
                <td class="derecha bold">Resultado del diagnostico:</td>
                <td>{{ $diagnostico->diagnostico }}</td>
            </tr>
            <tr>
                <td class="derecha bold">Fecha:</td>
                <td>{{ $diagnostico->fecha_registro_t }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
