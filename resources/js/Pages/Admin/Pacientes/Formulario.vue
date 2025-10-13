<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import { usePacientes } from "@/composables/pacientes/usePacientes";
import { useAxios } from "@/composables/axios/useAxios";
import { watch, ref, computed, defineEmits, onMounted, nextTick } from "vue";
import axios from "axios";
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

const { oPaciente, limpiarPaciente } = usePacientes();
const { axiosGet } = useAxios();
const accion = ref(props.accion_dialog);
const dialog = ref(props.open_dialog);
let form = useForm(oPaciente.value);
watch(
    () => props.open_dialog,
    async (newValue) => {
        dialog.value = newValue;
        if (dialog.value) {
            document
                .getElementsByTagName("body")[0]
                .classList.add("modal-open");
            form = useForm(oPaciente.value);
        }
    }
);
watch(
    () => props.accion_dialog,
    (newValue) => {
        accion.value = newValue;
    }
);

const { flash, auth } = usePage().props;

const tituloDialog = computed(() => {
    return accion.value == 0
        ? `<i class="fa fa-plus"></i> Nuevo Paciente`
        : `<i class="fa fa-edit"></i> Editar Paciente`;
});

const listExpedido = [
    { value: "LP", label: "La Paz" },
    { value: "CB", label: "Cochabamba" },
    { value: "SC", label: "Santa Cruz" },
    { value: "CH", label: "Chuquisaca" },
    { value: "OR", label: "Oruro" },
    { value: "PT", label: "Potosi" },
    { value: "TJ", label: "Tarija" },
    { value: "PD", label: "Pando" },
    { value: "BN", label: "Beni" },
];

const listGenero = [
    { value: "HOMBRE", label: "HOMBRE" },
    { value: "MUJER", label: "MUJER" },
];

const listEstadoCivil = [
    { value: "SOLTERO", label: "SOLTERO" },
    { value: "CASADO", label: "CASADO" },
    { value: "DIVORCIADO", label: "DIVORCIADO" },
    { value: "CONCUBINATO", label: "CONCUBINATO" },
    { value: "VIUDO", label: "VIUDO" },
];

const enviarFormulario = () => {
    let url =
        form["_method"] == "POST"
            ? route("pacientes.store")
            : route("pacientes.update", form.id);

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
            limpiarPaciente();
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

onMounted(() => {});
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
                            <div class="col-md-4 mt-2">
                                <label class="required">Nombre(s)</label>
                                <el-input
                                    type="text"
                                    :class="{
                                        'parsley-error': form.errors?.nombre,
                                    }"
                                    v-model="form.nombre"
                                    autosize
                                ></el-input>
                                <ul
                                    v-if="form.errors?.nombre"
                                    class="parsley-errors-list filled"
                                >
                                    <li class="parsley-required">
                                        {{ form.errors?.nombre }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-4 mt-2">
                                <label class="required">Apellido Paterno</label>
                                <el-input
                                    type="text"
                                    :class="{
                                        'parsley-error': form.errors?.paterno,
                                    }"
                                    v-model="form.paterno"
                                    autosize
                                ></el-input>
                                <ul
                                    v-if="form.errors?.paterno"
                                    class="parsley-errors-list filled"
                                >
                                    <li class="parsley-required">
                                        {{ form.errors?.paterno }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-4 mt-2">
                                <label class="">Apellido Materno</label>
                                <el-input
                                    type="text"
                                    :class="{
                                        'parsley-error': form.errors?.materno,
                                    }"
                                    v-model="form.materno"
                                    autosize
                                ></el-input>
                                <ul
                                    v-if="form.errors?.materno"
                                    class="parsley-errors-list filled"
                                >
                                    <li class="parsley-required">
                                        {{ form.errors?.materno }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-4 mt-2">
                                <label class="required">Número C.I.</label>
                                <el-input
                                    type="text"
                                    :class="{
                                        'parsley-error': form.errors?.ci,
                                    }"
                                    v-model="form.ci"
                                    autosize
                                ></el-input>
                                <ul
                                    v-if="form.errors?.ci"
                                    class="parsley-errors-list filled"
                                >
                                    <li class="parsley-required">
                                        {{ form.errors?.ci }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-4 mt-2">
                                <label class="required">Extensión C.I.</label>
                                <el-select
                                    :class="{
                                        'parsley-error': form.errors?.ci_exp,
                                    }"
                                    placeholder="Seleccione"
                                    v-model="form.ci_exp"
                                >
                                    <el-option
                                        v-for="item in listExpedido"
                                        :value="item.value"
                                        :label="item.label"
                                    >
                                    </el-option>
                                </el-select>
                                <ul
                                    v-if="form.errors?.ci_exp"
                                    class="parsley-errors-list filled"
                                >
                                    <li class="parsley-required">
                                        {{ form.errors?.ci_exp }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-4 mt-2">
                                <label class="required"
                                    >Fecha de Nacimiento</label
                                >
                                <input
                                    type="date"
                                    class="form-control"
                                    :class="{
                                        'parsley-error': form.errors?.fecha_nac,
                                    }"
                                    v-model="form.fecha_nac"
                                    autosize
                                />
                                <ul
                                    v-if="form.errors?.fecha_nac"
                                    class="parsley-errors-list filled"
                                >
                                    <li class="parsley-required">
                                        {{ form.errors?.fecha_nac }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-4 mt-2">
                                <label class="required">Género</label>
                                <el-select
                                    :class="{
                                        'parsley-error': form.errors?.genero,
                                    }"
                                    placeholder="Seleccione"
                                    v-model="form.genero"
                                >
                                    <el-option
                                        v-for="item in listGenero"
                                        :value="item.value"
                                        :label="item.label"
                                    >
                                    </el-option>
                                </el-select>
                                <ul
                                    v-if="form.errors?.genero"
                                    class="parsley-errors-list filled"
                                >
                                    <li class="parsley-required">
                                        {{ form.errors?.genero }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-4 mt-2">
                                <label class="required">Celular</label>
                                <el-input
                                    type="text"
                                    :class="{
                                        'parsley-error': form.errors?.cel,
                                    }"
                                    v-model="form.cel"
                                    autosize
                                ></el-input>
                                <ul
                                    v-if="form.errors?.cel"
                                    class="parsley-errors-list filled"
                                >
                                    <li class="parsley-required">
                                        {{ form.errors?.cel }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-4 mt-2">
                                <label class="">Dirección</label>
                                <el-input
                                    type="text"
                                    :class="{
                                        'parsley-error': form.errors?.dir,
                                    }"
                                    v-model="form.dir"
                                    autosize
                                ></el-input>
                                <ul
                                    v-if="form.errors?.dir"
                                    class="parsley-errors-list filled"
                                >
                                    <li class="parsley-required">
                                        {{ form.errors?.dir }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-4 mt-2">
                                <label class="">Ocupación Actual</label>
                                <el-input
                                    type="text"
                                    :class="{
                                        'parsley-error': form.errors?.ocupacion,
                                    }"
                                    v-model="form.ocupacion"
                                    autosize
                                ></el-input>
                                <ul
                                    v-if="form.errors?.ocupacion"
                                    class="parsley-errors-list filled"
                                >
                                    <li class="parsley-required">
                                        {{ form.errors?.ocupacion }}
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
