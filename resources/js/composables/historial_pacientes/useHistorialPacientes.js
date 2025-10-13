import { onMounted, ref } from "vue";

const oHistorialPaciente = ref({
    id: 0,
    paciente_id: "",
    motivo_consulta: "",
    historial_enfermedad: "",
    antecedentes_personales: "",
    antecedentes_familiares: "",
    antecedentes_no_personales: "",
    examenes_neurologicos: "",
    tratamientos: "",
    evoluciones: "",
    consultas: "",
    historial_archivos: [],
    eliminados_archivos: [],
    _method: "POST",
});

export const useHistorialPacientes = () => {
    const setHistorialPaciente = (item = null) => {
        if (item) {
            oHistorialPaciente.value.id = item.id;
            oHistorialPaciente.value.paciente_id = item.paciente_id;
            oHistorialPaciente.value.motivo_consulta = item.motivo_consulta;
            oHistorialPaciente.value.historial_enfermedad =
                item.historial_enfermedad;
            oHistorialPaciente.value.antecedentes_personales =
                item.antecedentes_personales;
            oHistorialPaciente.value.antecedentes_familiares =
                item.antecedentes_familiares;
            oHistorialPaciente.value.antecedentes_no_personales =
                item.antecedentes_no_personales;
            oHistorialPaciente.value.examenes_neurologicos =
                item.examenes_neurologicos;
            oHistorialPaciente.value.tratamientos = item.tratamientos;
            oHistorialPaciente.value.evoluciones = item.evoluciones;
            oHistorialPaciente.value.consultas = item.consultas;
            oHistorialPaciente.value.historial_archivos =
                item.historial_archivos;
            oHistorialPaciente.value.eliminados_archivos = [];
            oHistorialPaciente.value._method = "PUT";
            return oHistorialPaciente;
        }
        return false;
    };

    const limpiarHistorialPaciente = () => {
        oHistorialPaciente.value.id = 0;
        oHistorialPaciente.value.paciente_id = "";
        oHistorialPaciente.value.motivo_consulta = "";
        oHistorialPaciente.value.historial_enfermedad = "";
        oHistorialPaciente.value.antecedentes_personales = "";
        oHistorialPaciente.value.antecedentes_familiares = "";
        oHistorialPaciente.value.antecedentes_no_personales = "";
        oHistorialPaciente.value.examenes_neurologicos = "";
        oHistorialPaciente.value.tratamientos = "";
        oHistorialPaciente.value.evoluciones = "";
        oHistorialPaciente.value.consultas = "";
        oHistorialPaciente.value.historial_archivos = [];
        oHistorialPaciente.value.eliminados_archivos = [];
        oHistorialPaciente.value._method = "POST";
    };

    onMounted(() => {});

    return {
        oHistorialPaciente,
        setHistorialPaciente,
        limpiarHistorialPaciente,
    };
};
