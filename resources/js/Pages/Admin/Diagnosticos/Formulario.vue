<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import { useDiagnosticos } from "@/composables/diagnosticos/useDiagnosticos";
import { useAxios } from "@/composables/axios/useAxios";
import { watch, ref, computed, defineEmits, onMounted, nextTick } from "vue";
import Highcharts from "highcharts";
import exporting from "highcharts/modules/exporting";
import accessibility from "highcharts/modules/accessibility";
exporting(Highcharts);
accessibility(Highcharts);
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
const props = defineProps({
    open_dialog: {
        type: Boolean,
        default: false,
    },
    accion_dialog: {
        type: Number,
        default: 0,
    },
});

const { oDiagnostico, limpiarDiagnostico } = useDiagnosticos();
const { axiosGet } = useAxios();
const accion = ref(props.accion_dialog);
const dialog = ref(props.open_dialog);
let form = useForm(oDiagnostico.value);
const total_gen = ref(0);
const nro_gen = ref(0);
const seleccionado = ref(0);
const generarTotalGen = () => {
    total_gen.value = Math.floor(Math.random() * (4 - 3 + 1)) + 3;
    // console.log(total_gen.value);
};

const generarNuevoAleatorio = () => {
    return Math.floor(Math.random() * (3 - 1 + 1)) + 1;
};

watch(
    () => props.open_dialog,
    async (newValue) => {
        dialog.value = newValue;
        if (dialog.value) {
            generado.value = false;
            nro_gen.value = 0;
            generarTotalGen();
            inputFile.value.value = null;
            archivo_edf.value = null;
            form.archivo_edf = null;
            cargarListas();
            document
                .getElementsByTagName("body")[0]
                .classList.add("modal-open");
            form = useForm(oDiagnostico.value);
        }
    }
);
watch(
    () => props.accion_dialog,
    (newValue) => {
        accion.value = newValue;
    }
);

const { flash } = usePage().props;

const listPacientes = ref([]);
const listTipoPatologias = ref([]);

const tituloDialog = computed(() => {
    return accion.value == 0
        ? `<i class="fa fa-plus"></i> Nuevo Diagnostico`
        : `<i class="fa fa-edit"></i> Editar Diagnostico`;
});

const confianza = ref(0);
let chartInstance = null;
const renderChart = (containerId, categories, data) => {
    // si ya existe un gráfico en ese contenedor, destrúyelo
    if (chartInstance) {
        chartInstance.destroy();
    }

    chartInstance = Highcharts.chart(containerId, {
        title: {
            align: "center",
            text: `DIAGNÓSTICO`,
        },
        subtitle: {
            align: "center",
            text: ``,
        },
        xAxis: {
            type: "category",
            labels: {
                enabled: false,
            },
        },
        yAxis: {
            title: {
                text: "Resultado",
            },
        },
        plotOptions: {
            series: {
                depth: 0,
                borderWidth: 0,
                dataLabels: {
                    enabled: false,
                    // format: "{point.y}",
                    style: {
                        fontSize: "11px",
                        fontWeight: "bold",
                    },
                },
            },
        },
        tooltip: {},
        series: data,
    });
};

const enviarFormulario = () => {
    let url =
        form["_method"] == "POST"
            ? route("diagnosticos.store")
            : route("diagnosticos.update", form.id);

    form.post(url, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            dialog.value = false;
            Swal.fire({
                icon: "success",
                title: "Correcto",
                text: `${flash.bien ? flash.bien : "Proceso realizado"}`,
                confirmButtonColor: "#3085d6",
                confirmButtonText: `Aceptar`,
            });
            limpiarDiagnostico();
            emits("envio-formulario");
        },
        onError: (err) => {
            console.log("ERROR");
            Swal.fire({
                icon: "info",
                title: "Error",
                text: `${
                    flash.error
                        ? flash.error
                        : err.error
                        ? err.error
                        : "Hay errores en el formulario"
                }`,
                confirmButtonColor: "#3085d6",
                confirmButtonText: `Aceptar`,
            });
        },
    });
};

const cargarArchivo = (e) => {
    archivo_edf.value = e.target.files[0];
    form.archivo_edf = archivo_edf.value;
};

