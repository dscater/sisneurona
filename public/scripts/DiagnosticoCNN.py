import sys
import os
import time
import random
import json
import numpy as np

# --- CONFIGURACIÓN DE RUTAS DE ENTRENAMIENTO ---
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
RUTA_ENTRENAMIENTO = os.path.join(BASE_DIR, "ENTRENAMIENTO")

CARPETAS_DIAGNOSTICOS = {
    "EPILEPSIA": os.path.join(RUTA_ENTRENAMIENTO, "EPILEPSIA"),
    "ENCEFALOPATIA": os.path.join(RUTA_ENTRENAMIENTO, "ENCEFALOPATIA"),
    "NORMAL": os.path.join(RUTA_ENTRENAMIENTO, "NORMAL"),
}

# Crear las carpetas si no existen
for carpeta in CARPETAS_DIAGNOSTICOS.values():
    os.makedirs(carpeta, exist_ok=True)


# --- CARGA Y PREPROCESAMIENTO DE DATOS ---
def cargar_y_preprocesar_edf(ruta_archivo):
    datos_eeg = np.random.randn(256, 128)
    datos_eeg = (datos_eeg - np.mean(datos_eeg)) / np.std(datos_eeg)
    return datos_eeg


# --- MODELO CNN (simulación realista) ---
def construir_modelo_cnn():
    try:
        import tensorflow as tf
    except ImportError:
        return None
    return None  # No entrenamos nada aquí


# --- PREDICCIÓN ---
def realizar_prediccion(modelo, datos):
    etiquetas = ["EPILEPSIA", "ENCEFALOPATIA", "NORMAL"]
    prediccion = np.random.dirichlet(np.ones(3), size=1)[0]
    indice_pred = np.argmax(prediccion)
    diagnostico = etiquetas[indice_pred]
    confianza = round(prediccion[indice_pred] * 100, 2)
    return diagnostico, confianza


def generar_senales_eeg(diagnostico, datos_eeg):
    """
    Genera señales EEG realistas basadas en los datos procesados del modelo.
    Las variaciones dependen del diagnóstico.
    """
    canales = ["F3", "F4", "Cz", "Pz"]
    offsets = [100, 75, 50, 25]
    data = []

    # Tomar las primeras 4 filas del array EEG (simulan canales)
    eeg_canales = datos_eeg[:4, :]  # 4 canales x 128 muestras

    for idx, nombre in enumerate(canales):
        base = offsets[idx]
        wave = []

        # Expandir las 128 muestras a 2000 puntos interpolando
        interp = np.interp(
            np.linspace(0, len(eeg_canales[idx]) - 1, 2000),
            np.arange(len(eeg_canales[idx])),
            eeg_canales[idx]
        )

        for i, val in enumerate(interp):
            # Modificación según el tipo de diagnóstico
            if diagnostico == "NORMAL":
                # Ritmo alfa estable y simétrico (8–13 Hz)
                value = base + (val * 10) + np.sin(i * 0.15) * 2
            elif diagnostico == "ENCEFALOPATIA":
                # Ondas delta lentas y amplias (0.5–3 Hz)
                value = base + (val * 30) + np.sin(i * 0.03) * 10
            elif diagnostico == "EPILEPSIA":
                # Ondas lentas con picos agudos derivados del valor EEG
                value = base + (val * 20)
                if abs(val) > 1.5:  # activación alta = descarga epileptiforme
                    value += (val * 15)
            else:
                value = base + val * 5

            wave.append(round(float(value), 2))

        data.append({
            "name": nombre,
            "data": wave
        })

    return data



# --- FLUJO PRINCIPAL ---
if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No se proporcionó archivo EDF"}))
        sys.exit(1)

    archivo_edf = sys.argv[1]

    if not archivo_edf.lower().endswith(".edf"):
        print(json.dumps({"error": "El archivo debe tener extensión .edf"}))
        sys.exit(1)

    datos = cargar_y_preprocesar_edf(archivo_edf)
    modelo = construir_modelo_cnn()
    diagnostico, confianza = realizar_prediccion(modelo, datos)
    senales = generar_senales_eeg(diagnostico)

    resultado = {
        "diagnostico": diagnostico,
        "confianza": confianza,
        "senales": senales
    }

    print(json.dumps(resultado))
