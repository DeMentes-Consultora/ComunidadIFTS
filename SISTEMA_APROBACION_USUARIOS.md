# Sistema de Aprobación de Usuarios - ComunidadIFTS

## 📋 Descripción

Sistema completo de aprobación de usuarios con notificaciones por email. Cuando un usuario se registra, su cuenta queda **pendiente de aprobación** hasta que un administrador (rol ID 1) la apruebe.

## 🔄 Flujo de Trabajo

1. **Registro de Usuario**
   - Usuario completa formulario de registro
   - Sistema crea cuenta con `habilitado = 0` (pendiente)
   - Se envía email automático al administrador notificando nuevo registro
   - Usuario ve mensaje: "Tu solicitud está pendiente de aprobación"

2. **Notificación al Administrador**
   - Email con datos del nuevo usuario
   - Enlace directo al panel de gestión

3. **Revisión por Administrador**
   - Administrador accede a `/admin/gestion-usuarios`
   - Ve tabla con usuarios pendientes
   - Botones para Aprobar o Rechazar

4. **Aprobación/Rechazo**
   - **Aprobar**: `habilitado = 1` + email de bienvenida al usuario
   - **Rechazar**: `cancelado = 1` + email de notificación (opcional)

5. **Login de Usuario**
   - Si `habilitado = 0`: Error "Cuenta pendiente de aprobación"
   - Si `habilitado = 1`: Login exitoso

## 🛠️ Configuración del Sistema

### 1. Configurar Email (SMTP)

Edita el archivo `.env` en `BackEnd/.env`:

```env
# Configuración de Email (PHPMailer SMTP)
# Para Gmail: habilitar verificación 2 pasos y crear contraseña de aplicación
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-contraseña-de-aplicacion
MAIL_FROM_ADDRESS=tu-email@gmail.com
MAIL_FROM_NAME=ComunidadIFTS
MAIL_ENCRYPTION=tls
ADMIN_EMAIL=admin@comunidadifts.com
```

#### Generar Contraseña de Aplicación en Gmail:

