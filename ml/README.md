# iDrive CDO — Maintenance ML service

Random Forest predictive-maintenance API (FastAPI). Serves failure-imminent scores for the Desk **Predictive maintenance** page.

## Layout

```
ml/
  app.py                 FastAPI entry (uvicorn app:app)
  ml_model.py            Loads joblib models and runs inference
  requirements.txt       Python deps
  Dockerfile             Container image for port 8001
  start-ml-api.bat       Local Windows launcher
  models/                Trained .joblib artifacts + training_summary.json
  training/
    train_export.py      Retrain / export models from telemetry CSV
```

## Run locally

```bat
cd ml
python -m pip install -r requirements.txt
start-ml-api.bat
```

- API: http://127.0.0.1:8001  
- Docs: http://127.0.0.1:8001/docs  

The frontend (`ml-client.js`) calls this URL. Laravel stays on port 8000; this ML service stays on **8001**.

## Retrain

```bat
cd ml
python training\train_export.py --csv "%USERPROFILE%\Downloads\synthetic_telemetry_data.csv"
```

Artifacts are written to `models/`.

## Docker

Build context is this `ml/` folder. From the frontend repo compose file, set `IDRIVE_ML_DIR` to this path if needed.
