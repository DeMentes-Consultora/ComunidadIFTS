# Deploy ComunidadIFTS

## Objetivo

Centralizar el proceso de despliegue del proyecto tomando como referencia los snapshots operativos generados el 16-04-2026 y el estado real del stack actual.

## Estado operativo conocido

- Build frontend documentado como exitoso.
- Backend documentado como listo para deploy.
- QA funcional documentado con 7 casos pass al 16-04-2026.
- Pendiente operativo recurrente: configuracion real de SMTP en el entorno de produccion.

## Stack de despliegue

- Frontend Angular compilado.
- Backend PHP + Composer.
- MySQL en hosting.
- Destino historico documentado: InfinityFree.

## Archivos a subir

### Frontend

Subir el contenido generado para produccion a la raiz publica del hosting.

Referencia historica documentada:

- `deploy-infinityfree/`

Contenido esperado:

```text
public_html/
├── index.html
├── 3rdpartylicenses.txt
├── favicon.ico
├── .env
├── .htaccess
├── main-*.js
├── styles-*.css
└── chunk-*.js
```

Nota: los archivos van en la raiz publica, no dentro de una carpeta anidada tipo `browser/`.

### Backend

Subir el contenido de `BackEnd/` a la carpeta publica destinada a la API.

Contenido esperado:

```text
api/
├── api/
├── models/
├── config/
├── database/
├── services/
├── scripts/
├── composer.json
├── composer.lock
└── .env
```

No incluir `vendor/` si el servidor permite ejecutar Composer. En ese caso instalar dependencias en el servidor con `composer install --no-dev`.

## Variables de entorno

### Frontend

```env
API_BASE_URL=https://tu-dominio.com/api/
```

### Backend

```env
APP_ENV=production
APP_DEBUG=false
DB_HOST=sqlXXX.infinityfree.com
DB_USER=tu_usuario_db
DB_PASSWORD=tu_password_db
DB_NAME=tu_base
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_email@gmail.com
MAIL_PASSWORD=tu_app_password
MAIL_FROM_ADDRESS=tu_email@gmail.com
MAIL_FROM_NAME=ComunidadIFTS
MAIL_ENCRYPTION=tls
ADMIN_EMAIL=admin@dominio.com
```

Si el proyecto usa Cloudinary en produccion, completar tambien las variables correspondientes del servicio.

## Base de datos

- Esquema base: `BackEnd/database/comunidad_ifts.sql`
- Aplicar las migraciones necesarias segun el entorno.

Migraciones historicamente relevantes detectadas en la documentacion:

- `BackEnd/database/migrations/20260302_gestion_carreras_materias.sql`
- `BackEnd/database/migrations/20260414_dashboard_personalizacion_sitio.sql`
- `BackEnd/database/migrations/20260414_rename_carrousel_id_column.sql`
- `BackEnd/database/migrations/20260419_fix_carrera_materia_habilitado.sql`

## Pasos de despliegue

1. Instalar dependencias locales si hace falta preparar un build nuevo.
2. Compilar frontend para produccion.
3. Preparar variables de entorno reales para frontend y backend.
4. Subir el frontend compilado a la raiz publica.
5. Subir el backend a la carpeta publica de API.
6. Ejecutar `composer install --no-dev` en el servidor si no se sube `vendor/`.
7. Importar esquema base y aplicar migraciones pendientes.
8. Verificar permisos de archivos y carpetas.
9. Ejecutar pruebas post deploy.

## Checklist pre deploy

- [ ] Build frontend completado.
- [ ] Variables de entorno frontend actualizadas.
- [ ] Variables de entorno backend actualizadas.
- [ ] Base de datos existente y actualizada.
- [ ] Dependencias backend resueltas en servidor o incluidas segun estrategia.
- [ ] `.htaccess` en ubicacion correcta.

## Checklist post deploy

- [ ] La home carga sin errores de red.
- [ ] `api/login.php` responde sin error 500.
- [ ] Rol 1 ve menu admin.
- [ ] Rol 2 ve bolsa de trabajo y perfil.
- [ ] Rol 3 ve crear oferta y perfil de institucion.
- [ ] `api/ofertas-publicadas.php` responde para alumno autenticado.
- [ ] El formulario de contacto responde correctamente.
- [ ] Los mails criticos funcionan o queda documentado que SMTP sigue pendiente.

## Problemas frecuentes

### Frontend en blanco

- Revisar que `index.html`, `.js`, `.css` y `.htaccess` esten en la raiz publica.
- Revisar que la URL de `API_BASE_URL` coincida con el dominio real.
- Revisar consola del navegador para detectar 404 o errores CORS.

### Backend con error 500

- Verificar que `.env` exista y tenga valores correctos.
- Verificar que `vendor/` exista o que `composer install --no-dev` se haya ejecutado bien.
- Revisar logs de PHP o Apache del hosting.

### Base de datos no conecta

- Verificar `DB_HOST`, `DB_USER`, `DB_PASSWORD` y `DB_NAME`.
- Confirmar importacion del esquema base.
- Confirmar ejecucion de migraciones pendientes.

### Correos no salen

- Verificar variables SMTP.
- Si se usa Gmail, usar app password y no la contraseña normal.
- Confirmar que el hosting no bloquee el puerto o el proveedor SMTP.

## Estado de esta documentacion

Este archivo consolida la informacion que antes estaba repartida en snapshots de deploy de abril de 2026. La guia vigente debe actualizarse aqui.