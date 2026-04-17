# 📦 Ready for Deploy - Checklist

**Fecha**: 16 de Abril 2026  
**Build**: ✅ Angular compilado sin errores  
**QA**: ✅ 7/7 Casos pass  
**Status**: ✅ TODO LISTO PARA SUBIR AL SERVIDOR

---

## ✅ Frontend (LISTO)

**Carpeta**: `ComunidadIFTS/deploy-infinityfree/`

**Contenido para subir a servidor**:
```
deploy-infinityfree/
├── index.html                    ✅ (raíz, NO en carpeta anidada)
├── 3rdpartylicenses.txt         ✅
├── favicon.ico                   ✅
├── .env                          ✅ (EDIT con URL real del servidor)
├── .htaccess                     ✅
├── main-*.js                     ✅
├── styles-*.css                  ✅
└── chunk-*.js                    ✅ (todos los chunks generados)
```

**Nota**: La estructura ha sido simplificada. Anteriormente Angular 17 generaba `browser/` como carpeta anidada, pero para deploy se extrajo directamente a la raíz.

**Tamaño total frontend**: ~1.00 MB (comprimido ~250 KB)

**Acción**: Subir TODO el contenido de `deploy-infinityfree/` a `public_html/` del servidor (archivos en raíz, no dentro de subcarpeta)

---

## ✅ Backend (LISTO)

**Carpeta**: `ComunidadIFTS/BackEnd/`

**Contenido para subir a servidor**:
```
BackEnd/
├── api/                          ✅ (NO cambios desde QA)
├── models/                       ✅ (NO cambios)
├── config/                       ✅ (NO cambios)
├── database/                     ✅ (schemas + migrations)
├── services/                     ✅ (servicios auxiliares)
├── scripts/                      ✅ (utilidades)
├── composer.json                 ✅ (NO cambios)
├── composer.lock                 ✅ (NO cambios)
├── .env                          ✅ (EDIT con credenciales reales)
└── .htaccess                     ✅
```

**❌ NO incluir**: `vendor/` (ejecutar `composer install --no-dev` en servidor)

**Tamaño BackEnd (sin vendor)**: ~1.2 MB

**Acción**: Subir a carpeta `/api/` en servidor (crear si no existe)

---

## 📋 Pasos Inmediatos

### 1. Antes de Subir: Configurar Variables de Entorno

**Frontend** - Editar `deploy-infinityfree/.env`:
```
API_BASE_URL=https://comunidadifts.infinityfreeapp.com/api/
```

**Backend** - Editar `BackEnd/.env`:
```
APP_ENV=production
APP_DEBUG=false
DB_HOST=sql113.infinityfree.com
DB_USER=if0_41035439
DB_PASSWORD=[TU_PASSWORD_BD]
DB_NAME=if0_41035439_comunidad_ifts
MAIL_SMTP_HOST=smtp.gmail.com
MAIL_SMTP_PORT=587
MAIL_USERNAME=[TU_EMAIL@GMAIL.COM]
MAIL_PASSWORD=[APP_PASSWORD]
ADMIN_EMAIL=admin@ejemplo.com
```

### 2. Subir Archivos

**Opción A - Vía File Manager**:
1. Conectar a panel de hosting
2. Subir contenido de `deploy-infinityfree/` a `public_html/`
3. Crear carpeta `api/` y subir backend alli
4. Dar permisos 755 a directorios, 644 a archivos

**Opción B - Vía FTP**:
```bash
# Desde terminal local:
ftp conexion_hosting
put deploy-infinityfree/* /public_html/
put BackEnd/* /api/
```

**Opción C - Vía Git (si el servidor soporta)**:
```bash
cd /public_html
git clone [tu_repo] .
cd BackEnd
composer install --no-dev
```

### 3. En el Servidor: Instalar Dependencias

```bash
# SSH/Terminal del servidor
cd /home/usuario/public_html/api
composer install --no-dev
```

### 4. Verificar Permisos

```bash
# En servidor
chmod 755 -R .
chmod 644 api/*.php
chmod 644 config/*.php
chmod 644 models/*.php
# Si existe:
mkdir -p logs/
chmod 777 logs/
```

---

## 🧪 Tests Post-Deploy

**En cualquier navegador**:
1. https://comunidadifts.infinityfreeapp.com → Debe cargar login modal
2. Iniciar sesión → Verificar menu según rol
3. Probar cada funcionalidad (bolsa trabajo, crear oferta, aprobar, etc.)

**Desde terminal**:
```bash
# Test API
curl -X POST https://comunidadifts.infinityfreeapp.com/api/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"user@test.com","clave":"contrasena"}'
  
# Debe retornar JSON con success: true/false
```

---

## ⚠️ Pendientes / Observaciones

1. **IMPORTANTE**: Editar `.env` con valores reales del servidor ANTES de subir
2. **Mail SMTP**: Habrá que testear después (probablemente no está configurado)
3. **BD**: Debe ya existir en servidor con schema importado
4. **Usuarios de prueba**: Deben estar creados en BD del servidor

---

## 📞 Checklist Final ANTES de Subir

- [ ] `.env` frontend actualizado con URL correcta
- [ ] `.env` backend actualizado con credenciales BD
- [ ] `.env` backend actualizado con credenciales SMTP
- [ ] `deploy-infinityfree/` preparado (no incluye vendor, node_modules, .git)
- [ ] `BackEnd/` preparado (no incluye vendor)
- [ ] BD en servidor existente y actualizada
- [ ] Usuarios de prueba (rol 1, 2, 3) existen en BD servidor
- [ ] Permisos del servidor verificados (755 dirs, 644 files)

---

## 📚 Documentación Asociada

- **QA Ejecución**: `docs/QA_EJECUCION_16-ABRIL-2026.md`
- **QA Manual**: `docs/QA_ROLES_Y_BOLSA_TRABAJO.md`
- **Deploy Detallado**: `DEPLOY_INSTRUCCIONES_16-ABRIL-2026.md`
- **Roles Sistema**: `docs/SISTEMA_ROLES.md`

---

**Status Actual**: ✅ READY FOR PRODUCTION  
**Compilado**: 16 de Abril 2026  
**Testeado**: QA PASS (7/7)  
**Próximo paso**: Subir archivos a servidor InfinityFree