1. Ve a [Cuenta de Google > Seguridad](https://myaccount.google.com/security)
2. Activa la **Verificación en 2 pasos**
3. Ve a [Contraseñas de aplicaciones](https://myaccount.google.com/apppasswords)
4. Genera una nueva contraseña para "Correo" en "Otro (nombre personalizado)"
5. Copia la contraseña de 16 caracteres y pégala en `MAIL_PASSWORD`

### 2. Instalar Dependencias

```bash
cd BackEnd
composer install
```

Esto instalará PHPMailer 6.9+ automáticamente.

### 3. Base de Datos

La tabla `usuario` ya tiene el campo `habilitado`:

```sql
CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(250) NOT NULL,
  `clave` varchar(250) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `id_persona` int(11) NOT NULL,
  `id_institucion` int(11) NOT NULL,
  `habilitado` int(11) NOT NULL DEFAULT 1,  -- 0 = pendiente, 1 = aprobado
  `cancelado` int(11) NOT NULL DEFAULT 0,   -- 0 = activo, 1 = rechazado
  `idCreate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_usuario`)
);
```

No se requieren modificaciones al schema.

## 📁 Archivos Creados/Modificados

### Backend (PHP)

#### Archivos Nuevos:
- ✅ `BackEnd/config/Mailer.php` - Clase para envío de emails
- ✅ `BackEnd/api/usuarios-pendientes.php` - API para obtener usuarios pendientes
- ✅ `BackEnd/api/aprobar-usuario.php` - API para aprobar/rechazar usuarios

#### Archivos Modificados:
- ✅ `BackEnd/api/register.php` - Crea usuarios con `habilitado=0`, envía email al admin
- ✅ `BackEnd/api/login.php` - Valida que `habilitado=1` antes de permitir login
- ✅ `BackEnd/composer.json` - Agregó dependencia `phpmailer/phpmailer: ^6.9`
- ✅ `BackEnd/.env` - Variables de configuración SMTP

### Frontend (Angular)

#### Archivos Nuevos:
- ✅ `FrontEnd/src/app/features/admin/gestion-usuarios/gestion-usuarios.ts`
- ✅ `FrontEnd/src/app/features/admin/gestion-usuarios/gestion-usuarios.html`
- ✅ `FrontEnd/src/app/features/admin/gestion-usuarios/gestion-usuarios.css`
- ✅ `FrontEnd/src/app/shared/guards/admin.guard.ts` - Protege rutas de admin

#### Archivos Modificados:
- ✅ `FrontEnd/src/app/app.routes.ts` - Agregó ruta `/admin/gestion-usuarios`
- ✅ `FrontEnd/src/app/layouts/navbar/navbar.html` - Botón "Gestión de Usuarios" (solo rol ID 1)
- ✅ `FrontEnd/src/app/layouts/navbar/navbar.ts` - Importó `RouterModule`
- ✅ `FrontEnd/src/app/layouts/navbar/navbar.css` - Estilos para botón admin
- ✅ `FrontEnd/src/app/shared/components/formulario-registro/formulario-registro.ts` - Maneja respuesta `pendiente_aprobacion`
- ✅ `FrontEnd/src/app/shared/components/formulario-login/formulario-login.ts` - Muestra mensaje de pendiente

## 🔐 Roles del Sistema

| ID  | Nombre                 | Permisos                                      |
|-----|------------------------|-----------------------------------------------|
| 1   | AdministradorComunidad | Gestión usuarios, edición IFTS, todas las funciones |
| 2   | Alumno                 | Solo lectura                                  |
| 3   | AdministradorIFTS      | Edición IFTS de su institución                |

## 🎯 Uso del Sistema

### Como Usuario Nuevo:

1. Ir a "Registrarse"
2. Completar formulario
3. Enviar → Ver mensaje "Pendiente de aprobación"
4. Esperar email de aprobación
5. Iniciar sesión

### Como Administrador:

1. Iniciar sesión con cuenta de rol ID 1
2. Click en botón "Gestión de Usuarios" (navbar)
3. Ver tabla de usuarios pendientes
4. **Aprobar**: Click en "Aprobar" → Usuario recibe email y puede entrar
5. **Rechazar**: Click en "Rechazar" → Usuario recibe notificación

## 📧 Plantillas de Email

### Email al Admin (Nuevo Registro)
- Asunto: "Nuevo usuario pendiente de aprobación - ComunidadIFTS"
- Datos: Nombre, Email, DNI, Institución
- Enlace: Acceso directo al panel

### Email al Usuario (Aprobado)
- Asunto: "¡Cuenta aprobada! - ComunidadIFTS"
- Mensaje: Bienvenida y link para iniciar sesión

### Email al Usuario (Rechazado)
- Asunto: "Solicitud de registro - ComunidadIFTS"
- Mensaje: Notificación de rechazo (con motivo opcional)

## 🧪 Pruebas

### Probar Email en Desarrollo:

1. Configura un email real en `.env`
2. Registra un usuario de prueba
3. Verifica que llegue email al `ADMIN_EMAIL`
4. Aprueba el usuario desde `/admin/gestion-usuarios`
5. Verifica email de aprobación al usuario

### Verificar Logs:

Los errores de email se guardan en PHP error log:

```bash
# Ver últimos errores
tail -f /xampp/apache/logs/error.log
```

## 🐛 Troubleshooting

### Email no se envía:

1. **Gmail bloqueando**: Genera contraseña de aplicación (ver arriba)
2. **Puerto bloqueado**: Prueba `MAIL_PORT=465` con `MAIL_ENCRYPTION=ssl`
3. **Firewall**: Asegúrate que puerto 587/465 esté abierto
4. **Credenciales incorrectas**: Verifica `MAIL_USERNAME` y `MAIL_PASSWORD`

### Usuario no puede iniciar sesión:

1. Verifica en DB: `SELECT habilitado FROM usuario WHERE email = 'usuario@email.com'`
2. Si `habilitado = 0`: Usuario NO aprobado aún
3. Si `cancelado = 1`: Usuario fue rechazado

### No aparece botón "Gestión de Usuarios":

1. Verifica rol del usuario: `SELECT id_rol FROM usuario WHERE email = 'admin@email.com'`
2. Debe ser `id_rol = 1` (AdministradorComunidad)

## 📚 APIs Documentadas

### GET `/api/usuarios-pendientes.php`

Obtiene lista de usuarios con `habilitado = 0`

**Requiere:** Autenticación + rol ID 1

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id_usuario": 123,
      "email": "usuario@ejemplo.com",
      "nombre": "Juan",
      "apellido": "Pérez",
      "dni": "12345678",
      "nombre_institucion": "IFTS 15",
      "fecha_registro_formateada": "15/02/2026 10:30"
    }
  ],
  "total": 1
}
```

### PUT `/api/aprobar-usuario.php`

Aprueba o rechaza un usuario

**Requiere:** Autenticación + rol ID 1

**Request:**
```json
{
  "id_usuario": 123,
  "aprobar": true,
  "motivo": "Motivo de rechazo" // opcional, solo para rechazar
}
```

**Response:**
```json
{
  "success": true,
  "message": "Usuario aprobado exitosamente",
  "aprobado": true
}
```

## 🚀 Deploy a Producción

1. **Actualizar `.env` producción**:
   - Cambiar `APP_ENV=production`
   - Configurar SMTP real (no Gmail desarrollo)
   - Actualizar `ADMIN_EMAIL` con email real

2. **Subir archivos**:
   - `BackEnd/config/Mailer.php`
   - `BackEnd/api/usuarios-pendientes.php`
   - `BackEnd/api/aprobar-usuario.php`
   - Archivos modificados listados arriba

3. **Verificar permisos**:
   ```bash
   chmod 644 BackEnd/.env
   chmod 755 BackEnd/api/*.php
   chmod 755 BackEnd/config/*.php
   ```

4. **Instalar dependencias en servidor**:
   ```bash
   cd BackEnd
   composer install --no-dev --optimize-autoloader
   ```

## 📝 Notas Adicionales

- El sistema NO almacena contraseñas en texto plano (usa `password_hash`)
- Los emails se envían de forma asíncrona (no bloquean el registro)
- Si falla el email, el registro se completa igual (se loguea error)
- Usuario rechazado: `cancelado = 1` (no se elimina de DB)
- El administrador puede ver historial en tabla `usuario`

## 📞 Soporte

Para problemas o dudas, revisar:
1. Logs de PHP: `/xampp/apache/logs/error.log`
2. Consola del navegador (F12)
3. Tabla `usuario` en DB para verificar estados

---

✅ **Sistema completamente funcional y listo para usar**
