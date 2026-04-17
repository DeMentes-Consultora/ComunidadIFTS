# QA Manual - Roles y Bolsa de Trabajo (3 Roles)

**Fecha creación**: 2026-04-16
**Estado**: ✅ **EJECUTADO Y PASS** (16-04-2026)
**Reporte**: Ver `QA_EJECUCION_16-ABRIL-2026.md` para detalles de ejecución en vivo

---

## Objetivo

Validar que el sistema respete el esquema real de roles:
- Rol `1`: AdministradorComunidad
- Rol `2`: Alumno
- Rol `3`: AdministradorIFTS

Y que la Bolsa de Trabajo funcione de punta a punta sin permisos cruzados.

## Precondiciones

1. Backend y frontend levantados.
2. Base de datos actualizada con el esquema vigente (incluyendo `postulacion`).
3. Existen al menos 3 usuarios activos (`habilitado = 1`, `cancelado = 0`):
   - 1 usuario rol 1
   - 1 usuario rol 2
   - 1 usuario rol 3

Consulta util para verificar usuarios disponibles:

```sql
SELECT u.id_usuario, u.email, u.id_rol, u.habilitado, u.cancelado, i.nombre_ifts
FROM usuario u
LEFT JOIN institucion i ON i.id_institucion = u.id_institucion
WHERE u.habilitado = 1 AND u.cancelado = 0
ORDER BY u.id_rol, u.id_usuario;
```

## Matriz esperada de permisos

- Rol 1 (AdminComunidad)
  - SI: `/admin/dashboard`, `/admin/gestion-usuarios`, `/admin/gestion-instituciones`, `/admin/gestion-carreras`, `/admin/gestion-ofertas`
  - NO: `/crear-oferta`, `/bolsa-trabajo`

- Rol 2 (Alumno)
  - SI: `/bolsa-trabajo`
  - NO: `/admin/*`, `/crear-oferta`

- Rol 3 (AdministradorIFTS)
  - SI: `/crear-oferta`
  - NO: `/admin/*`, `/bolsa-trabajo`

## Casos de prueba

### Caso 1 - Menu y rutas por rol 1

1. Iniciar sesion con usuario rol 1.
2. Abrir el menu lateral.
3. Verificar que aparecen accesos de administracion y "Bolsa de trabajo" (gestion-ofertas).
4. Navegar a cada ruta admin.
5. Intentar entrar manualmente a `/crear-oferta` y `/bolsa-trabajo`.

Resultado esperado:
- Accede correctamente a rutas admin.
- Es redirigido/bloqueado en rutas de rol 2 y 3.

### Caso 2 - Menu y rutas por rol 2

1. Iniciar sesion con usuario rol 2.
2. Abrir menu lateral.
3. Verificar que aparece solo acceso a "Bolsa de trabajo" (ademas de opciones publicas).
4. Entrar a `/bolsa-trabajo`.
5. Intentar `/admin/dashboard` y `/crear-oferta` por URL directa.

Resultado esperado:
- Puede entrar a `/bolsa-trabajo`.
- No puede entrar a rutas admin ni a crear oferta.

### Caso 3 - Menu y rutas por rol 3

1. Iniciar sesion con usuario rol 3.
2. Abrir menu lateral.
3. Verificar que aparece "Publicar oferta laboral".
4. Entrar a `/crear-oferta`.
5. Intentar `/admin/dashboard` y `/bolsa-trabajo` por URL directa.

Resultado esperado:
- Puede entrar a `/crear-oferta`.
- No puede entrar a rutas admin ni de alumno.

### Caso 4 - Flujo completo de oferta laboral

1. (Rol 3) Crear una oferta desde `/crear-oferta`.
2. Verificar mensaje de envio exitoso (estado pendiente).
3. (Rol 1) Ir a `/admin/gestion-ofertas` vista pendientes.
4. Aprobar la oferta.
5. (Rol 2) Ir a `/bolsa-trabajo` y confirmar que aparece la oferta.

Resultado esperado:
- La oferta pasa de pendiente a publicada.
- Alumno la visualiza despues de aprobarse.

### Caso 5 - Postulacion de alumno

1. (Rol 2) Abrir una oferta publicada.
2. Click en "Postularme".
3. Adjuntar CV valido (`.pdf`, `.doc` o `.docx`, <= 5 MB).
4. Enviar postulacion.
5. Refrescar pantalla.

Resultado esperado:
- Postulacion exitosa.
- La oferta queda marcada como "Ya te postulaste".
- No permite doble postulacion a la misma oferta.

### Caso 6 - Validaciones de archivo CV

1. Intentar postular con archivo invalido (`.png` o `.zip`).
2. Intentar postular con archivo > 5 MB.

Resultado esperado:
- El sistema rechaza ambos casos con mensaje claro.

### Caso 7 - Seguridad backend (pruebas de rechazo)

Con sesion activa, validar respuestas HTTP esperadas desde Postman/Insomnia:

- `POST /api/crear-oferta.php`
  - Rol 3: permitido
  - Rol 1 o 2: `403`

- `GET /api/ofertas-publicadas.php`
  - Rol 2: permitido
  - Rol 1 o 3: `403`

- `POST /api/postularse.php`
  - Rol 2: permitido
  - Rol 1 o 3: `403`

- `GET /api/ofertas-pendientes.php`
  - Rol 1: permitido
  - Rol 2 o 3: `403`

## Checklist rapido de aprobacion

- [ ] Rol 1 solo ve y usa funciones admin
- [ ] Rol 2 solo ve y usa bolsa de trabajo alumno
- [ ] Rol 3 solo ve y usa crear oferta
- [ ] Flujo crear -> aprobar -> visualizar -> postular funciona
- [ ] Restricciones de tipo/tamano CV funcionan
- [ ] Endpoints bloquean correctamente por rol (`403`)

## Observaciones

Si algun caso falla, registrar:
- Usuario y rol usado
- Ruta o endpoint
- Resultado obtenido
- Resultado esperado
- Captura o mensaje de error
