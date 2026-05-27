# Historial Tecnico de ComunidadIFTS

## Alcance

Este archivo concentra los hitos tecnicos relevantes del proyecto y reemplaza la necesidad de reconstruir el contexto leyendo varios snapshots dispersos.

## 2 de marzo de 2026 - Gestion de instituciones y carreras

### Cambio funcional

- Se implemento la gestion de instituciones con tabla administrativa, acciones de editar y eliminar, y formulario con edicion restringida.
- Se implemento la gestion de carreras y materias con interfaz de dos paneles y drag and drop.
- Se adopto uso incremental de Angular signals en pantallas nuevas sin refactor masivo del proyecto.

### Superficies involucradas

- `FrontEnd/src/app/features/admin/gestion-instituciones/`
- `FrontEnd/src/app/features/admin/gestion-carreras/`
- `FrontEnd/src/app/shared/components/formulario-institucion/`
- `FrontEnd/src/app/shared/services/gestion-carreras.service.ts`
- `BackEnd/api/eliminar-institucion.php`
- `BackEnd/api/gestion-carreras.php`
- `BackEnd/models/Materia.php`
- `BackEnd/database/migrations/20260302_gestion_carreras_materias.sql`

### Validacion registrada

- PHP lint OK en endpoints y modelo involucrados.
- Build Angular OK.

## 12 de marzo de 2026 - Google Auth, fotos y refactor backend

### Cambio funcional

- Se completo autenticacion Google con aprobacion manual preservada.
- Se refactorizo backend para eliminar SQL inline en `api/` y delegarlo en `models/`.
- Se incorporo soporte de foto de perfil en `persona`.
- Se agrego endpoint autenticado para actualizar foto de perfil.

### Decisiones tecnicas

- Centralizar fotos en Cloudinary cuando sea posible.
- Si falla Cloudinary durante login o registro Google, usar fallback a la imagen original de Google.
- Mantener `deploy-infinityfree/` como snapshot de subida al servidor.

### Superficies involucradas

- `BackEnd/api/google-auth.php`
- `BackEnd/api/actualizar-foto-perfil.php`
- entidad `persona`
- integracion Cloudinary

## 14 de abril de 2026 - Dashboard de personalizacion del sitio

### Cambio funcional

- Se implemento dashboard admin en `/admin/dashboard` para personalizar el sitio sin tocar codigo.
- Se incorporo edicion de logo, texto de navbar y carrusel principal.
- Se agregaron estadisticas visibles en el dashboard.

### Superficies involucradas

- `BackEnd/api/site-customization.php`
- `BackEnd/api/dashboard-stats.php`
- `BackEnd/models/SiteCustomizationModel.php`
- `FrontEnd/src/app/features/admin/dashboard/`
- `FrontEnd/src/app/shared/services/site-customization.service.ts`
- `FrontEnd/src/app/shared/models/site-customization.model.ts`
- `FrontEnd/src/app/layouts/navbar/`
- `FrontEnd/src/app/shared/components/carrusel/`

### Ajustes de esquema

- Tabla final del carrusel: `carrousel`.
- PK final del carrusel: `id_carrousel`.
- Tabla `navbar` extendida con `brand_text`.
- Migraciones asociadas:
  - `BackEnd/database/migrations/20260414_dashboard_personalizacion_sitio.sql`
  - `BackEnd/database/migrations/20260414_rename_carrousel_id_column.sql`

### Verificaciones registradas

- Verificacion del modelo backend con conexion real: OK.
- `GET /api/site-customization.php?scope=public`: HTTP 200 documentado.
- Validaciones de sintaxis PHP en archivos principales: OK.

### Observacion historica

- Quedo asentado un warning de editor en `FrontEnd/src/app/layouts/navbar/navbar.ts` sobre inyeccion de `SiteCustomizationService`, aunque el flujo publico se habia verificado como funcional en esa etapa.

## 16 de abril de 2026 - QA y estado listo para deploy

### Estado operativo documentado

- QA ejecutado con 7 casos pass.
- Build frontend documentado como exitoso.
- Proyecto marcado como listo para deploy.

### Flujos validados

- Roles 1, 2 y 3 con permisos correctos.
- Flujo crear oferta -> aprobar -> visualizar -> postular.
- Validaciones de CV por tipo y tamaño.
- Rechazo HTTP 403 en endpoints con roles incorrectos.

### Referencias

- `docs/QA_EJECUCION_16-ABRIL-2026.md`
- `docs/QA_ROLES_Y_BOLSA_TRABAJO.md`
- snapshots de deploy consolidados luego en `docs/deploy.md`

## 19 de abril de 2026 - Correcciones de bolsa y carreras

### Cambio funcional

- Se corrigio que una oferta deshabilitada reapareciera tras refrescar la vista admin.
- Se corrigio error 500 al mover materias entre carreras por mismatch de columna `id_carreraMateria`.
- Se corrigio problema de proxy local usando `127.0.0.1` en lugar de `localhost`.

### Superficies involucradas

- `FrontEnd/src/app/features/admin/gestion-ofertas/gestion-ofertas.ts`
- `BackEnd/models/Materia.php`
- `BackEnd/database/migrations/20260419_fix_carrera_materia_habilitado.sql`
- `FrontEnd/proxy.conf.json`

### Pendiente tecnico registrado

- Quedo sugerida una migracion similar para normalizar typo en `institucion_carrera.habiltado`.

### Referencia

- correcciones consolidadas en este historial tecnico

## Hitos estructurales vigentes

- El backend sigue el patron `api` para HTTP, `models` para SQL, `services` para integraciones y `config` para bootstrap tecnico.
- Los roles vigentes contrastados con el codigo son `1` AdministradorComunidad, `2` Alumno y `3` AdministradorIFTS.
- La documentacion de deploy y estado actual fue centralizada en `docs/` para reducir drift con archivos legacy de raiz.

## Relacion con otros markdown historicos

- `docs/historial-conversacion.md` conserva el detalle largo y contexto de negocio.
- `docs/HISTORIAL_CONVERSACION_2026-03-02.md` conserva el snapshot detallado de la etapa de gestion admin.
- Este archivo resume hitos tecnicos para lectura rapida y mantenimiento.