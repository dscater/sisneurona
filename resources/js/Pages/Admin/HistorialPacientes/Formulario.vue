<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import { useHistorialPacientes } from "@/composables/historial_pacientes/useHistorialPacientes";
import { useAxios } from "@/composables/axios/useAxios";
import { watch, ref, computed, defineEmits, onMounted, nextTick } from "vue";
import MiDropZone from "@/Components/MiDropZone.vue";
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

const { oHistorialPaciente, limpiarHistorialPaciente } =
    useHistorialPacientes();
const { axiosGet } = useAxios();
const accion = ref(props.accion_dialog);
const dialog = ref(props.open_dialog);
let form = useForm(oHistorialPaciente.value);
watch(
    () => props.open_dialog,
    async (newValue) => {
        dialog.value = newValue;
        if (dialog.value) {
            cargarListas();
            document
                .getElementsByTagName("body")[0]
                .classList.add("modal-open");
            form = useForm(oHistorialPaciente.value);
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

const detectaArchivos = (files) => {
    form.historial_archivos = files;
};

const detectaEliminados = (eliminados) => {
    form.eliminados_archivos = eliminados;
};

const tituloDialog = computed(() => {
    return accion.value == 0
        ? `<i class="fa fa-plus"></i> Nuevo Historial de Paciente`
        : `<i class="fa fa-edit"></i> Editar Historial de Paciente`;
});

const enviarFormulario = () => {
    let url =
        form["_method"] == "POST"
            ? route("historial_pacientes.store")
            : route("historial_pacientes.update", form.id);

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
            limpiarHistorialPaciente();
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
};

const cargarPacientes = async () => {
    const data = await axiosGet(route("pacientes.listado"));
    listPacientes.value = data.pacientes;
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
                            <div class="col-md-6 mt-2">
                                <label class="required"
                                    >Motivo de Consulta</label
                                >
                                <el-input
                                    type="textarea"
                                    :class="{
                                        'parsley-error':
                                            form.errors?.motivo_consulta,
                                    }"
                                    v-model="form.motivo_consulta"
                                    autosize
                                >
                                </el-input>
                                <ul
                                    v-if="form.errors?.motivo_consulta"
                                    class="parsley-errors-list filled"
                                >
                                    <li class="parsley-required">
                                        {{ form.errors?.motivo_consulta }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6 mt-2">
                                <label class="required"
                                    >Historia de la enfermedad actual</label
                                >
                                <el-input
                                    type="textarea"
                                    :class="{
                                        'parsley-error':
                                            form.errors?.historial_enfermedad,
                                    }"
                                    v-model="form.historial_enfermedad"
                                    autosize
                                >
                                </el-input>
                                <ul
                                    v-if="form.errors?.historial_enfermedad"
                                    class="parsley-errors-list filled"
                                >
                                    <li class="parsley-required">
                                        {{ form.errors?.historial_enfermedad }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6 mt-2">
                                <label class="required"
                                    >Antecedentes Patológicas Personales</label
                                >
                                <el-input
                                    type="textarea"
                                    :class="{
                                        'parsley-error':
                                            form.errors
                                                ?.antecedentes_personales,
                                    }"
                                    v-model="form.antecedentes_personales"
                                    autosize
                                >
                                </el-input>
                                <ul
                                    v-if="form.errors?.antecedentes_personales"
                                    class="parsley-errors-list filled"
                                >
                                    <li class="parsley-required">
                                        {{
                                            form.errors?.antecedentes_personales
                                        }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6 mt-2">
                                <label class="required"
                                    >Antecedentes Patológicos Familiares</label
                                >
                                <el-input
                                    type="textarea"
                                    :class="{
                                        'parsley-error':
                                            form.errors
                                                ?.antecedentes_familiares,
                                    }"
                                    v-model="form.antecedentes_familiares"
                                    autosize
                                >
                                </el-input>
                                <ul
                                    v-if="form.errors?.antecedentes_familiares"
                                    class="parsley-errors-list filled"
                                >
                                    <li class="parsley-required">
                                        {{
                                            form.errors?.antecedentes_familiares
                                        }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6 mt-2">
                                <label class="required"
                                    >Antecedentes No Patológicas
                                    Personales</label
                                >
                                <el-input
                                    type="textarea"
                                    :class="{
                                        'parsley-error':
                                            form.errors
                                                ?.antecedentes_no_personales,
                                    }"
                                    v-model="form.antecedentes_no_personales"
                                    autosize
                                >
                                </el-input>
                                <ul
                                    v-if="
                                        form.errors?.antecedentes_no_personales
                                    "
                                    class="parsley-errors-list filled"
                                >
                                    <li class="parsley-required">
                                        {{
                                            form.errors
                                                ?.antecedentes_no_personales
                                        }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6 mt-2">
                                <label class="required"
                                    >Exámenes Neurológicos</label
                                >
                                <el-input
                                    type="textarea"
                                    :class="{
                                        'parsley-error':
                                            form.errors?.examenes_neurologicos,
                                    }"
                                    v-model="form.examenes_neurologicos"
                                    autosize
                                >
                                </el-input>
                                <ul
                                    v-if="form.errors?.examenes_neurologicos"
                                    class="parsley-errors-list filled"
                                >
                                    <li class="parsley-required">
                                        {{ form.errors?.examenes_neurologicos }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6 mt-2">
                                <label class="required">Tratamientos</label>
                                <el-input
                                    type="textarea"
                                    :class="{
                                        'parsley-error':
                                            form.errors?.tratamientos,
                                    }"
                                    v-model="form.tratamientos"
                                    autosize
                                >
                                </el-input>
                                <ul
                                    v-if="form.errors?.tratamientos"
                                    class="parsley-errors-list filled"
                                >
                                    <li class="parsley-required">
                                        {{ form.errors?.tratamientos }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6 mt-2">
                                <label class="required">Evoluciones</label>
                                <el-input
                                    type="textarea"
                                    :class="{
                                        'parsley-error':
                                            form.errors?.evoluciones,
                                    }"
                                    v-model="form.evoluciones"
                                    autosize
                                >
                                </el-input>
                                <ul
                                    v-if="form.errors?.evoluciones"
                                    class="parsley-errors-list filled"
                                >
                                    <li class="parsley-required">
                                        {{ form.errors?.evoluciones }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6 mt-2">
                                <label class="required"
                                    >Consultas de Seguimientos</label
                                >
                                <el-input
                                    type="textarea"
                                    :class="{
                                        'parsley-error': form.errors?.consultas,
                                    }"
                                    v-model="form.consultas"
                                    autosize
                                >
                                </el-input>
                                <ul
                                    v-if="form.errors?.consultas"
                                    class="parsley-errors-list filled"
                                >
                                    <li class="parsley-required">
                                        {{ form.errors?.consultas }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-12 mt-3">
                                <label class="label required"
                                    >Cargar archivos PDF</label
                                >
                                <div class="text-muted">
                                    Selecciona al menos un archivo
                                </div>
                                <MiDropZone
                                    :files="form.historial_archivos"
                                    @UpdateFiles="detectaArchivos"
                                    @addEliminados="detectaEliminados"
                                ></MiDropZone>
                                <ul
                                    v-if="form.errors?.historial_archivos"
                                    class="parsley-errors-list filled"
                                >
                                    <li class="parsley-required">
                                        {{ form.errors?.historial_archivos }}
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
                    >
                        <i class="fa fa-save"></i>
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
