# Deploy a Servidor - 16 de Abril 2026

## Estado del Build
- ✅ Frontend: Build completado (1.00 MB, zero warnings)
- ✅ Backend: Listo para deploy (sin cambios en dependencias composer)
- ✅ QA: Todos los tests pass

---

## Archivos Generados para Deploy

### 2. Frontend (Lista lista)
**Ubicación local**: `ComunidadIFTS/deploy-infinityfree/`  
**Contenido**:
- ✅ Archivos compilados Angular (.js, .css)
- ✅ index.html (directamente en raíz, no en subcarpeta)
- ✅ 3rdpartylicenses.txt
- ✅ favicon.ico
- ✅ .env (configuración para servidor)
- ✅ .htaccess (rewrite rules)

### 2. Backend
**Ubicación local**: `ComunidadIFTS/BackEnd/`  
**Estructura**:
```
BackEnd/
├── api/                     ✅ Endpoints (sin cambios)
├── models/                  ✅ Modelos DB (sin cambios)
├── config/                  ✅ Configuración SMTP, DB, CORS (sin cambios)
├── database/                ✅ Migrations/schema
├── services/                ✅ Servicios auxiliares
├── scripts/                 ✅ Scripts de utilidad
├── composer.json            ✅ Dependencias (sin cambios)
├── composer.lock            ✅ Lock file (sin cambios)
└── .env                     ✅ Variables de entorno
├── .env.production          ✅ Template para servidor
└── vendor/                  ❌ NO INCLUIR (ejecutar composer install en servidor)
```

---

## Instrucciones de Deploy a Servidor

### Paso 1: Subir Frontend

1. Acceder a panel de control del hosting (cPanel, Hostinger, etc.)
2. Abrir File Manager
3. Navegar a carpeta pública (ej: `public_html/`)
4. Borrar contenido viejo
5. Subir contenido de `ComunidadIFTS/deploy-infinityfree/`:
   - Todos los `.js`, `.css`, `index.html`
   - Archivos `.txt` y `favicon.ico`
   - **IMPORTANTE**: `.env` y `.htaccess`
   
   **Nota**: Los archivos van directamente en `public_html/`, NO dentro de una subcarpeta `browser/`

### Paso 2: Subir Backend

1. En File Manager, crear carpeta (si no existe): `api/`
2. Subir contenido de `ComunidadIFTS/BackEnd/`:
   ```
   api/
   ├── api/
   ├── models/
   ├── config/
   ├── database/
   ├── services/
   ├── scripts/
   ├── composer.json
   ├── composer.lock
   └── .env
   ```

3. **NO subir carpeta `vendor/`**

### Paso 3: Instalar Dependencias en Servidor

1. Acceder a Terminal/SSH del servidor
2. Navegar a carpeta backend:
   ```bash
   cd /home/usuario/public_html/api
   # o
   cd /home/usuario/api
   ```

3. Instalar composer:
   ```bash
   composer install --no-dev
   ```
   
   > **Nota**: `--no-dev` instala solo dependencias de producción (más rápido)

### Paso 4: Verificar Variables de Entorno

**Frontend - `.env`**:
```
API_BASE_URL=https://tudominio.com/api/
```

**Backend - `.env`**:
```
APP_ENV=production
APP_DEBUG=false
DB_HOST=localhost
DB_USER=tu_usuario_db
DB_PASSWORD=tu_contraseña_db
DB_NAME=tu_bd
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_email@gmail.com
MAIL_PASSWORD=app_password_aqui
MAIL_FROM_ADDRESS=tu_email@gmail.com
MAIL_FROM_NAME=ComunidadIFTS
MAIL_ENCRYPTION=tls
ADMIN_EMAIL=admin@ejemplo.com
```

### Paso 5: Verificar Permisos y Carpetas

```bash
# En servidor, desde carpeta backend:
chmod 755 -R .
chmod 644 -R api/
chmod 644 -R models/
chmod 644 *.php
chmod 644 *.json

# Si existe carpeta logs:
mkdir -p logs/
chmod 777 logs/
```

