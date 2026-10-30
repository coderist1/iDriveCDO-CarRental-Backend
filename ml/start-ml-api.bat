@echo off
cd /d "%~dp0"
echo.
echo  iDrive CDO Maintenance ML API
echo  Folder: %cd%
echo  URL:    http://localhost:8001
echo  Docs:   http://localhost:8001/docs
echo.
python -m uvicorn app:app --app-dir "%~dp0" --reload-dir "%~dp0"    
pause
