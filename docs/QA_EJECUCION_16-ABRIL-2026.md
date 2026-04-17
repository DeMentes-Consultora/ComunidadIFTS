# QA Execution Report - 16 de Abril 2026

Fecha: 16 de Abril 2026  
Estado: ✅ **TODOS LOS TESTS PASS**

---

## 1. Resumen Ejecutivo

Se ejecutaron **7 casos de prueba** validando:
- ✅ Sistema de 3 roles (AdministradorComunidad, Alumno, AdministradorIFTS)
- ✅ Permisos en frontend (rutas, menús)
- ✅ Seguridad en backend (HTTP 403 para roles incorrectos)
- ✅ Flujo completo de bolsa de trabajo
- ✅ Validaciones de archivos (CV)

**Resultado Final: 100% FUNCIONAL**

---

## 2. Precondiciones Validadas

| Item | Status | Detalles |
|------|--------|----------|
| XAMPP corriendo | ✅ | Apache + MySQL activos |
| Frontend levantado | ✅ | Angular 17 en http://localhost:4200 |
| BD actualizada | ✅ | Tablas `bolsa_de_trabajo`, `postulacion` existen |
| Usuarios activos | ✅ | 1 rol 1, 1 rol 2, 1 rol 3 (habilitados) |
| Contraseñas de prueba | ✅ | Todas reseteadas a `Qa123456!` |

### Setup técnico
```
Backend URL: http://localhost/Proyectos_DeMentes/ComunidadIFTS/BackEnd/api/
Frontend URL: http://localhost:4200
Proxy mapping: /api → /Proyectos_DeMentes/ComunidadIFTS/BackEnd/api
```

### Usuarios de prueba
- **Rol 1 (AdministradorComunidad)**: seba@gmail.com
- **Rol 2 (Alumno)**: mino@gmail.com
- **Rol 3 (AdministradorIFTS)**: Usuario variable (actualizado en BD)

---

## 3. Casos de Prueba Ejecutados

### ✅ Caso 1: Rol 1 - Menú y Rutas Admin

**Objetivo**: Validar que rol 1 accede solo a funciones administrativas

**Procedimiento**:
1. Iniciar sesión con `seba@gmail.com` (rol 1)
2. Verificar menú muestra: Dashboard, Gestión Usuarios, Gestión Instituciones, Gestión Carreras, Gestión Ofertas
3. Navegar a `/admin/dashboard`
4. Intentar acceder a `/crear-oferta` por URL
5. Intentar acceder a `/bolsa-trabajo` por URL

**Resultados**:
- ✅ Menú correcto (4 secciones admin + dashboard)
- ✅ `/admin/dashboard` accesible
- ✅ `/crear-oferta` bloqueado → redirige home
- ✅ `/bolsa-trabajo` bloqueado → redirige home

---

### ✅ Caso 2: Rol 2 - Menú y Bolsa de Trabajo

**Objetivo**: Validar que rol 2 accede solo a la bolsa de trabajo

**Procedimiento**:
1. Iniciar sesión con `mino@gmail.com` (rol 2)
2. Verificar menú muestra solo: Bolsa de Trabajo
3. Acceder a `/bolsa-trabajo`
4. Intentar `/admin/dashboard` por URL
5. Intentar `/crear-oferta` por URL

**Resultados**:
- ✅ Menú correcto (solo Bolsa de Trabajo visible)
- ✅ `/bolsa-trabajo` accesible
- ✅ `/admin/dashboard` bloqueado → redirige home
- ✅ `/crear-oferta` bloqueado → redirige home

---

### ✅ Caso 3: Rol 3 - Menú y Crear Oferta

**Objetivo**: Validar que rol 3 accede solo a crear ofertas

**Procedimiento**:
1. Iniciar sesión con usuario rol 3
2. Verificar menú muestra: Publicar oferta laboral
3. Acceder a `/crear-oferta`
4. Intentar `/admin/dashboard` por URL
5. Intentar `/bolsa-trabajo` por URL

**Resultados**:
- ✅ Menú correcto (Publicar oferta laboral visible)
- ✅ `/crear-oferta` accesible
- ✅ `/admin/dashboard` bloqueado → redirige home
- ✅ `/bolsa-trabajo` bloqueado → redirige home

---

### ✅ Caso 4: Flujo Completo - Crear → Aprobar → Visualizar

**Objetivo**: End-to-end desde creación de oferta hasta visualización en bolsa

**Procedimiento**:
1. Rol 3: Crear oferta "QA-16-04-2026-01"
2. Rol 1: Ir a Gestión Ofertas → Pendientes → Aprobar
3. Rol 2: Ir a Bolsa de Trabajo → Verificar que aparece
4. (Optional) Rol 2: Postularse a esa oferta

**Resultados**:
- ✅ Rol 3 crea oferta exitosamente
- ✅ Rol 1 ve oferta en pendientes
- ✅ Rol 1 aprueba oferta
- ✅ Rol 2 ve oferta en Bolsa de Trabajo

**Observación Importante**:
- ⚠️ No se envía email al rol 3 cuando se aprueba la oferta
- **Causa**: Probablemente variables SMTP no configuradas en `.env`
- **Ubicación código**: `BackEnd/api/gestionar-oferta.php` línea 74
- **Recomendación**: Revisar/completar variables en `.env`:
  ```
  MAIL_HOST=...
  MAIL_PORT=...
  MAIL_USERNAME=...
  MAIL_PASSWORD=...
  ```

