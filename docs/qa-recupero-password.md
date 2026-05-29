# QA Recupero de Contrasena

## Objetivo

Validar el flujo de recupero de contrasena implementado en ComunidadIFTS contemplando que algunos entornos locales o corporativos pueden bloquear SMTP saliente.

## Prerrequisitos

- Migracion aplicada: `BackEnd/database/migrations/20260529_password_resets.sql`
- `APP_URL` configurada hacia el frontend donde existe la ruta `/resetear`
- Variables SMTP configuradas en `BackEnd/.env`
- Al menos un usuario activo conocido en la tabla `usuario`

Nota para entorno local:

- Si `APP_ENV` no es `production`, el backend devuelve un `reset_link` de prueba y omite el envio real de email para poder validar el flujo sin depender de SMTP.

## Casos de prueba

### 1. Solicitud de recupero con email invalido

1. Abrir `/recuperar`.
2. Ingresar un formato de email invalido.
3. Verificar que el frontend no permita enviar el formulario.

Resultado esperado:

- Se muestra validacion de email invalido.

### 2. Solicitud de recupero con email inexistente

1. Abrir `/recuperar`.
2. Ingresar un email que no exista en la base.
3. Enviar formulario.

Resultado esperado:

- El backend responde de forma generica sin revelar si el usuario existe.
- No se muestra error tecnico al usuario.

### 3. Solicitud de recupero con usuario valido

1. Abrir `/recuperar`.
2. Ingresar un email activo existente.
3. Enviar formulario.

Resultado esperado:

- Se crea un registro en `password_resets` con `used = 0`.
- Se intenta enviar correo con enlace a `/resetear?token=...`.

Observacion:

- Si el correo no sale y el entorno es una PC corporativa o restringida, no concluir fallo de codigo sin repetir la prueba desde hosting o desde una red no restringida.
- En desarrollo local, la UI puede mostrar directamente el enlace de prueba para continuar el flujo sin correo.

### 4. Consumo de enlace valido

1. Abrir el enlace recibido por correo.
2. Verificar que cargue la pantalla `/resetear?token=...`.
3. Ingresar nueva contrasena y confirmacion.

Resultado esperado:

- El token se valida.
- La contrasena del usuario se actualiza.
- El token queda marcado como usado.

### 5. Token invalido o expirado

1. Abrir `/resetear?token=token-inexistente`.

Resultado esperado:

- Se informa que el enlace es invalido o expiro.
- No se muestra el formulario de cambio de contrasena.

### 6. Reutilizacion del token

1. Completar exitosamente el reseteo con un token valido.
2. Reabrir el mismo enlace.

Resultado esperado:

- El sistema rechaza reutilizar el token.

## Diagnostico rapido

Si falla el flujo, revisar en este orden:

1. Existe tabla `password_resets` y tiene la estructura esperada.
2. `APP_URL` apunta al frontend correcto.
3. SMTP configurado y con salida permitida por la red.
4. Logs de PHP/Apache para errores del Mailer.
5. Registro en `password_resets` creado o no creado para distinguir fallo de BD contra fallo de correo.

## Criterio de cierre

El flujo se considera validado cuando:

- Se genera token para usuario valido.
- Llega el correo o se documenta claramente que el bloqueo es del entorno.
- Se puede actualizar la contrasena desde el enlace.
- El token no puede reutilizarse.