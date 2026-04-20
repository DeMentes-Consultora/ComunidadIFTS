@echo off
REM Script para preparar ComunidadIFTS para InfinityFree
REM Cuenta: if0_41035439
REM Dominio: comunidadifts.infinityfreeapp.com

echo.
echo ================================================
echo   Preparando ComunidadIFTS para InfinityFree
echo   Dominio: comunidadifts.infinityfreeapp.com
echo ================================================
echo.

set INCLUDE_VENDOR=0
if /I "%~1"=="--with-vendor" set INCLUDE_VENDOR=1

if not exist "BackEnd" (
    echo ERROR: Ejecuta este script desde la carpeta raiz del proyecto
    pause
    exit /b 1
)

echo [1/5] Instalando dependencias del Backend...
cd BackEnd
call composer install --no-dev --optimize-autoloader
if errorlevel 1 (
    echo ERROR: No se pudieron instalar las dependencias del Backend
    cd ..
    pause
    exit /b 1
)
cd ..
echo OK - Dependencias del Backend instaladas
echo.

echo [2/5] Instalando dependencias del Frontend...
cd FrontEnd
call npm install
if errorlevel 1 (
    echo ERROR: No se pudieron instalar las dependencias del Frontend
    cd ..
    pause
    exit /b 1
)
echo OK - Dependencias del Frontend instaladas
echo.

echo [3/5] Compilando Frontend Angular para produccion...
call ng build --configuration production
if errorlevel 1 (
    echo ERROR: No se pudo compilar el Frontend
    cd ..
    pause
    exit /b 1
)
cd ..
echo OK - Frontend compilado
echo.

echo [4/5] Creando carpeta de despliegue...
set DEPLOY_DIR=deploy-infinityfree

if exist "%DEPLOY_DIR%" (
    rmdir /s /q "%DEPLOY_DIR%"
)
mkdir "%DEPLOY_DIR%"

if not exist "BackEnd\.env.production" (
    echo ERROR: No existe BackEnd\.env.production
    echo Crea ese archivo antes de preparar el deploy.
    pause
    exit /b 1
)

echo    - Copiando Backend...
if "%INCLUDE_VENDOR%"=="1" (
    echo      Incluyendo vendor porque se solicito --with-vendor
    xcopy /E /I /Y BackEnd\vendor "%DEPLOY_DIR%\vendor" >nul
) else (
    echo      Omitiendo vendor ^(sin cambios de Composer / deploy incremental^)
)
xcopy /E /I /Y BackEnd\config "%DEPLOY_DIR%\config" >nul
xcopy /E /I /Y BackEnd\api "%DEPLOY_DIR%\api" >nul
xcopy /E /I /Y BackEnd\models "%DEPLOY_DIR%\models" >nul
xcopy /E /I /Y BackEnd\services "%DEPLOY_DIR%\services" >nul
copy /Y BackEnd\.htaccess "%DEPLOY_DIR%\" >nul
copy /Y BackEnd\check-server.php "%DEPLOY_DIR%\" >nul
copy /Y BackEnd\.env.production "%DEPLOY_DIR%\.env" >nul

echo    - Copiando Frontend compilado...
powershell -Command "$browser='FrontEnd\dist\ComunidadIFTS\browser'; $root='FrontEnd\dist\ComunidadIFTS'; if (Test-Path $browser) { Copy-Item -Path ($browser + '\*') -Destination '%DEPLOY_DIR%' -Recurse -Force } elseif (Test-Path $root) { Copy-Item -Path ($root + '\*') -Destination '%DEPLOY_DIR%' -Recurse -Force; Remove-Item -Path '%DEPLOY_DIR%\browser' -Recurse -Force -ErrorAction SilentlyContinue; Remove-Item -Path '%DEPLOY_DIR%\prerendered-routes.json' -Force -ErrorAction SilentlyContinue; Remove-Item -Path '%DEPLOY_DIR%\3rdpartylicenses.txt' -Force -ErrorAction SilentlyContinue } else { exit 1 }" 2>nul
if errorlevel 1 (
    echo ERROR: No se pudo copiar el Frontend compilado
    echo Verifica que exista FrontEnd\dist\ComunidadIFTS\ o FrontEnd\dist\ComunidadIFTS\browser\
    pause
    exit /b 1
)

