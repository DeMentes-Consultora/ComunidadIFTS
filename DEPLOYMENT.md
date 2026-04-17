# 🚀 Guía de Despliegue - ComunidadIFTS en InfinityFree

## 📋 Información de la Cuenta

- **Hosting**: InfinityFree
- **Dominio**: https://comunidadifts.infinityfreeapp.com
- **Base de Datos**: if0_41035439_comunidad_ifts
- **Usuario BD**: if0_41035439
- **Host BD**: sql113.infinityfree.com

---

## 🚀 Método Rápido (Recomendado)

### 1. Ejecutar Script de Preparación

Abre una terminal en la carpeta del proyecto y ejecuta:

```bash
prepare-deploy.bat
```

El script automáticamente:
- ✅ Instalará dependencias del Backend (Composer)
- ✅ Instalará dependencias del Frontend (npm)
- ✅ Compilará el Frontend para producción
- ✅ Creará la carpeta `deploy-infinityfree/` con todo listo
- ✅ Validará que exista `BackEnd/.env.production`
- ✅ Fallará si no puede copiar el Frontend compilado

Por defecto, el script NO copia `vendor/` si no necesitas redeploy completo de dependencias.

- Usa `prepare-deploy.bat` para deploy incremental.
- Usa `prepare-deploy.bat --with-vendor` para primer deploy o cuando cambien `composer.json` / `composer.lock`.

### 2. Configurar Contraseña

Edita el archivo `deploy-infinityfree/.env`:
- Busca la línea: `DB_PASS=`
- Agrega tu contraseña de MySQL de InfinityFree
- Ejemplo: `DB_PASS=miContraseña123`
- Guarda el archivo

### 3. Continuar con "Subir Archivos vía FTP"

---

## 📤 Subir Archivos vía FTP

### Configurar Cliente FTP (FileZilla)

1. Descarga FileZilla si no lo tienes: https://filezilla-project.org/
2. Abre FileZilla
3. Configura la conexión:
   - **Host**: `ftpupload.net` (o `ftp.infinityfree.com`)
   - **Usuario**: Tu usuario FTP (generalmente `epiz_XXXXXXXX`)
   - **Contraseña**: Tu contraseña FTP de InfinityFree
   - **Puerto**: `21`
4. Haz clic en "Conexión rápida"

### Subir los Archivos

1. En el panel derecho de FileZilla, navega a la carpeta `htdocs/`
2. Si generaste deploy con `--with-vendor`, puedes reemplazar todo `htdocs/`.
3. Si generaste deploy sin `vendor/`, NO elimines `htdocs/vendor/` del servidor.
4. En el panel izquierdo, navega a tu carpeta `deploy-infinityfree/`
5. Selecciona **TODO** el contenido de `deploy-infinityfree/`
6. Arrastra todos los archivos al panel derecho (`htdocs/`) y acepta reemplazar los existentes.
7. Espera a que termine la transferencia (puede tardar varios minutos)

**Archivos que deben estar en htdocs/:**
```
htdocs/
├── .env
├── .htaccess
├── index.html
├── main-*.js
├── polyfills-*.js
├── styles-*.css
├── vendor/
├── config/
├── api/
├── models/
└── check-server.php
```

Nota:
- `vendor/` debe estar en `htdocs/` solo en primer deploy o cuando cambien dependencias PHP.
- En deploys normales de código, puedes conservar el `vendor/` ya existente en el servidor.

---

## 🗄️ Importar Base de Datos

### 1. Acceder a phpMyAdmin

1. Ve al panel de control de InfinityFree (VistaPanel)
2. Busca la sección **MySQL Databases**
3. Haz clic en **phpMyAdmin**

### 2. Seleccionar Base de Datos

1. En el panel izquierdo de phpMyAdmin, haz clic en:
   `if0_41035439_comunidad_ifts`

### 3. Importar el SQL

1. Haz clic en la pestaña **Importar**
2. Haz clic en **Seleccionar archivo**
3. Navega a: `BackEnd/database/if0_41035439_comunidad_ifts.sql`
4. Haz clic en **Ejecutar** (botón al final de la página)
5. Espera a que termine la importación
6. Deberías ver un mensaje de éxito

---

## ✅ Verificación

### 1. Verificar Configuración del Servidor

Visita en tu navegador:
```
https://comunidadifts.infinityfreeapp.com/check-server.php
```

**Verifica que todo esté en verde:**
- ✅ PHP Version >= 7.4
- ✅ Extensiones PHP cargadas
- ✅ Archivos y carpetas existen
- ✅ Conexión a base de datos exitosa
- ✅ Tablas encontradas

### 2. Probar la API

Visita:
```
https://comunidadifts.infinityfreeapp.com/api/carreras.php
```

Deberías ver un JSON con las carreras disponibles.

### 3. Probar el Frontend

Visita:
```
https://comunidadifts.infinityfreeapp.com
```

Deberías ver:
- ✅ La página carga correctamente
- ✅ El mapa se muestra
- ✅ Las instituciones aparecen en el mapa
- ✅ Los filtros funcionan
- ✅ Los detalles de instituciones se muestran

---

## 🔒 Seguridad Post-Despliegue

### Después de verificar que todo funciona:

1. **Eliminar archivo de verificación**:
   - Conéctate vía FTP
   - Elimina `htdocs/check-server.php`

