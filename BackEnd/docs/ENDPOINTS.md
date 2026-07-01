# Endpoints API (ComunidadIFTS)

Listado operativo de endpoints del backend en `BackEnd/api/` contrastado con el codigo vigente.

## Base URL

- Desarrollo: `/api`
- Produccion: `https://comunidadifts.infinityfreeapp.com/api`

## Roles vigentes

- `1`: AdministradorComunidad
- `2`: Alumno
- `3`: AdministradorIFTS

## Endpoints

| Endpoint | Metodo(s) | Requiere sesion | Roles | Descripcion |
|---|---|---|---|---|
| `/api/instituciones.php` | `GET` | No | - | Listado de instituciones |
| `/api/carreras.php` | `GET` | No | - | Listado de carreras |
| `/api/contacto.php` | `POST` | No | - | Envio de consultas desde el formulario de contacto |
| `/api/stats-publicas.php` | `GET` | No | - | Estadisticas publicas del sitio |
| `/api/login.php` | `POST` | No | - | Login de usuario |
| `/api/register.php` | `POST` | No | - | Registro de usuario |
| `/api/google-auth.php` | `POST` | No | - | Login o registro con Google |
| `/api/logout.php` | `POST` | Si | Cualquiera autenticado | Cerrar sesion |
| `/api/actualizar-foto-perfil.php` | `POST` | Si | Cualquiera autenticado | Subir o reemplazar foto de perfil |
| `/api/like-institucion.php` | `POST` | No | - | Incrementar likes de institucion |
| `/api/guardar-institucion.php` | `POST` | Si | `1`, `3` | Crear institucion |
| `/api/actualizar-institucion.php` | `PUT`, `POST` | Si | `1`, `3` | Actualizar institucion, incluido multipart |
| `/api/eliminar-institucion.php` | `POST` | Si | `1`, `3` | Eliminar institucion |
| `/api/gestion-carreras.php` | `GET`, `POST` | Si | `1`, `3` | Gestion de carreras, materias y asociaciones |
| `/api/site-customization.php` | `GET`, `POST` | Parcial | Publico para scope publico; `1` para cambios | Leer o actualizar personalizacion del sitio |
| `/api/dashboard-stats.php` | `GET` | Si | `1` | Estadisticas del dashboard administrativo |
| `/api/usuarios-pendientes.php` | `GET` | Si | `1` | Listado de usuarios pendientes |
| `/api/usuarios-registrados.php` | `GET` | Si | `1` | Listado de usuarios registrados |
| `/api/aprobar-usuario.php` | `PUT`, `POST` | Si | `1` | Aprobar o rechazar usuario |
| `/api/cambiar-rol-usuario.php` | `PUT`, `POST` | Si | `1` | Cambiar rol de un usuario |
| `/api/crear-oferta.php` | `POST` | Si | `3` | Crear oferta laboral en revision |
| `/api/ofertas-pendientes.php` | `GET` | Si | `1` | Listado de ofertas pendientes para aprobacion |
| `/api/gestionar-oferta.php` | `PUT`, `POST` | Si | `1` | Aprobar, rechazar o deshabilitar oferta |
| `/api/ofertas-publicadas.php` | `GET` | Si | `2` | Listado de ofertas visibles para alumnos |
| `/api/postularse.php` | `POST` | Si | `2` | Postularse a una oferta |
| `/api/cancelar-postulacion.php` | `POST` | Si | `2` | Cancelar u ocultar una postulacion propia |
| `/api/perfil-alumno.php` | `GET` | Si | `2` | Obtener datos del perfil de alumno |
| `/api/perfil-institucion.php` | `GET` | Si | `1`, `3` | Obtener datos del perfil o panel de institucion |
| `/api/actualizar-datos-academicos.php` | `PUT`, `POST` | Si | `2` | Actualizar carrera y ano del alumno |
| `/api/migrar-logos-cloudinary.php` | `GET` | No por sesion | Token `MIGRATION_TOKEN` | Migracion de logos a Cloudinary |
| `/api/foro-categorias.php` | `GET` | Si | `1`, `2`, `3` | Listar categorias del foro |
| `/api/foro-categorias-gestion.php` | `POST`, `PUT`, `DELETE` | Si | `1` | CRUD de categorias (solo admin) |
| `/api/foro-temas.php` | `GET` | Si | `1`, `2`, `3` | Listar/buscar temas del foro |
| `/api/foro-tema-crear.php` | `POST` | Si | `1`, `2`, `3` | Crear nuevo tema |
| `/api/foro-tema.php` | `GET`, `PUT`, `DELETE` | Si | `1`, `2`, `3` | Ver, editar, cerrar, fijar o eliminar tema |
| `/api/foro-respuestas.php` | `GET`, `POST` | Si | `1`, `2`, `3` | Listar/crear respuestas a un tema |
| `/api/foro-respuesta.php` | `PUT`, `DELETE` | Si | `1`, `2`, `3` | Editar o eliminar respuesta |
| `/api/foro-adjunto-subir.php` | `POST` | Si | `1`, `2`, `3` | Subir archivo adjunto (imagen/pdf/video) |
| `/api/foro-buscar.php` | `GET` | Si | `1`, `2`, `3` | Busqueda fulltext en temas y respuestas |
| `/api/foro-cerrar-inactivos.php` | `GET` | Si | `1` | Cerrar temas inactivos (7+ dias sin respuesta) |

## Notas

1. El frontend construye URLs contra `/api` en desarrollo.
2. Algunos endpoints aceptan `POST` como compatibilidad adicional a `PUT`.
3. `migrar-logos-cloudinary.php` se protege con query param `token`, no con sesion.
4. `actualizar-foto-perfil.php` espera `multipart/form-data` con el campo `foto_perfil`.
5. Este archivo debe mantenerse alineado con `BackEnd/api/` y con los roles documentados en `docs/roles.md`.
