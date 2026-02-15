@echo off
echo ================================
echo Testing NASA Earthdata Credentials
echo ================================
echo.

REM Read credentials from .env
if not exist .env (
    echo ERROR: .env file not found
    exit /b 1
)

for /f "tokens=2 delims==" %%a in ('findstr "NASA_EARTHDATA_USERNAME" .env') do set USERNAME=%%a
for /f "tokens=2 delims==" %%a in ('findstr "NASA_EARTHDATA_PASSWORD" .env') do set PASSWORD=%%a

if "%USERNAME%"=="" (
    echo ERROR: NASA_EARTHDATA_USERNAME not found in .env
    exit /b 1
)

if "%PASSWORD%"=="" (
    echo ERROR: NASA_EARTHDATA_PASSWORD not found in .env
    exit /b 1
)

echo Username: %USERNAME%
echo Password: ****
echo.
echo Testing authentication...
echo.

REM Test with curl (if available)
curl -X POST "https://appeears.earthdatacloud.nasa.gov/api/login" ^
  -u "%USERNAME%:%PASSWORD%" ^
  -H "Content-Type: application/json" ^
  -w "\nHTTP Status: %%{http_code}\n" ^
  -s

echo.
echo.
echo ================================
echo If you see a token above: SUCCESS (credentials valid)
echo If you see 401/unauthorized: FAILED (credentials invalid)
echo ================================
echo.
echo Next steps:
echo   - If SUCCESS: Use NASA_EARTHDATA_MOCK=false in production
echo   - If FAILED: Register at https://urs.earthdata.nasa.gov/users/new
echo.

REM Alternative: Use PHP artisan command
echo You can also test with:
echo   php artisan remote-sensing:test-credentials
echo.

pause
