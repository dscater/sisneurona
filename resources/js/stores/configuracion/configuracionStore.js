import { defineStore } from "pinia";

export const useConfiguracionStore = defineStore("configuracion", {
    state: () => ({
        oConfiguracion: {
            razon_social:"SISOPP S.A.",
            alias:"SO",
            // appends
            url_logo: "",
        },
    }),
    actions: {
        setConfiguracion(value) {
            this.oConfiguracion = value;
        },
    },
});
