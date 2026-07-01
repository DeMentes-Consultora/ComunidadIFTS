# Roles de ComunidadIFTS

## Sistema vigente

ComunidadIFTS utiliza un sistema de roles basado en IDs.

| ID | Nombre del Rol | Alcance principal |
|---|---|---|
| 1 | AdministradorComunidad | Control del sistema, gestion de usuarios, dashboard admin y gestion global |
| 2 | Alumno | Consumo de bolsa de trabajo y perfil de alumno |
| 3 | AdministradorIFTS | Publicacion de ofertas, perfil institucional y gestion vinculada a su institucion |

## Permisos funcionales principales

### Instituciones

- Crear y editar instituciones: roles `1` y `3`
- Solo lectura: resto de usuarios

### Bolsa de trabajo

- Crear oferta laboral: rol `3`
- Aprobar, rechazar y deshabilitar oferta: rol `1`
- Ver ofertas publicadas y postularse: rol `2`

### Foro

- Acceso a lista de temas, creación, respuestas, búsqueda y adjuntos: roles `1`, `2` y `3`
- Gestión de categorías y cierre automático de inactivos: rol `1`

### Administracion general

- Dashboard admin y gestion de usuarios: rol `1`

## Implementacion

### Frontend

Las rutas se protegen por rol desde `FrontEnd/src/app/app.routes.ts` con guards.

### Backend

Los permisos se validan por `id_rol` dentro de cada endpoint sensible en `BackEnd/api/`.

## Ventajas del esquema por ID

1. Consultas mas simples y performantes.
2. Menor riesgo de errores por nombres de rol hardcodeados.
3. Mayor estabilidad si cambia el label visible del rol.

## Regla de mantenimiento

Si se agrega o cambia un rol, actualizar este archivo, `docs/estado-actual.md` y la documentacion de endpoints que corresponda.