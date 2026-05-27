# Aprobacion de Usuarios en ComunidadIFTS

## Descripcion

El registro de usuario deja la cuenta en estado pendiente hasta que un administrador de comunidad la aprueba.

## Flujo

1. El usuario se registra.
2. La cuenta se crea con `habilitado = 0` y `cancelado = 0`.
3. El administrador recibe notificacion por email.
4. El administrador revisa el usuario desde `/admin/gestion-usuarios`.
5. Puede aprobar o rechazar.
6. Si se aprueba, el usuario ya puede iniciar sesion.

## Reglas de estado

- Pendiente: `habilitado = 0`, `cancelado = 0`
- Aprobado: `habilitado = 1`, `cancelado = 0`
- Rechazado: `habilitado = 0`, `cancelado = 1`

## Endpoints involucrados

- `GET /api/usuarios-pendientes.php`
- `PUT|POST /api/aprobar-usuario.php`
- `POST /api/register.php`
- `POST /api/login.php`

## Dependencias operativas

- SMTP configurado en `BackEnd/.env`
- PHPMailer instalado por Composer

## Observaciones

- El flujo funcional esta documentado como implementado.
- SMTP sigue siendo un pendiente operativo frecuente cuando el proyecto se despliega en otro entorno.

## Mantenimiento

Si cambia el flujo de aprobacion, actualizar tambien `docs/estado-actual.md`, `BackEnd/docs/ENDPOINTS.md` y la documentacion de deploy si impacta en variables de entorno.