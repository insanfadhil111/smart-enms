from datetime import datetime, timedelta
from fastapi import FastAPI
import pandas as pd
import numpy as np
from sklearn.preprocessing import MinMaxScaler
from tensorflow.keras.models import Sequential, load_model
from tensorflow.keras.layers import GRU, Dense, Dropout
import requests
import os
import uvicorn
import logging
import json

# URL API POST
post_url = 'http://127.0.0.1:8000/api/terima-forecast'

n_steps = 7  # Sequence length for training and prediction
headers = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
    "Content-Type": "application/json"
}

if __name__ == "__main__":
    uvicorn.run(app, host="127.0.0.1", port=8080)

app = FastAPI()

@app.get("/")
def read_root():
    return {"Hello": "World"}

@app.get("/predict")
async def predict_energy():
    try:
        # Mengambil data dari API
        url = 'https://iotlab-uns.com/smart-enms/api/daily-energy-reversed'
        response = requests.get(url, headers=headers)
        data = response.json()

        # Convert date strings to datetime objects
        for record in data:
            record["date"] = datetime.strptime(record["date"], "%Y-%m-%d").date()

        today = datetime.now().date()
        last_day = today - timedelta(days=today.weekday() - 2)

        # Filter data sebelum Rabu terakhir
        filtered_data = [record for record in data if record["date"] <= last_day]

        # Convert dates and energy data
        dates = [record['date'] for record in filtered_data]
        energies = [record['today_energy'] for record in filtered_data]
        energies = np.array(energies).reshape(-1, 1)

        # Preprocess data: Normalize
        scaler = MinMaxScaler()
        energies_normalized = scaler.fit_transform(energies)

        # Load pre-trained GRU model
        model = load_model("gru_capstone.keras")

        # Membuat prediksi 14 hari ke depan
        predictions = []
        current_batch = energies_normalized[-n_steps:].reshape((1, n_steps, 1))

        for i in range(14):
            current_pred = model.predict(current_batch)[0]
            predictions.append(current_pred)
            current_batch = np.append(current_batch[:, 1:, :], [[current_pred]], axis=1)

        # Inverse transform predictions
        predictions_actual = scaler.inverse_transform(predictions)

        # Format predictions into JSON with corresponding dates
        results = []
        last_date = today - timedelta(days=today.weekday() - 3)  # Mulai Kamis
        for i, prediction in enumerate(predictions_actual):
            results.append({
                "date": (last_date + timedelta(days=i)).strftime("%Y-%m-%d"),
                "prediction": float(prediction[0])  # Convert to float for JSON serialization
            })

        # Menampilkan data yang akan dikirim
        print("Data yang akan dikirim:")
        print(json.dumps(results, indent=4))

        # Send the predictions to the Laravel API
        response = requests.post(post_url, json=results, headers=headers)
        if response.status_code == 200:
            return {"message": "Predictions sent successfully", "data": results}
        else:
            return {"message": f"Failed to send predictions to {post_url}", "status_code": response.status_code}

        print("Prediction completed")
        print(f"Results: {results}")
        return {"message": "Predictions sent successfully", "data": results}
    except Exception as e:
        logging.error("Error during prediction:", exc_info=True)
        return {"message": "An error occurred during prediction", "error": str(e)}

# @app.get ("/modelling")
# async def update_model():
#     # Mengambil data dari API
#     url = 'https://iotlab-uns.com/smart-bms/public/api/daily-energy-reversed'
#     response = requests.get(url, headers=headers)
#     data = response.json()

#     # Convert date strings to datetime objects
#     for record in data:
#         record["date"] = datetime.strptime(record["date"], "%Y-%m-%d").date()

#     today = datetime.now().date()
#     last_day = today - timedelta(days=today.weekday() - 2)

#     # Filter data sebelum Rabu terakhir
#     filtered_data = [record for record in data if record["date"] <= last_day]

#     # Extract energy data
#     energies = [record['today_energy'] for record in filtered_data]
#     train_data = np.array(energies).reshape(-1, 1 )

#     # Preprocess data: Normalize
#     scaler = MinMaxScaler()
#     train_data_normalized = scaler.fit_transform(train_data)

#     # Split data into sequences
#     sequences = []
#     labels = []
#     for i in range(len(train_data_normalized) - n_steps):
#         sequences.append(train_data_normalized[i:i+n_steps])
#         labels.append(train_data_normalized[i+n_steps])

#     # Convert sequences and labels to numpy arrays
#     sequences = np.array(sequences)
#     labels = np.array(labels)

#     # Reshape sequences for GRU input
#     sequences = sequences.reshape((sequences.shape[0], sequences.shape[1], 1))

#     # Build GRU model
#     model = Sequential()

#     model.add(GRU(50, activation='relu', input_shape=(n_steps, 1)))
#     model.add(Dense(1))
#     model.compile(optimizer='adam', loss='mse')

#     # Train GRU model
#     model.fit(sequences, labels, epochs=50, verbose=0)

#     # Save trained GRU model
#     model.save("gru_capstone.h5")

