@echo off
REM Agregar Node portable al PATH
set PATH=C:\node;%PATH%

REM Navegar a la carpeta FrontEnd
cd /d C:\xampp\htdocs\Proyectos_DeMentes\ComunidadIFTS\FrontEnd

REM Ejecutar ng serve
ng serve --open