### Paso 6: Pruebas Post-Deploy

1. **Frontend**: Abrir https://tudominio.com en navegador
   - Debe cargar login modal
   - Verificar consola (F12) sin errores de red

2. **Backend**: Probar endpoint de login
   ```bash
   curl -X POST https://tudominio.com/api/login.php \
     -H "Content-Type: application/json" \
     -d '{"email":"test@ejemplo.com","clave":"tu_contraseña"}'
   ```
   - Debe retornar JSON (success true o false, sin errores 500)

3. **Menú**: Iniciar sesión con cada rol
   - Rol 1: Ver admin menu
   - Rol 2: Ver bolsa de trabajo
   - Rol 3: Ver crear oferta

---

## Checklist Pre-Deploy

- [ ] Build frontend completado (dist/ con index.html)
- [ ] Backend carpeta preparada (sin vendor)
- [ ] Variables de entorno updated (.env backend)
- [ ] BD actualizada en servidor (schema + usuarios)
- [ ] Permisos de carpetas verificados
- [ ] .htaccess en lugar correcto
- [ ] DNS apuntando a servidor

---

## Checklist Post-Deploy

- [ ] Frontend carga sin errores (F12 consola)
- [ ] Login funciona (endpoint /api/login.php)
- [ ] Rol 1 ve admin menu
- [ ] Rol 2 ve bolsa de trabajo
- [ ] Rol 3 ve crear oferta
- [ ] Endpoints /api/ofertas-publicadas.php retorna datos
- [ ] Mensajes de error claros (no 500)

---

## Observación: Mail SMTP

Si en servidor los mails no se envían:
1. Verificar `.env` tiene credenciales SMTP correctas
2. Si usas Gmail:
   - Crear "App Password" (no contraseña regular)
   - Usar ese app password en MAIL_PASSWORD
3. Si usas otro proveedor:
   - Reemplazar MAIL_HOST y MAIL_PORT según docs del provider

---

## Archivos Importantes Versionados

**No incluir en deploy final**:
- ❌ node_modules/
- ❌ .git/
- ❌ vendor/ (usar composer install en servidor)
- ❌ src/ (solo después de ng build)
- ❌ *.spec.ts
- ❌ .gitignore

**Incluir siempre**:
- ✅ .env/.env.production
- ✅ .htaccess/.env.production (si existe)
- ✅ composer.json + composer.lock
- ✅ package.json (referencial)

---

## Rollback en Caso de Error

Si algo falla en el servidor:
1. Mantener backup anterior de archivos
2. Restaurar carpeta frontend anterior
3. Para backend: `git checkout api/` (si usas git) o restaurar desde backup
4. Si BD se daño: restaurar dump anterior

---

## Soporte & Debugging

**Si frontend no carga**:
```
1. Verificar .htaccess está presente
2. Revisar F12 → Network tab → ver 404 en requests
3. Verificar URL en .env coincide con dominio real
```

**Si backend no responde**:
```
1. PHP habilitado en servidor
2. curl -X GET https://tudominio.com/api/login.php debe devolver JSON
3. Si error 500: revisar error_log de Apache/PHP
4. Verificar composer install se ejecutó exitosamente
```

**Si BD no conecta**:
```
1. Verificar credenciales en .env
2. Probar conexión manual desde server: mysql -h HOST -u USER -p
3. Verificar BD existe
```

---

## Próximos Pasos

1. ✅ Compilar y preparar archivos (HECHO)
2. ⏳ Subir a hosting (PENDIENTE)
3. ⏳ Ejecutar composer install en servidor (PENDIENTE)
4. ⏳ Validar endpoints funcionan (PENDIENTE)
5. ⏳ Pruebas end-to-end en servidor (PENDIENTE)

---

**Fecha generación**: 16 de Abril 2026  
**Build Status**: ✅ LISTO PARA DEPLOY  
**Testeado**: ✅ QA PASS (7/7 casos)  
**Referencia QA**: Ver `QA_EJECUCION_16-ABRIL-2026.md`