#     return {"message": "Model updated successfully"}

# if __name__ == "__main__":
#     uvicorn.run(app, host="0.0.0.0", port=8080)


# from datetime import datetime, timedelta
# from fastapi import FastAPI
# import pandas as pd
# import numpy as np
# from sklearn.preprocessing import MinMaxScaler
# from tensorflow.keras.models import Sequential, load_model
# from tensorflow.keras.layers import GRU, Dense, Dropout
# import requests
# import os
# import uvicorn
# import logging
# import json

# # URL API POST
# post_url = 'http://127.0.0.1:8000/api/terima-forecast'

# n_steps = 7  # Sequence length for training and prediction
# headers = {
#     "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
#     "Content-Type": "application/json"
# }

# app = FastAPI()


# @app.get("/")
# def read_root():
#     return {"Hello": "World"}

# @app.get("/predict")
# async def predict_energy():
#     try:
#         # Mengambil data dari API
#         url = 'https://iotlab-uns.com/smart-bms/public/api/daily-energy-reversed'
#         response = requests.get(url, headers=headers)
#         data = response.json()

#         # Convert date strings to datetime objects
#         for record in data:
#             record["date"] = datetime.strptime(record["date"], "%Y-%m-%d").date()

#         today = datetime.now().date()
#         last_day = today - timedelta(days=today.weekday() - 2)

#         # Filter data sebelum Rabu terakhir
#         filtered_data = [record for record in data if record["date"] <= last_day]

#         # Convert dates and energy data
#         dates = [record['date'] for record in filtered_data]
#         energies = [record['today_energy'] for record in filtered_data]
#         energies = np.array(energies).reshape(-1, 1)

#         # Preprocess data: Normalize
#         scaler = MinMaxScaler()
#         energies_normalized = scaler.fit_transform(energies)

#         # Load pre-trained GRU model
#         model = load_model("gru_capstone.h5")

#         # Membuat prediksi 14 hari ke depan
#         predictions = []
#         current_batch = energies_normalized[-n_steps:].reshape((1, n_steps, 1))

#         for i in range(14):
#             current_pred = model.predict(current_batch)[0]
#             predictions.append(current_pred)
#             current_batch = np.append(current_batch[:, 1:, :], [[current_pred]], axis=1)

#         # Inverse transform predictions
#         predictions_actual = scaler.inverse_transform(predictions)

#         # Format predictions into JSON with corresponding dates
#         results = []
#         last_date = today - timedelta(days=today.weekday() - 3)  # Mulai Kamis
#         for i, prediction in enumerate(predictions_actual):
#             results.append({
#                 "date": (last_date + timedelta(days=i)).strftime("%Y-%m-%d"),
#                 "prediction": float(prediction[0])  # Convert to float for JSON serialization
#             })

#         # Send the predictions to the Laravel API
#         response = requests.post(post_url, json=json.dumps(results), headers=headers)
#         if response.status_code == 200:
#             return {"message": "Predictions sent successfully", "data": results}
#         else:
#             return {"message": f"Failed to send predictions to {post_url}", "status_code": response.status_code}
        
#         print("Prediction completed")
#         print(f"Results: {results}")
#         return {"message": "Predictions sent successfully", "data": results}
#     except Exception as e:
#         logging.error("Error during prediction:", exc_info=True)
#         return {"message": "An error occurred during prediction", "error": str(e)}

@app.get ("/modelling")
async def update_model():
    # Mengambil data dari API
    url = 'https://iotlab-uns.com/smart-enms/api/daily-energy-reversed'
    response = requests.get(url, headers=headers)
    data = response.json()

    # Convert date strings to datetime objects
    for record in data:
        record["date"] = datetime.strptime(record["date"], "%Y-%m-%d").date()

    today = datetime.now().date()
    last_day = today - timedelta(days=today.weekday() - 2)

    # Filter data sebelum Rabu terakhir
    filtered_data = [record for record in data if record["date"] <= last_day]

    # Extract energy data
    energies = [record['total'] for record in filtered_data]
    train_data = np.array(energies).reshape(-1, 1)

    # Normalize data
    scaler = MinMaxScaler()
    train_data_normalized = scaler.fit_transform(train_data)

    # Prepare data for GRU
    X_train, y_train = [], []
    for i in range(len(train_data_normalized) - n_steps):
        X_train.append(train_data_normalized[i:i + n_steps])
        y_train.append(train_data_normalized[i + n_steps])

    X_train, y_train = np.array(X_train), np.array(y_train)

    # Build the GRU model
    model = Sequential()
    model.add(GRU(64, activation='relu', input_shape=(n_steps, 1)))
    model.add(Dropout(0.3))
    model.add(Dense(32, activation='relu'))
    model.add(Dropout(0.3))
    model.add(Dense(1))
    model.compile(optimizer='adam', loss='mean_squared_error')

    # Train the model
    model.fit(X_train, y_train, epochs=500, batch_size=16, verbose=0)

    # Save the model
    model.save('gru_capstone.keras')

    return {"message": "Model updated successfully"}


