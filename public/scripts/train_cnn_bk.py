# train_cnn.py
import os
import numpy as np
import tensorflow as tf
from tensorflow.keras import layers, models

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
RUTA_ENTRENAMIENTO = os.path.join(BASE_DIR, "ENTRENAMIENTO")
CLASES = ["EPILEPSIA", "ENCEFALOPATIA", "NORMAL"]

X_train, y_train = [], []

print("EMPEZANDO ENTRANIMENTO")
for i, clase in enumerate(CLASES):
    carpeta = os.path.join(RUTA_ENTRENAMIENTO, clase)
    for archivo in os.listdir(carpeta):
        if archivo.endswith(".EDF"):
            print("Leyendo:", archivo)
            # cargar los archivos
            from DiagnosticoCNN import cargar_y_preprocesar_edf
            datos = cargar_y_preprocesar_edf(os.path.join(carpeta, archivo))
            # Tomamos el primer canal y recortamos 128 muestras
            X_train.append(datos[0,:128].reshape(128,1))
            y_onehot = np.zeros(len(CLASES))
            y_onehot[i] = 1.0
            y_train.append(y_onehot)

X_train = np.array(X_train)
y_train = np.array(y_train)

# Modelo CNN
inputs = (128,1)
model = tf.keras.Sequential([
    layers.Conv1D(32, 3, activation='relu', input_shape=inputs),
    layers.MaxPooling1D(2),
    layers.Conv1D(64,3, activation='relu'),
    layers.MaxPooling1D(2),
    layers.Flatten(),
    layers.Dense(128, activation='relu'),
    layers.Dropout(0.3),
    layers.Dense(3, activation='softmax')
])

model.compile(optimizer='adam', loss='categorical_crossentropy', metrics=['accuracy'])

model.fit(X_train, y_train, epochs=30, batch_size=16, validation_split=0.1)

# Guardar el modelo
model.save(os.path.join(BASE_DIR, "modelo_cnn.h5"))
print("Modelo entrenado y guardado!")
