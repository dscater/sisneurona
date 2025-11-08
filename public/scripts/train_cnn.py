# train_cnn.py
import os
import numpy as np
import tensorflow as tf
from tensorflow.keras import layers, models
import pyedflib

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
RUTA_ENTRENAMIENTO = os.path.join(BASE_DIR, "ENTRENAMIENTO")
CLASES = ["EPILEPSIA", "ENCEFALOPATIA", "NORMAL"]

def banda_pass(signal, fs=128, lowcut=0.5, highcut=40.0):
    try:
        from scipy.signal import butter, filtfilt
        nyq = 0.5 * fs
        b, a = butter(4, [lowcut/nyq, highcut/nyq], btype='band')
        return filtfilt(b, a, signal)
    except:
        freqs = np.fft.rfftfreq(len(signal), d=1/fs)
        spec = np.fft.rfft(signal)
        mask = (freqs >= lowcut) & (freqs <= highcut)
        spec *= mask
        return np.fft.irfft(spec, n=len(signal))

def verificar_edf(ruta_archivo):
    """Intenta abrir un EDF, retorna True si es válido, False si no"""
    try:
        f = pyedflib.EdfReader(ruta_archivo)
        f._close()
        return True
    except Exception as e:
        print(f"Archivo inválido EDF: {ruta_archivo}, error: {e}")
        return False

def cargar_edf(ruta_archivo):
    f = pyedflib.EdfReader(ruta_archivo)
    n_sig = f.signals_in_file
    min_len = min(f.getNSamples())
    n_samples = min(min_len, 128)
    data = []
    for i in range(n_sig):
        sig = f.readSignal(i)[:n_samples]
        data.append(banda_pass(sig))
    f._close()
    data = np.array(data)
    if data.shape[0] < 4:
        pad = np.zeros((4 - data.shape[0], data.shape[1]))
        data = np.vstack([data, pad])
    data = (data - np.mean(data)) / (np.std(data) + 1e-10)
    return data[:4, :128]

# ==========================
# Cargar datos válidos
# ==========================
X, y = [], []

print("EMPEZANDO ENTRENAMIENTO")
for idx, clase in enumerate(CLASES):
    carpeta = os.path.join(RUTA_ENTRENAMIENTO, clase)
    for archivo in os.listdir(carpeta):
        if archivo.lower().endswith(".edf"):
            ruta = os.path.join(carpeta, archivo)
            if verificar_edf(ruta):  # Solo procesar EDF válidos
                print("Cargando:", archivo)
                eeg = cargar_edf(ruta)
                X.append(eeg.T)
                y.append(idx)
            else:
                print("Se omite archivo no válido:", archivo)

if len(X) == 0:
    raise ValueError("No se encontraron archivos EDF válidos. Revisa tu carpeta de entrenamiento.")

X = np.array(X, dtype=np.float32)
y = tf.keras.utils.to_categorical(y, num_classes=3)

# ==========================
# Construir y entrenar modelo
# ==========================
model = models.Sequential([
    layers.Input(shape=(128,4)),
    layers.Conv1D(32, 3, activation='relu'),
    layers.MaxPooling1D(2),
    layers.Conv1D(64,3, activation='relu'),
    layers.MaxPooling1D(2),
    layers.Flatten(),
    layers.Dense(128, activation='relu'),
    layers.Dropout(0.3),
    layers.Dense(3, activation='softmax')
])
model.compile(optimizer='adam', loss='categorical_crossentropy', metrics=['accuracy'])

model.fit(X, y, epochs=30, batch_size=16, validation_split=0.1)

# Guardar modelo
model.save(os.path.join(BASE_DIR, "cnn_model.keras"))
print("Modelo entrenado y guardado como cnn_model.keras")
