# Estado Actual de ComunidadIFTS

## Resumen

ComunidadIFTS es una plataforma con frontend Angular 21 y backend PHP para centralizar instituciones IFTS, autenticacion, administracion por roles, personalizacion del sitio, perfiles y bolsa de trabajo.

## Estado tecnico vigente

- Frontend standalone con Angular 21, Angular Material, CDK y Leaflet.
- Backend PHP con PDO, Dotenv, PHPMailer y Cloudinary.
- Arquitectura backend separada en `api`, `models`, `services` y `config`.
- Sistema de roles vigente por ID: `1` AdministradorComunidad, `2` Alumno, `3` AdministradorIFTS.
- Flujo de bolsa de trabajo validado documentalmente con QA pass al 16-04-2026.
- Existen correcciones posteriores documentadas para gestion de ofertas, gestion de carreras y proxy local.

## Stack tecnico

- Frontend: Angular 21.1.x, Angular Material, Angular CDK, RxJS, Leaflet.
- Backend: PHP 7.4+, MySQL, Composer.
- Librerias backend: `vlucas/phpdotenv`, `phpmailer/phpmailer`, `cloudinary/cloudinary_php`.

## Estructura real del proyecto

```text
ComunidadIFTS/
├── BackEnd/
│   ├── api/                 # Endpoints HTTP
│   ├── config/              # DB, CORS, mail, media folders
│   ├── database/            # Schema y migraciones
│   ├── docs/                # Arquitectura y endpoints backend
│   ├── models/              # SQL y persistencia
│   ├── scripts/             # Utilidades operativas
│   └── services/            # Cloudinary y otras integraciones
├── FrontEnd/
│   ├── src/app/features/    # Home, contacto, tienda, perfiles, admin, bolsa
│   ├── src/app/shared/      # Guards, servicios, componentes y modelos
│   └── proxy.conf.json
├── docs/                    # Documentacion humana centralizada
└── README.md
```

## Frontend vigente

### Features detectadas

- `home`
- `contacto`
- `tienda`
- `bolsa-trabajo`
- `perfil-alumno`
- `perfil-institucion`
- `admin`

### Rutas principales

- `/home`
- `/mapa`
- `/tienda`
- `/contacto`
- `/admin/dashboard`
- `/admin/gestion-usuarios`
- `/admin/gestion-instituciones`
- `/admin/gestion-carreras`
- `/admin/gestion-ofertas`
- `/admin/panel-ofertas-global`
- `/bolsa-trabajo`
- `/perfil`
- `/crear-oferta`
- `/perfil-institucion`

### Acceso por rol

- Rol 1: rutas `/admin/*`
- Rol 2: `/bolsa-trabajo` y `/perfil`
- Rol 3: `/crear-oferta` y `/perfil-institucion`

## Backend vigente

### Modulos principales detectados

- Autenticacion local y Google.
- Registro con aprobacion manual de usuarios.
- Gestion de instituciones.
- Gestion de carreras y materias.
- Personalizacion publica del sitio.
- Perfil de alumno.
- Perfil de institucion.
- Bolsa de trabajo, postulaciones y cancelacion de postulacion.
- Contacto.

### Endpoints representativos

- Publicos: `instituciones.php`, `carreras.php`, `contacto.php`, `stats-publicas.php`.
- Autenticacion: `login.php`, `register.php`, `google-auth.php`, `logout.php`.
- Admin comunidad: `usuarios-pendientes.php`, `aprobar-usuario.php`, `usuarios-registrados.php`, `cambiar-rol-usuario.php`, `dashboard-stats.php`, `gestionar-oferta.php`.
- Instituciones y carreras: `guardar-institucion.php`, `actualizar-institucion.php`, `eliminar-institucion.php`, `gestion-carreras.php`.
- Bolsa y perfiles: `crear-oferta.php`, `ofertas-pendientes.php`, `ofertas-publicadas.php`, `postularse.php`, `cancelar-postulacion.php`, `perfil-alumno.php`, `perfil-institucion.php`, `actualizar-datos-academicos.php`, `actualizar-foto-perfil.php`.

## Estado documental

- La documentacion previa estaba dispersa entre `docs`, la raiz del proyecto y `BackEnd/docs`.
- La fuente de verdad para roles ahora vive en `docs/roles.md` y en el codigo real.
- `BackEnd/docs/ENDPOINTS.md` tenia drift documental sobre roles y fue corregido para alinearlo con el codigo vigente.
- Los snapshots de deploy y correcciones fueron consolidados en `docs/deploy.md` y `docs/historial-tecnico.md`.

## Estado operativo documentado

- QA funcional ejecutado y documentado con 7 casos pass al 16-04-2026.
- Deploy marcado como listo en esa misma etapa.
- Pendiente operativo recurrente: configuracion y validacion real de SMTP en entorno de deploy.

## Punto de arranque recomendado

1. Leer este archivo.
2. Validar roles y rutas desde `FrontEnd/src/app/app.routes.ts` si la tarea toca permisos.
3. Validar APIs desde `BackEnd/docs/ENDPOINTS.md` y desde `BackEnd/api/` si la tarea toca backend.
4. Usar los QA documentados como referencia de comportamiento esperado.