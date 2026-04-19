# Resumen de Correcciones - Sesión de Debugging

## ✅ Problema 1: Ofertas laborales deshabilitadas reaparecen tras refresh
**Descripción:** Admin deshabilita una oferta en el panel de "Publicadas", pero al refrescar la página vuelve a aparecer.

**Causa raíz:** 
- El backend estaba correcto (UPDATE persiste en BD correctamente)
- El problema era en el FRONTEND: 
  - El objeto `oferta` mantiene su estado original (`habilitado=1`)
  - Cuando Angular refresca, el binding `[checked]="o.habilitado == 1"` hace que el toggle vuelva a ponerse checked
  - La lista no se limpiaba correctamente antes de recargar

**Solución implementada:**
- Cambio de estrategia en `togglePublicada()` de gestion-ofertas.ts
- Ahora utiliza **optimistic update**:
  1. Remueve la oferta de la lista inmediatamente cuando el usuario desliza el toggle
  2. Ejecuta el request al backend en paralelo
  3. Si tiene éxito: muestra mensaje confirmatorio
  4. Si falla: restaura la oferta a la lista y muestra error
- Ahora el usuario ve feedback inmediato y la oferta no reaparece

**Archivo modificado:** 
- [ComunidadIFTS/FrontEnd/src/app/features/admin/gestion-ofertas/gestion-ofertas.ts](ComunidadIFTS/FrontEnd/src/app/features/admin/gestion-ofertas/gestion-ofertas.ts#L165-L201)

---

## ✅ Problema 2: Error 500 al mover materias entre carreras
**Descripción:** 500 error cuando arrastra una materia entre carreras en gestion-carreras

**Causa raíz:** 
- La BD tiene columna PK con nombre `id_carreraMateria` (camelCase)
- El código consultaba `id_carrera_materia` (snake_case)
- Mismatch en nombre de columna

**Solución implementada:**
- Actualización de [ComunidadIFTS/BackEnd/models/Materia.php](ComunidadIFTS/BackEnd/models/Materia.php):
  - Línea 98: Cambio `id_carrera_materia` → `id_carreraMateria` en SELECT
  - Línea 110: Cambio en WHERE clause de UPDATE  
  - Línea 113: Cambio en array de resultado
  - Líneas 106-109: Remoción de columna `habilitado` del INSERT (simplificación)
  - Líneas 122-124: Remoción de `habilitado` del UPDATE (evita dependency en typos)
- Creación de migración idempotente para normalizar la columna

**Archivos afectados:**
- [ComunidadIFTS/BackEnd/models/Materia.php](ComunidadIFTS/BackEnd/models/Materia.php)
- [ComunidadIFTS/BackEnd/database/migrations/20260419_fix_carrera_materia_habilitado.sql](ComunidadIFTS/BackEnd/database/migrations/20260419_fix_carrera_materia_habilitado.sql)

---

## ✅ Problema 3: Error ECONNREFUSED en proxy Vite
**Descripción:** `AggregateError [ECONNREFUSED]` al intentar acceder a rutas `/api` desde el dev server

**Causa raíz:** 
- Target del proxy Vite usaba `localhost` que puede causar ambigüedad IPv6/IPv4 en Windows
- Conexión fallaba por resolución no determinística

**Solución implementada:**
- Cambio en [ComunidadIFTS/FrontEnd/proxy.conf.json](ComunidadIFTS/FrontEnd/proxy.conf.json):
  - Target cambiado de `http://localhost` → `http://127.0.0.1` (IPv4 explícito)
- Ahora el proxy se conecta confiablemente al backend en desarrollo

**Archivo modificado:**
- [ComunidadIFTS/FrontEnd/proxy.conf.json](ComunidadIFTS/FrontEnd/proxy.conf.json)

---

## ✅ Información solicitada: Ubicación de botón "Explorar" del carousel
**Descripción:** Usuario solicitó ubicación del texto "Explorar" para personalización

**Respuesta:**
- Archivo: [ComunidadIFTS/FrontEnd/src/app/shared/components/carrusel/carrusel.html](ComunidadIFTS/FrontEnd/src/app/shared/components/carrusel/carrusel.html#L9)
- Línea: 9
- Elemento: `<a [href]="currentSlide.enlace" class="btn-cta">Explorar</a>`
- El texto "Explorar" está hardcodeado en el template HTML

---

## 🔍 Diagnósticos ejecutados

### Tests realizados:
1. **debug_deshabilitar_oferta.php** - Verificación del flujo backend
   - ✓ Deshabilitación persiste en BD
   - ✓ Query de obtenerPublicadas() filtra correctamente
   - ✓ Oferta no reaparece tras deshabilitar

2. **test_flujo_completo.php** - Simulación completa del flujo frontend
   - ✓ Backend funciona correctamente en todos los pasos
   - ✓ Transacciones se completan exitosamente
   - ✓ Base de datos mantiene estado correcto

3. **test_http_request.php** - Simulación de solicitud HTTP PUT
   - ✓ Autenticación correcta
   - ✓ Validación de permisos OK  
   - ✓ UPDATE persiste correctamente
   - ✓ SELECT devuelve estado actualizado

### Verificaciones de código:
- ✓ Análisis de proxy.conf.json
- ✓ Análisis de models/BolsaTrabajo.php
- ✓ Análisis de api/gestionar-oferta.php
- ✓ Análisis de servicios Angular
- ✓ Análisis de templates HTML/templates

---

## 📋 Pendientes opcionales

### Normalizacion de columnas con typo
Similar al problema en carrera_materia, existe typo en institucion_carrera:
- Columna: `habiltado` (typo) debería ser `habilitado`
- Recomendación: Crear migración idempotente similar
- Archivo sugerido: `20260420_fix_institucion_carrera_habilitado.sql`

---

## 🧪 Validación recomendada

Para verificar que los problemas están resueltos:

1. **Test de ofertas laborales:**
   - [ ] Admin se loguea
   - [ ] Navega a gestion bolsa trabajo > Publicadas
   - [ ] Deshabilita una oferta → debería desaparecer inmediatamente
   - [ ] Refresca la página → oferta NO reaparece
   - [ ] En otra pestaña como alumno → oferta no está visible

2. **Test de carreras:**
   - [ ] Admin accede a gestion carreras
   - [ ] Arrastra una materia entre carreras
   - [ ] No debe haber error 500
   - [ ] Materia persiste en nueva carrera

3. **Test de proxy:**
   - [ ] Frontend dev server (npm start) se inicia sin errores ECONNREFUSED
   - [ ] Todas las llamadas a /api/* se conectan correctamente
