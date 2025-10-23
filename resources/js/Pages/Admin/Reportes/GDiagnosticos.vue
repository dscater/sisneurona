<script setup>
import { useApp } from "@/composables/useApp";
import { computed, onMounted, ref, nextTick } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import Highcharts from "highcharts";
import exporting from "highcharts/modules/exporting";
import accessibility from "highcharts/modules/accessibility";
import Highcharts3D from "highcharts/highcharts-3d";
import cylinder from "highcharts/modules/cylinder";
import { useFormater } from "@/composables/useFormater";
const { getFormatoMoneda } = useFormater();
const { auth } = usePage().props;
const user = ref(auth.user);
exporting(Highcharts);
accessibility(Highcharts);
Highcharts3D(Highcharts);
cylinder(Highcharts);
Highcharts.setOptions({
    lang: {
        downloadPNG: "Descargar PNG",
        downloadJPEG: "Descargar JPEG",
        downloadPDF: "Descargar PDF",
        downloadSVG: "Descargar SVG",
        printChart: "Imprimir gráfico",
        contextButtonTitle: "Menú de exportación",
        viewFullscreen: "Pantalla completa",
        exitFullscreen: "Salir de pantalla completa",
    },
});
const { setLoading } = useApp();
const generando = ref(false);
const txtBtn = computed(() => {
    if (generando.value) {
        return "Generando Grafico...";
    }
    return "Generar Grafico";
});

const listTipoPatologias = ref([
    {
        id: "todos",
        nombre: "TODOS",
    },
    {
        id: 1,
        nombre: "EPILEPSIA",
    },
    {
        id: 2,
        nombre: "ENCEFALOPATIAS",
    },
    {
        id: 3,
        nombre: "NORMAL",
    },
]);
const listPacientes = ref([]);

const obtenerFechaActual = () => {
    const fecha = new Date();
    const anio = fecha.getFullYear();
    const mes = String(fecha.getMonth() + 1).padStart(2, "0"); // Mes empieza desde 0
    const dia = String(fecha.getDate()).padStart(2, "0"); // Día del mes
    return `${anio}-${mes}-${dia}`;
};

const form = ref({
    paciente_id: "todos",
    tipo_patologia_id: "todos",
    fecha_ini: "",
    fecha_fin: "",
});

const getPacientes = () => {
    axios.get(route("pacientes.listado")).then((response) => {
        listPacientes.value = response.data.pacientes;
        listPacientes.value.unshift({
            ...{ id: "todos", label: "TODOS", full_name: "TODOS" },
        });
    });
};

const generarGrafico = async () => {
    generando.value = true;
    axios
        .get(route("reportes.r_gdiagnosticos"), {
            params: form.value,
        })
        .then((response) => {
            nextTick(() => {
                const containerId = `container`;
                const container = document.getElementById(containerId);
                // Verificar que el contenedor exista y tenga un tamaño válido
                if (container) {
                    renderChart(
                        containerId,
                        response.data.categories,
                        response.data.data
                    );
                } else {
                    console.error(`Contenedor ${containerId} no válido.`);
                }
            });
            // Create the chart
            generando.value = false;
        });
};

const renderChart = (containerId, categories, data) => {
    const rowHeight = 80;
    const minHeight = 200;
    const calculatedHeight = Math.max(minHeight, categories.length * rowHeight);
    Highcharts.chart(containerId, {
        chart: {
            type: "cylinder",
            options3d: {
                enabled: true,
                alpha: 20,
                beta: 4,
                depth: 40,
                viewDistance: 0,
            },
            height: calculatedHeight,
        },
        title: {
            align: "center",
            text: `REPORTE DIAGNÓSTICOS`,
        },
        subtitle: {
            align: "center",
            text: ``,
        },
        accessibility: {
            announceNewData: {
                enabled: true,
            },
        },
        xAxis: {
            type: "category",
        },
        yAxis: {
            title: {
                text: "TOTAL",
            },
        },
        legend: {
            enabled: true,
        },
        plotOptions: {
            series: {
                depth: 100,
                borderWidth: 0,
                dataLabels: {
                    enabled: true,
                    // format: "{point.y}",
                    style: {
                        fontSize: "11px",
                        fontWeight: "bold",
                    },
                },
            },
        },
        tooltip: {
            useHTML: true,
            formatter: function () {
                return `
                    <div style="text-align:center;">
                        <div style="display:inline-block; width:12px; height:12px; background:${this.point.color}; border-radius:50%; margin-right:5px;"></div>
                        <strong style="color:${this.point.color};">${this.point.name}</strong>
                        <br>
                        <span class="text-md"><strong>Total:</strong> ${this.point.y}</span>
                    </div>
                    `;
            },
        },

        series: [
            {
                name: "Reporte Diagnósticos",
                data: data,
                colorByPoint: true,
            },
        ],
    });
};

onMounted(() => {
    getPacientes();
    setTimeout(() => {
        setLoading(false);
    }, 300);
});
</script>
<template>
    <Head title="Reporte Diagnósticos"></Head>
    <!-- BEGIN breadcrumb -->
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
        <li class="breadcrumb-item active">Gráficas > Reporte Diagnósticos</li>
    </ol>
    <!-- END breadcrumb -->
    <!-- BEGIN page-header -->
    <h1 class="page-header">Gráficas > Reporte Diagnósticos</h1>
    <!-- END page-header -->
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card">
                <div class="card-body">
                    <form @submit.prevent="generarReporte">
                        <div class="row">
                            <div class="col-md-12">
                                <label>Seleccionar Paciente*</label>
                                <el-select v-model="form.paciente_id">
                                    <el-option
                                        v-for="item in listPacientes"
                                        :value="item.id"
                                        :key="item.id"
                                        :label="item.full_name"
                                    >
                                    </el-option>
                                </el-select>
                            </div>
                            <div class="col-md-12 mt-2">
                                <label>Seleccionar Diagnóstico*</label>
                                <el-select v-model="form.tipo_patologia_id">
                                    <el-option
                                        v-for="item in listTipoPatologias"
                                        :value="item.id"
                                        :key="item.id"
                                        :label="item.nombre"
                                    >
                                    </el-option>
                                </el-select>
                            </div>
                            <div class="col-md-12 mt-2">
                                <label>Rango de fechas</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input
                                            type="date"
                                            class="form-control"
                                            v-model="form.fecha_ini"
                                        />
                                    </div>
                                    <div class="col-md-6">
                                        <input
                                            type="date"
                                            class="form-control"
                                            v-model="form.fecha_fin"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 text-center mt-3">
                                <button
                                    class="btn btn-primary"
                                    block
                                    @click="generarGrafico"
                                    :disabled="generando"
                                    v-text="txtBtn"
                                ></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row overflow-auto" style="max-height: 600px">
        <div class="col-12 mt-3" id="container"></div>
    </div>
</template>