echo OK - Carpeta de despliegue creada
echo.

echo [5/5] Creando instrucciones...
(
echo ================================================================
echo          INSTRUCCIONES DE DESPLIEGUE - COMUNIDADIFTS
echo ================================================================
echo.
echo IMPORTANTE: Antes de subir los archivos, configura tu contraseña
echo.
echo 1. Edita el archivo .env en la carpeta deploy-infinityfree:
echo    - Busca la linea: DB_PASS=
echo    - Agrega tu contraseña de MySQL de InfinityFree
echo    - Ejemplo: DB_PASS=tu_contraseña_aqui
echo    - Completa tambien SMTP real ^(NO dejes placeholders^):
echo      MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS, ADMIN_EMAIL
echo    - No usar valores como: tu-email@gmail.com o tu-contraseña-de-aplicacion
echo.
echo 2. Conecta via FTP a InfinityFree:
echo    Host: ftpupload.net
echo    Puerto: 21
echo    Usuario: epiz_XXXXXXXX ^(tu usuario FTP^)
echo    Contraseña: ^(tu contraseña FTP^)
echo.
echo 3. Sube el contenido de deploy-infinityfree/ a htdocs/
if "%INCLUDE_VENDOR%"=="1" (
echo    - Puedes reemplazar todo el contenido de htdocs/
) else (
echo    - NO elimines vendor/ del servidor si ya existe y no cambiaste Composer
echo    - Sube y reemplaza solo los archivos/carpetas presentes en deploy-infinityfree/
)
echo.
echo 4. Importar la base de datos:
echo    - Accede a phpMyAdmin desde VistaPanel
echo    - Selecciona: if0_41035439_comunidad_ifts
echo    - Importa: BackEnd/database/comunidad_ifts.sql
echo.
echo 5. Verifica el despliegue:
echo    https://comunidadifts.infinityfreeapp.com/check-server.php
echo.
echo 6. Prueba la API:
echo    https://comunidadifts.infinityfreeapp.com/api/carreras.php
echo.
echo 7. Visita tu sitio:
echo    https://comunidadifts.infinityfreeapp.com
echo.
echo 8. SEGURIDAD: Elimina check-server.php del servidor
echo.
echo ================================================================
echo Configuracion actual:
echo ================================================================
echo DB Host: sql113.infinityfree.com
echo DB Name: if0_41035439_comunidad_ifts
echo DB User: if0_41035439
echo Dominio: comunidadifts.infinityfreeapp.com
echo ================================================================
) > "%DEPLOY_DIR%\INSTRUCCIONES.txt"

echo OK - Instrucciones creadas
echo.

echo ================================================
echo   PREPARACION COMPLETADA
echo ================================================
echo.
echo SIGUIENTE PASO:
echo.
echo 1. Edita: deploy-infinityfree\.env
echo    Agrega tu contraseña de MySQL en la linea DB_PASS=
echo    Completa MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS y ADMIN_EMAIL reales
echo.
echo 2. Lee: deploy-infinityfree\INSTRUCCIONES.txt
echo.
if "%INCLUDE_VENDOR%"=="1" (
echo 3. Sube los archivos via FTP a InfinityFree ^(incluye vendor/^)
) else (
echo 3. Sube los archivos via FTP a InfinityFree ^(sin borrar vendor/ en el server^)
echo    Si necesitas un deploy completo, ejecuta: prepare-deploy.bat --with-vendor
)
echo.
echo Presiona cualquier tecla para salir...
pause >nul
