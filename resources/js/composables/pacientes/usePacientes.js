import { onMounted, ref } from "vue";

const oPaciente = ref({
    id: 0,
    nombre: "",
    paterno: "",
    materno: "",
    ci: "",
    ci_exp: "",
    fecha_nac: "",
    genero: "",
    cel: "",
    dir: "",
    ocupacion: "",
    _method: "POST",
});

export const usePacientes = () => {
    const setPaciente = (item = null) => {
        if (item) {
            oPaciente.value.id = item.id;
            oPaciente.value.nombre = item.nombre;
            oPaciente.value.paterno = item.paterno;
            oPaciente.value.materno = item.materno;
            oPaciente.value.ci = item.ci;
            oPaciente.value.ci_exp = item.ci_exp;
            oPaciente.value.fecha_nac = item.fecha_nac;
            oPaciente.value.genero = item.genero;
            oPaciente.value.cel = item.cel;
            oPaciente.value.dir = item.dir;
            oPaciente.value.ocupacion = item.ocupacion;
            oPaciente.value._method = "PUT";
            return oPaciente;
        }
        return false;
    };

    const limpiarPaciente = () => {
        oPaciente.value.id = 0;
        oPaciente.value.nombre = "";
        oPaciente.value.paterno = "";
        oPaciente.value.materno = "";
        oPaciente.value.ci = "";
        oPaciente.value.ci_exp = "";
        oPaciente.value.fecha_nac = "";
        oPaciente.value.genero = "";
        oPaciente.value.cel = "";
        oPaciente.value.dir = "";
        oPaciente.value.ocupacion = "";
        oPaciente.value._method = "POST";
    };

    onMounted(() => {});

    return {
        oPaciente,
        setPaciente,
        limpiarPaciente,
    };
};