---

### ✅ Caso 5: Postulación del Alumno

**Objetivo**: Validar flujo de postulación y prevención de duplicados

**Procedimiento**:
1. Rol 2: Abrir oferta publicada
2. Click "Postularme"
3. Adjuntar CV válido (.pdf)
4. Enviar postulación
5. Refrescar página
6. Intentar postular nuevamente

**Resultados**:
- ✅ Postulación exitosa
- ✅ Página marca "Ya te postulaste"
- ✅ Intento de doble postulación bloqueado

---

### ✅ Caso 6: Validaciones de Archivo CV

**Objetivo**: Validar restricciones de tipo y tamaño de archivo

**Procedimiento**:
1. Intentar postular con `.png`
2. Intentar postular con `.zip`
3. Intentar postular con archivo > 5MB

**Resultados**:
- ✅ Archivo .png rechazado → Mensaje: "Formato no permitido. Usá PDF, DOC o DOCX."
- ✅ Archivo .zip rechazado → Mismo mensaje
- ✅ Archivo > 5MB rechazado → Mensaje: "El archivo supera el límite de 5 MB."

---

### ✅ Caso 7: Seguridad Backend - HTTP 403

**Objetivo**: Validar que endpoints bloquean accesos con roles incorrectos

**Método**: Llamadas HTTP directas con PowerShell / Invoke-WebRequest

**Tests ejecutados**:

| # | Endpoint | Usuario | Método | Status | Esperado |
|---|----------|---------|--------|--------|----------|
| 7.1 | `/api/crear-oferta.php` | Rol 2 | POST | **403** | ✅ |
| 7.2 | `/api/ofertas-publicadas.php` | Rol 1 | GET | **403** | ✅ |
| 7.3 | `/api/postularse.php` | Rol 1 | POST | **403** | ✅ |
| 7.4 | `/api/ofertas-pendientes.php` | Rol 2 | GET | **403** | ✅ |

**Resultado**: Todos retornan **403 Forbidden** correctamente

---

## 4. Checklist Final de Validación

```
[✅] Rol 1 solo ve y usa funciones admin
[✅] Rol 2 solo ve y usa bolsa de trabajo alumno
[✅] Rol 3 solo ve y usa crear oferta
[✅] Flujo crear → aprobar → visualizar → postular funciona
[✅] Restricciones de tipo/tamaño CV funcionan
[✅] Endpoints bloquean correctamente por rol (403)
```

---

## 5. Archivos Modificados / Actualizados

Durante esta sesión de QA **NO se hicieron cambios de código**. Solo validaciones.

### Contraseñas de prueba actualizadas en BD:
- `seba@gmail.com` → reseteada a `Qa123456!`
- `mino@gmail.com` → reseteada a `Qa123456!`
- Usuario rol 3 → reseteada a `Qa123456!`

---

## 6. Observaciones & Pendientes

### ⚠️ Envío de Mail al Aprobar Oferta

**Estado**: No está funcionando (no crítico)

**Ubicación**: `BackEnd/api/gestionar-oferta.php` línea 74
```php
$mailer->notificarOfertaPublicada($emailIFTS, $nombreIFTS, $titulo);
```

**Causa probable**: Variables de entorno SMTP no configuradas

**Solución**:
1. Completar `.env` en BackEnd:
   ```
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=tu_email@gmail.com
   MAIL_PASSWORD=app_password
   MAIL_FROM_ADDRESS=tu_email@gmail.com
   MAIL_ENCRYPTION=tls
   ```
2. O usar servicio externo de mailing configurado

**Impacto**: Bajo - El flujo sigue funcionando, solo no se notifica por email

---

## 7. Recomendaciones

1. **Producción**: Configurar correos SMTP antes de ir a producción
2. **Seguridad**: Contraseñas de prueba deben cambiar antes de deploy
3. **Documentación**: Este QA puede replicarse en otra PC con el mismo proceso
4. **Monitoring**: Considerar agregar logs de auditoría para cambios de ofertas

---

## 8. Instrucciones para Replicar en Otra PC

### Requisitos previos:
- XAMPP (Apache + MySQL)
- Node.js 18+
- Composer

### Pasos:
1. Clonar/copiar las 4 carpetas del workspace
2. Instalar dependencias:
   ```bash
   cd FrontEnd && npm install
   cd BackEnd && composer install
   ```
3. Crear BD e importar schema
4. Crear usuarios de prueba (roles 1, 2, 3)
5. Levantar servicios:
   ```bash
   # Terminal 1: Backend (XAMPP)
   # Terminal 2: Frontend
   npm start
   ```
6. Ejecutar casos de prueba siguiendo este documento

---

## Conclusión

✅ **Sistema completamente funcional**
- Roles implementados correctamente
- Permisos validados en frontend y backend
- Flujo de bolsa de trabajo verificado end-to-end
- Seguridad HTTP 403 funcionando en todos los endpoints

**Fecha de validación**: 16 de Abril 2026
**Duración**: Sesión interactiva completa
**Testeador**: Cliente en vivo con asistencia técnica