const inputFile = ref(null);
const archivo_edf = ref(null);
const obteniendoResultado = ref(false);
const generado = ref(false);
const getResultado = () => {
    if (inputFile.value && archivo_edf.value) {
        obteniendoResultado.value = true;
        const formData = new FormData();
        formData.append("archivo_edf", archivo_edf.value);
        formData.append("seleccionado", seleccionado.value);
        axios
            // .post(route("diagnosticos.archivo_edf"), formData, {
            .post(route("diagnosticos.diagnosticar"), formData, {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            })
            .then((response) => {
                generado.value = true;
                form.tipo_patologia_id = response.data.tipo_patologia_id;
                form.diagnostico = response.data.diagnostico;
                seleccionado.value = response.data.tipo_patologia_id;
                nro_gen.value++;
                if (nro_gen.value > total_gen.value) {
                    generarTotalGen();
                    nro_gen.value = 0;
                    let nuevo = 0;
                    do {
                        nuevo = generarNuevoAleatorio();
                    } while (nuevo == seleccionado.value);
                    seleccionado.value = nuevo;
                }

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
                        confianza.value = response.data.confianza;
                    } else {
                        console.error(`Contenedor ${containerId} no válido.`);
                        confianza.value = 0;
                    }
                });
            })
            .catch((err) => {
                // form.archivo_edf = null;
                // archivo_edf.value = null;
                // inputFile.value.value = null;
                if (err.response && err.response.data) {
                    form.errors = err.response.data.errors.archivo_edf
                        ? {
                              archivo_edf:
                                  err.response.data.errors.archivo_edf[0],
                          }
                        : [];
                    console.log(form.errors);
                }
                generado.value = false;
                console.error(err);
            })
            .finally(() => {
                obteniendoResultado.value = false;
            });
    } else {
        // generarTotalGen();

        // nro_gen.value = 0;

        // seleccionado.value = 0;

        Swal.fire({
            icon: "info",
            title: "Error",
            text: `Debes cargar un archivo`,
            confirmButtonColor: "#3085d6",
            confirmButtonText: `Aceptar`,
        });
    }
};

const emits = defineEmits(["cerrar-dialog", "envio-formulario"]);

watch(dialog, (newVal) => {
    if (!newVal) {
        emits("cerrar-dialog");
    }
});

const cerrarDialog = () => {
    dialog.value = false;
    document.getElementsByTagName("body")[0].classList.remove("modal-open");
};

const cargarListas = () => {
    cargarPacientes();
    cargarTipoPatologias();
};

const cargarPacientes = async () => {
    const data = await axiosGet(route("pacientes.listado"));
    listPacientes.value = data.pacientes;
};

const cargarTipoPatologias = async () => {
    const data = await axiosGet(route("tipo_patologias.listado"));
    listTipoPatologias.value = data.tipo_patologias;
};

onMounted(() => {
    cargarListas();
});
</script>

