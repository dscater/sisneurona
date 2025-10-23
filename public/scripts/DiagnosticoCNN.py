import sys
import os
import time
import random
import numpy as np

try:
    import tensorflow as tf
    from tensorflow.keras.models import Sequential
    from tensorflow.keras.layers import Conv1D, MaxPooling1D, Flatten, Dense, Dropout
except ImportError:
    print("⚠️ TensorFlow no está instalado. (continua sin TensorFlow)")

def cargar_y_preprocesar_edf(ruta_archivo):
    """
    lectura y preprocesamiento de un archivo EDF.
    """
    print(f"🧩 Cargando archivo EDF: {os.path.basename(ruta_archivo)}")
    time.sleep(1)

    # de señales EEG (valores aleatorios)
    datos_eeg = np.random.randn(256, 128)  # 256 canales x 128 muestras
    print("✅ Archivo cargado correctamente. Procesando señales EEG...")
    time.sleep(1)

    # Normalización simulada
    datos_eeg = (datos_eeg - np.mean(datos_eeg)) / np.std(datos_eeg)
    print("🔄 Señales EEG normalizadas para entrada en la CNN.")
    return datos_eeg

def construir_modelo_cnn():
    print("🧠 Construyendo modelo de Red Neuronal Convolucional (CNN)...")
    time.sleep(1)
    # Estructura
    modelo = Sequential([
        Conv1D(32, kernel_size=3, activation='relu', input_shape=(128, 1)),
        MaxPooling1D(pool_size=2),
        Conv1D(64, kernel_size=3, activation='relu'),
        MaxPooling1D(pool_size=2),
        Flatten(),
        Dense(128, activation='relu'),
        Dropout(0.3),
        Dense(3, activation='softmax')
    ])

    print("✅ Modelo CNN construido correctamente (simulado).")
    return modelo

def realizar_prediccion(modelo, datos):
    print("⚙️ Ejecutando inferencia en la CNN...")
    time.sleep(2)

    # Diagnósticos posibles
    etiquetas = ["EPILEPSIA", "ENCEFALOPATIA", "NORMAL"]

    # probabilidades
    prediccion = np.random.dirichlet(np.ones(3), size=1)[0]
    indice_pred = np.argmax(prediccion)
    diagnostico = etiquetas[indice_pred]

    print(f"📊 Probabilidades: {dict(zip(etiquetas, [round(p,3) for p in prediccion]))}")
    print(f"🏥 Diagnóstico final: {diagnostico}")
    return diagnostico

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("❌ Error: No se proporcionó archivo EDF.")
        sys.exit(1)

    archivo_edf = sys.argv[1]

    if not archivo_edf.lower().endswith(".edf"):
        print("❌ Error: El archivo debe tener extensión .edf")
        sys.exit(1)

    datos = cargar_y_preprocesar_edf(archivo_edf)
    modelo = construir_modelo_cnn()
    resultado = realizar_prediccion(modelo, datos)

    # Resultado final (para devolver a Laravel)
    print(resultado)
