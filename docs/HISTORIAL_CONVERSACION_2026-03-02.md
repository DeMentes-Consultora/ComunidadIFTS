# Historial técnico de conversación

**Fecha:** 2 de marzo de 2026  
**Proyecto:** ComunidadIFTS

## 1) Alcance confirmado
Se confirmó que la implementación debía hacerse en `ComunidadIFTS` (no en `IFTS15`).

## 2) Requerimientos solicitados
- Gestión de instituciones con tabla ordenada por `id_institucion`.
- Columnas: `id_institucion`, `logo_institucion`, `nombre_institucion`, `direccion_institucion` y acciones.
- Acciones por íconos: eliminar y modificar.
- Al modificar: abrir formulario de institución con datos cargados, permitiendo editar todos los campos **excepto** nombre de institución y nombre de carrera.
- Nuevo endpoint de gestión de carreras:
  - Pantalla dividida en dos.
  - Derecha: carreras.
  - Izquierda: materias.
  - Asociación por Drag & Drop con Angular Material/CDK.
- Evaluación sobre refactor a Angular Signals.

## 3) FrontEnd implementado
### 3.1 Gestión de instituciones (admin)
- Se creó pantalla nueva:
  - `FrontEnd/src/app/features/admin/gestion-instituciones/gestion-instituciones.ts`
  - `FrontEnd/src/app/features/admin/gestion-instituciones/gestion-instituciones.html`
  - `FrontEnd/src/app/features/admin/gestion-instituciones/gestion-instituciones.css`
- Funcionalidad:
  - Tabla con columnas requeridas.
  - Orden por ID ascendente.
  - Botones de acción por íconos (`edit`, `delete`).
  - Carga/feedback con spinner y snackbars.

### 3.2 Formulario de institución en modo edición restringida
- Se ajustó:
  - `FrontEnd/src/app/shared/components/formulario-institucion/formulario-institucion.ts`
  - `FrontEnd/src/app/shared/components/formulario-institucion/formulario-institucion.html`
- Cambios:
  - Nuevo input para bloquear nombre/carreras en edición.
  - Bloqueo de `nombre_ifts` cuando corresponde.
  - Bloqueo de checkboxes de carreras en edición restringida.
  - Compatibilidad para uso en dialog y embebido.

### 3.3 Gestión de carreras y materias (admin)
- Se creó pantalla nueva:
  - `FrontEnd/src/app/features/admin/gestion-carreras/gestion-carreras.ts`
  - `FrontEnd/src/app/features/admin/gestion-carreras/gestion-carreras.html`
  - `FrontEnd/src/app/features/admin/gestion-carreras/gestion-carreras.css`
- Se creó servicio:
  - `FrontEnd/src/app/shared/services/gestion-carreras.service.ts`
- Funcionalidad:
  - Diseño en dos paneles (materias/carreras).
  - Drag & Drop con `@angular/cdk/drag-drop`.
  - Asociar/desasociar materia en backend.

### 3.4 Rutas
- Se actualizaron rutas admin en:
  - `FrontEnd/src/app/app.routes.ts`
- Rutas agregadas:
  - `/admin/gestion-instituciones`
  - `/admin/gestion-carreras`

### 3.5 Servicio de instituciones
- Se actualizó:
  - `FrontEnd/src/app/shared/services/instituciones.service.ts`
- Cambios:
  - Método para eliminar institución.
  - `withCredentials: true` en endpoints protegidos (guardar/actualizar/eliminar).

## 4) BackEnd implementado
### 4.1 Endpoints nuevos
- `BackEnd/api/eliminar-institucion.php`
  - Eliminación de institución y sus relaciones.
- `BackEnd/api/gestion-carreras.php`
  - GET: estado completo (carreras + materias libres + materias por carrera).
  - POST: `asociar` / `desasociar` materia-carrera.

### 4.2 Modelo nuevo
- `BackEnd/models/Materia.php`
  - Operaciones para listar, asociar y desasociar materias.

### 4.3 Migración SQL agregada
- `BackEnd/database/migrations/20260302_gestion_carreras_materias.sql`
  - Crea tablas `materia` y `carrera_materia` si no existen.

## 5) Decisión sobre Angular Signals
Se aplicó estrategia incremental:
- Las pantallas nuevas (`gestion-instituciones` y `gestion-carreras`) usan `signal` y `computed`.
- No se realizó refactor masivo de todo el proyecto para evitar riesgo y mantener entrega rápida.

## 6) Validaciones ejecutadas
- PHP lint:
  - `api/eliminar-institucion.php` ✅
  - `api/gestion-carreras.php` ✅
  - `models/Materia.php` ✅
- Build Angular:
  - `npm run build` ✅

## 7) Pendiente operativo
Para que gestión de carreras/materias funcione en todos los entornos:
1. Ejecutar migración SQL:
   - `BackEnd/database/migrations/20260302_gestion_carreras_materias.sql`
2. Verificar permisos de sesión/rol en entorno real para endpoints admin.

## 8) Resultado
Se dejó implementada la gestión solicitada en `ComunidadIFTS` con:
- Tabla de instituciones con acciones de modificar/eliminar.
- Edición restringida según requerimiento.
- Gestión carreras/materias con Drag & Drop.
- Endpoints backend y estructura de base para soportar el flujo.
