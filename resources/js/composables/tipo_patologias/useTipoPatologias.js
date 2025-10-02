import { onMounted, ref } from "vue";

const oTipoPatologia = ref({
    id: 0,
    nombre: "",
    descripcion: "",
    fecha_registro: "",
    _method: "POST",
});

export const useTipoPatologias = () => {
    const setTipoPatologia = (item = null) => {
        if (item) {
            oTipoPatologia.value.id = item.id;
            oTipoPatologia.value.nombre = item.nombre;
            oTipoPatologia.value.descripcion = item.descripcion;
            oTipoPatologia.value.fecha_registro = item.fecha_registro;
            oTipoPatologia.value._method = "PUT";
            return oTipoPatologia;
        }
        return false;
    };

    const limpiarTipoPatologia = () => {
        oTipoPatologia.value.id = 0;
        oTipoPatologia.value.nombre = "";
        oTipoPatologia.value.descripcion = "";
        oTipoPatologia.value.fecha_registro = "";
        oTipoPatologia.value._method = "POST";
    };

    onMounted(() => {});

    return {
        oTipoPatologia,
        setTipoPatologia,
        limpiarTipoPatologia,
    };
};