2. **Verificar protección del .env**:
   - Visita: `https://comunidadifts.infinityfreeapp.com/.env`
   - Debe mostrar **403 Forbidden** o **404 Not Found**

3. **Verificar protección de vendor/**:
   - Visita: `https://comunidadifts.infinityfreeapp.com/vendor/`
   - Debe mostrar **403 Forbidden**

---

## 🐛 Solución de Problemas

### Error 500 - Internal Server Error

**Causas posibles:**
- El archivo `.env` no existe o está mal configurado
- Falta la carpeta `vendor/`
- Permisos de archivos incorrectos

**Solución:**
1. Verifica que `.env` existe en `htdocs/`
2. Verifica que la contraseña en `.env` es correcta
3. Asegúrate de que `vendor/` esté completo
4. Revisa los logs de error en VistaPanel > Error Logs

### Base de datos no conecta

**Solución:**
1. Verifica las credenciales en `.env`:
   ```
   DB_HOST=sql113.infinityfree.com
   DB_USER=if0_41035439
   DB_PASS=TU_CONTRASEÑA
   DB_NAME=if0_41035439_comunidad_ifts
   ```
2. Confirma que la base de datos fue importada en phpMyAdmin
3. Verifica que tu contraseña no tenga caracteres especiales que causen problemas

### API devuelve errores CORS

**Solución:**
1. Verifica que `ALLOWED_ORIGINS` en `.env` incluye tu dominio
2. Asegúrate de que `config/cors.php` existe
3. Limpia la caché del navegador

### Frontend muestra página en blanco

**Causas posibles:**
- No se compiló correctamente el frontend
- Faltan archivos JavaScript o CSS
- URL de la API incorrecta

**Solución:**
1. Abre la consola del navegador (F12) y busca errores
2. Verifica que todos los archivos `.js` y `.css` estén en `htdocs/`
3. Confirma que `environment.prod.ts` tiene la URL correcta
4. Recompila el frontend: `ng build --configuration production`

### Imágenes o recursos no cargan

**Solución:**
1. Verifica que la carpeta `assets/` (si existe) esté subida
2. Revisa que las rutas en el código sean relativas, no absolutas

---

## 📊 Limitaciones de InfinityFree

Ten en cuenta estas limitaciones del hosting gratuito:

- **Hits diarios**: 50,000 por día
- **Ancho de banda**: Ilimitado (con Fair Use Policy)
- **Almacenamiento**: 5GB
- **Bases de datos**: 400MB por BD
- **Inodes**: 10,000 archivos
- **Tiempo de ejecución PHP**: 60 segundos
- **Tamaño de subida**: 10MB por archivo

---

## 🔄 Actualizar el Sitio

Para hacer cambios después del despliegue inicial:

1. Haz los cambios en tu código local
2. Vuelve a ejecutar `prepare-deploy.bat`
3. Si cambiaste dependencias PHP, ejecuta `prepare-deploy.bat --with-vendor`
4. Sube los archivos modificados vía FTP
4. Si cambiaste la base de datos, exporta e importa de nuevo

---

## ✅ Checklist Operativo (Auth + CORS + Mail)

Antes de dar por finalizado un despliegue, verificar:

- [ ] `environment.prod.ts` apunta a `https://comunidadifts.infinityfreeapp.com/api`
- [ ] `CORS_ALLOWED_ORIGINS` incluye el dominio del frontend (sin espacios extra)
- [ ] Login funciona y crea sesión (`/api/login.php` responde `success: true`)
- [ ] Endpoints protegidos responden con sesión activa (`/api/usuarios-pendientes.php`)
- [ ] Variables SMTP completas en `.env`:
   - [ ] `MAIL_HOST`
   - [ ] `MAIL_PORT`
   - [ ] `MAIL_USERNAME`
   - [ ] `MAIL_PASSWORD`
   - [ ] `MAIL_FROM_ADDRESS`
   - [ ] `MAIL_FROM_NAME`
   - [ ] `MAIL_ENCRYPTION`
   - [ ] `ADMIN_EMAIL`
- [ ] Registro de usuario notifica al admin por email
- [ ] Registro de usuario envía comprobante al usuario registrado
- [ ] Al registrar, la respuesta de `/api/register.php` devuelve:
   - [ ] `email_admin_notificado: true`
   - [ ] `email_usuario_notificado: true`
   - [ ] `warning: null`
- [ ] Aprobación/rechazo de usuario funciona y devuelve mensaje claro en frontend

---

## 📞 Recursos Adicionales

- **Panel de Control**: https://app.infinityfree.com
- **Foro de Soporte**: https://forum.infinityfree.com
- **Documentación**: https://forum.infinityfree.com/docs

---

## ✨ Checklist Final

- [ ] Script de preparación ejecutado
- [ ] Contraseña configurada en `.env`
- [ ] Archivos subidos vía FTP a `htdocs/`
- [ ] Base de datos importada en phpMyAdmin
- [ ] `check-server.php` muestra todo en verde
- [ ] API responde correctamente
- [ ] Frontend carga sin errores
- [ ] Funcionalidad probada (mapa, filtros, detalles)
- [ ] `check-server.php` eliminado
- [ ] Archivos sensibles protegidos

---

**¡Felicitaciones por el despliegue! 🎉**

*ComunidadIFTS - DeMentes Consultora © 2026*
