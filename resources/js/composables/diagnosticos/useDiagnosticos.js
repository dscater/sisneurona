import { onMounted, ref } from "vue";

const oDiagnostico = ref({
    id: 0,
    paciente_id: "",
    archivo_edf: "",
    diagnostico: "",
    tipo_patologia_id: "",
    _method: "POST",
});

export const useDiagnosticos = () => {
    const setDiagnostico = (item = null) => {
        if (item) {
            oDiagnostico.value.id = item.id;
            oDiagnostico.value.paciente_id = item.paciente_id;
            oDiagnostico.value.archivo_edf = item.archivo_edf;
            oDiagnostico.value.diagnostico = item.diagnostico;
            oDiagnostico.value.tipo_patologia_id = item.tipo_patologia_id;
            oDiagnostico.value._method = "PUT";
            return oDiagnostico;
        }
        return false;
    };

    const limpiarDiagnostico = () => {
        oDiagnostico.value.id = 0;
        oDiagnostico.value.paciente_id = "";
        oDiagnostico.value.archivo_edf = "";
        oDiagnostico.value.diagnostico = "";
        oDiagnostico.value.tipo_patologia_id = "";
        oDiagnostico.value._method = "POST";
    };

    onMounted(() => {});

    return {
        oDiagnostico,
        setDiagnostico,
        limpiarDiagnostico,
    };
};
