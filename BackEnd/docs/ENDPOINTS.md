# Endpoints API (ComunidadIFTS)

Listado operativo de endpoints del backend (`BackEnd/api/`) con metodo HTTP y URL base.

## Base URL

- Desarrollo (Angular): `apiUrl = /api`
- Produccion: `apiUrl = https://comunidadifts.infinityfreeapp.com/api`

## Endpoints

| Endpoint | Metodo(s) | Requiere sesion | Roles | Descripcion |
|---|---|---|---|---|
| `/api/instituciones.php` | `GET` | No | - | Listado de instituciones |
| `/api/carreras.php` | `GET` | No | - | Listado de carreras |
| `/api/login.php` | `POST` | No | - | Login de usuario |
| `/api/register.php` | `POST` | No | - | Registro de usuario |
| `/api/google-auth.php` | `POST` | No | - | Login/registro con Google |
| `/api/actualizar-foto-perfil.php` | `POST` | Si | Cualquiera autenticado | Subir/reemplazar foto de perfil |
| `/api/logout.php` | `POST` | Si | Cualquiera autenticado | Cerrar sesion |
| `/api/like-institucion.php` | `POST` | No | - | Incrementar likes de institucion |
| `/api/guardar-institucion.php` | `POST` | Si | `1`, `7` | Crear institucion |
| `/api/actualizar-institucion.php` | `PUT`, `POST` | Si | `1`, `7` | Actualizar institucion (incluye multipart) |
| `/api/eliminar-institucion.php` | `POST` | Si | `1`, `3`, `7` | Eliminar institucion |
| `/api/usuarios-pendientes.php` | `GET` | Si | `1` | Listado de usuarios pendientes |
| `/api/aprobar-usuario.php` | `PUT`, `POST` | Si | `1` | Aprobar o rechazar usuario |
| `/api/gestion-carreras.php` | `GET`, `POST` | Si | `1`, `3`, `7` | Gestion de carreras, materias y asociaciones |
| `/api/migrar-logos-cloudinary.php` | `GET` | No (usa token) | Token `MIGRATION_TOKEN` | Migracion de logos a Cloudinary |

## Notas

1. En frontend, las URLs se construyen como `${environment.apiUrl}/archivo.php`.
2. Algunos endpoints aceptan `POST` como compatibilidad ademas de `PUT`.
3. `migrar-logos-cloudinary.php` se protege por query param `token` y no por sesion.
4. `actualizar-foto-perfil.php` espera `multipart/form-data` con el campo de archivo `foto_perfil`.