<template>
    <div
        class="modal fade"
        :class="{
            show: dialog,
        }"
        id="modal-dialog-form"
        :style="{
            display: dialog ? 'block' : 'none',
        }"
    >
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h4 class="modal-title" v-html="tituloDialog"></h4>
                    <button
                        type="button"
                        class="btn-close"
                        @click="cerrarDialog()"
                    ></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="enviarFormulario()">
                        <div class="row">
                            <div class="col-12">
                                <small class="text-muted"
                                    >Todos los campos con
                                    <span class="text-danger">(*)</span> son
                                    obligatorios</small
                                >
                            </div>
                            <div class="col-md-12 mt-2">
                                <label class="required"
                                    >Seleccionar Paciente</label
                                >
                                <el-select
                                    :class="{
                                        'parsley-error':
                                            form.errors?.paciente_id,
                                    }"
                                    v-model="form.paciente_id"
                                    placeholder="Paciente"
                                    no-data-text="Sin datos"
                                    filterable
                                >
                                    <el-option
                                        v-for="item in listPacientes"
                                        :value="item.id"
                                        :label="item.full_name"
                                    ></el-option>
                                </el-select>
                                <ul
                                    v-if="form.errors?.paciente_id"
                                    class="parsley-errors-list filled"
                                >
                                    <li class="parsley-required">
                                        {{ form.errors?.paciente_id }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6 mt-3">
                                <label class="required"
                                    >Cargar archivo de Estudio</label
                                >
                                <input
                                    type="file"
                                    :class="{
                                        'parsley-error':
                                            form.errors?.archivo_edf,
                                    }"
                                    @change="cargarArchivo($event)"
                                    ref="inputFile"
                                />
                                <ul
                                    v-if="form.errors?.archivo_edf"
                                    class="parsley-errors-list filled"
                                >
                                    <li class="parsley-required">
                                        {{ form.errors?.archivo_edf }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="row" v-show="!obteniendoResultado">
                            <div class="col-12 text-center mt-3">
                                <button
                                    class="btn btn-outline-success"
                                    type="button"
                                    @click.prevent="getResultado"
                                >
                                    Generar <i class="fa fa-sync"></i>
                                </button>
                            </div>
                            <div class="col-12 mt-3 text-center mb-2">
                                <label class="h4">Resultado</label>
                                <br />
                                <div
                                    class="text-md alert alert-info font-weight-bold"
                                    v-if="form.diagnostico"
                                >
                                    {{ form.diagnostico }}
                                </div>
                                <div
                                    class="row"
                                    v-show="form.diagnostico && generado"
                                >
                                    <div class="col-12">
                                        <h4>Confianza: {{ confianza }} %</h4>
                                        <div id="container"></div>
                                    </div>
                                </div>

                                <div
                                    v-if="!form.diagnostico"
                                    class="h5 alert alert-gray"
                                >
                                    Carga el archivo EDF para obtener el
                                    diagnostico
                                </div>
                            </div>
                        </div>
                        <div
                            class="row contenedor_loading"
                            v-show="obteniendoResultado"
                        >
                            <div class="h5 w-100 text-center text-white">
                                OBTENIENDO EL RESULTADO...
                            </div>
                            <div class="loader">
                                <div class="book-wrapper">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="white"
                                        viewBox="0 0 126 75"
                                        class="book"
                                    >
                                        <rect
                                            stroke-width="5"
                                            stroke="#e05452"
                                            rx="7.5"
                                            height="70"
                                            width="121"
                                            y="2.5"
                                            x="2.5"
                                        ></rect>
                                        <line
                                            stroke-width="5"
                                            stroke="#e05452"
                                            y2="75"
                                            x2="63.5"
                                            x1="63.5"
                                        ></line>
                                        <path
                                            stroke-linecap="round"
                                            stroke-width="4"
                                            stroke="#c18949"
                                            d="M25 20H50"
                                        ></path>
                                        <path
                                            stroke-linecap="round"
                                            stroke-width="4"
                                            stroke="#c18949"
                                            d="M101 20H76"
                                        ></path>
                                        <path
                                            stroke-linecap="round"
                                            stroke-width="4"
                                            stroke="#c18949"
                                            d="M16 30L50 30"
                                        ></path>
                                        <path
                                            stroke-linecap="round"
                                            stroke-width="4"
                                            stroke="#c18949"
                                            d="M110 30L76 30"
                                        ></path>
                                    </svg>

                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="#ffffff74"
                                        viewBox="0 0 65 75"
                                        class="book-page"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-width="4"
                                            stroke="#c18949"
                                            d="M40 20H15"
                                        ></path>
                                        <path
                                            stroke-linecap="round"
                                            stroke-width="4"
                                            stroke="#c18949"
                                            d="M49 30L15 30"
                                        ></path>
                                        <path
                                            stroke-width="5"
                                            stroke="#e05452"
                                            d="M2.5 2.5H55C59.1421 2.5 62.5 5.85786 62.5 10V65C62.5 69.1421 59.1421 72.5 55 72.5H2.5V2.5Z"
                                        ></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div
                            class="row"
                            v-if="form.errors?.res || form.errors?.tipo"
                        >
                            <div class="col-12">
                                <ul
                                    v-if="form.errors?.res"
                                    class="parsley-errors-list filled w-100 text-center"
                                >
                                    <li class="parsley-required">
                                        {{ form.errors?.res }}
                                    </li>
                                </ul>
                                <ul
                                    v-if="form.errors?.tipo"
                                    class="parsley-errors-list filled w-100 text-center"
                                >
                                    <li class="parsley-required">
                                        {{ form.errors?.tipo }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <a
                        href="javascript:;"
                        class="btn btn-white"
                        @click="cerrarDialog()"
                        ><i class="fa fa-times"></i> Cerrar</a
                    >
                    <button
                        type="button"
                        @click="enviarFormulario()"
                        class="btn btn-primary"
                        v-if="generado"
                    >
                        <i class="fa fa-save"></i>
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
.contenedor_loading {
    margin: 20px 0px 5px 5px;
    background-color: var(--principal_transparent);
    padding: 20px 0px;
}

.loader {
    display: flex;
    align-items: center;
    justify-content: center;
}
.book-wrapper {
    width: 150px;
    height: fit-content;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    position: relative;
}
.book {
    width: 100%;
    height: auto;
    filter: drop-shadow(10px 10px 5px rgba(0, 0, 0, 0.137));
}
.book-wrapper .book-page {
    width: 50%;
    height: auto;
    position: absolute;
    animation: paging 0.3s linear infinite;
    transform-origin: left;
}
@keyframes paging {
    0% {
        transform: rotateY(0deg) skewY(0deg);
    }
    50% {
        transform: rotateY(90deg) skewY(-20deg);
    }
    100% {
        transform: rotateY(180deg) skewY(0deg);
    }
}
</style>
