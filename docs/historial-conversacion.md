# Historial de Conversación - Proyecto ComunidadIFTS

**Fecha de inicio:** 26 de enero de 2026

---

## Contexto del Proyecto

### Quiénes Somos
- 3 alumnos del terciario técnico IFTS12
- Objetivo: crear empresa propia
- Primer paso: crear comunidad de todos los IFTS mediante sitio web

### Problema Identificado
Los IFTS no tienen ningún lugar que los nuclee a todos. Muchos no tienen sitios web actualizados o redes sociales activas.

---

## Propuesta de Valor

### Para los Institutos (IFTS)
- Mayor visibilidad centralizada
- Posibilidad de publicidad (paga)
- Desarrollo de sitios web (servicio pago)

### Para los Estudiantes
- Información centralizada de todos los IFTS
- Mapa interactivo con ubicaciones
- Foro de la comunidad (futuro)
- Bolsa de trabajo (futuro)

### Usuarios Objetivo
- Estudiantes actuales
- Egresados
- Aspirantes
- Los institutos mismos (cada grupo tiene roles diferentes)

---

## Modelo de Negocio

### Fase 1 - MVP (Actual)
- **Gratuito:** Información básica de IFTS en el mapa
- **Pago:** 
  - Desarrollo de sitios web para IFTS que no tienen
  - Publicidad no gratuita (institutos pagan)

### Estrategia de Entrada
1. Presentar proyecto a directora del IFTS12 (gratuito para ellos)
2. IFTS12 hace de "gancho" para otros institutos
3. Validación del mercado con caso piloto

---

## MVP Definido (Enfoque Simple)

### ✅ Incluir en Primera Versión
- Mapa interactivo con ubicación de IFTS
- Información básica de cada instituto
- Sistema de likes/favoritos
- Cálculo de rutas

### ❌ NO Incluir Ahora (Viene Después)
- Foro (demasiado para arrancar)
- Bolsa de trabajo (responsabilidad legal compleja)
- Sitios web individuales para cada instituto

---

## Decisiones Técnicas

### Stack Tecnológico
- **Frontend:** Angular (versión moderna como LaCanchitaDeLosPibes)
- **Backend:** PHP con estructura organizada
- **Base de datos:** MySQL
- **Hosting inicial:** InfinityFree

### Estructura de Referencia
Usar como base el proyecto **LaCanchitaDeLosPibes** que ya tienen:

**Frontend:**
- Angular 20
- Estructura por features (admin, auth, dashboard, home, reservas)
- Environments para configuración
- SSR (Server Side Rendering)
- Material Design

**Backend:**
- Separación: Api / Controllers / Model / ConectionBD
- Dotenv para variables de entorno (.env)
- CORS configurado
- Composer para dependencias

---

## Problemas Críticos Detectados (Estado Actual)

### 🔴 SEGURIDAD - URGENTE
1. **Credenciales expuestas en GitHub**
   - Archivo: `conexion.php`
   - Contraseña en texto plano: `MapaPassIfts`
   - Host: `sql302.infinityfree.com`
   - **ACCIÓN:** Crear .env, cambiar contraseña, agregar .gitignore

2. **Sin autenticación**
   - Cualquiera puede agregar instituciones
   - Cualquiera puede dar infinitos likes
   - **SOLUCIÓN:** Panel admin con contraseña para aprobar instituciones

3. **Sin validación de archivos**
   - Subida de logos sin verificar tipo/tamaño
   - Riesgo de subir archivos maliciosos

### ⚠️ ARQUITECTURA
1. Archivos mezclados en raíz (index.html, *.php)
2. Carpetas BackEnd/FrontEnd existen pero tienen subcarpetas confusas (backend/frontend)
3. Sin separación clara de responsabilidades

---

## Plan de Acción Acordado

### Orden de Implementación
1. ✅ **Crear estructura BackEnd con seguridad (.env)** - 5 min
2. ✅ **Crear proyecto Angular en FrontEnd** - 10 min
3. **Migrar funcionalidad del mapa a Angular** - después

### Estructura Propuesta
```
/ComunidadIFTS
  /docs
    historial-conversacion.md (este archivo)
  /BackEnd
    /api
      obtener_instituciones.php
      guardar_institucion.php
      like_institucion.php
      obtener_carreras.php
    /config
      config.example.php (template sin credenciales)
    /controllers
    /models
    .env (NO commitear)
    .env.example
    composer.json
  /FrontEnd
    /src
      /app
        /features
        /layouts
        /shared
      /assets
      /environments
    angular.json
    package.json
  .gitignore
  README.md
```

---

## Notas Importantes

### Validación Pendiente
- ❓ No se ha contactado aún con la directora del IFTS12
- ❓ No hay cartas de intención de ningún IFTS
- ❓ Proyecto en construcción antes de validar demanda

### Competencia
- ✅ No existe nada similar para los IFTS en Argentina

### Próximos Pasos
1. Arreglar seguridad (URGENTE)
2. Reorganizar estructura de archivos
3. Preparar demo profesional para presentar a directora
4. Validar con IFTS12
5. Iterar según feedback

---

## Estado Actual del Código

### Archivos Existentes (Raíz del Proyecto)
- `index.html` - Mapa interactivo con Leaflet
- `conexion.php` - Conexión DB (⚠️ credenciales expuestas)
- `obtener_instituciones.php` - API para listar instituciones
- `obtener_carreras.php` - API para listar carreras
- `guardar_institucion.php` - API para crear instituciones
- `like_institucion.php` - API para dar likes

### Funcionalidades Implementadas
- ✅ Mapa interactivo con Leaflet
- ✅ Agregar instituciones en el mapa
- ✅ Sistema de likes
- ✅ Calcular rutas desde ubicación del usuario
- ✅ Geolocalización
- ✅ Responsive (detecta móviles)
- ✅ Relación N:N entre instituciones y carreras
- ✅ Subida de logos

### Cambios Implementados - 27 de enero de 2026 (Tarde)

#### ✅ Conexión Backend ↔ Frontend
- **Modelos TypeScript:** Interface `Institucion` y `Carrera` con tipos completos
- **Servicio InstitucionesService:** Métodos para obtener instituciones, dar likes y guardar
- **Environments:** Configuración de URLs de API (desarrollo y producción)
- **HttpClient:** Configurado en `app.config.ts` con `provideHttpClient()`
- **Componente Mapa:** Actualizado para consumir API real en lugar de datos hardcodeados
  - `ngOnInit()` carga instituciones desde el backend
  - Renderiza marcadores después de cargar datos
  - Manejo de estados de carga y errores
- **Servidor Backend:** PHP corriendo en `localhost:8000`
- **Base de Datos:** `comunidad_ifts_mapa` con 3 instituciones (IFTS 12, 20, 15)

#### 📝 Decisión de Arquitectura - Autenticación
Discusión sobre manejo de sesiones:
- ❌ **Sesiones PHP:** Stateful, complica CORS con Angular separado
- ✅ **JWT (Recomendado):** Stateless, token en `localStorage`, funciona perfecto con APIs REST

**Próxima implementación sugerida:**
1. Backend genera JWT al login
2. Frontend guarda token en localStorage
3. Cada petición incluye header `Authorization: Bearer {token}`
4. Backend valida token en cada endpoint protegido

### Cambios Implementados - 27 de enero de 2026 (Mañana)

#### ✅ Instalaciones y Configuraciones
- **Angular CLI global:** Instalado para usar comando `ng` desde cualquier lugar
- **Node portable:** Configurado en `C:\node` con PATH correcto
- **Leaflet + plugins:** Instalados para mapas interactivos (leaflet-routing-machine, geocoder)
- **Script start-dev.bat:** Creado para facilitar inicios del servidor

#### ✅ Componentes Creados

**1. Navbar (`src/app/layouts/navbar/`)**
- Marca "Comunidad IFTS" con icono de escuela
- Botón de menú (sidenav toggle)
- Botón de inicio de sesión
- Estilos verde Benetton (#006633)
- Responsive para móvil/desktop

**2. Sidenav (`src/app/layouts/sidenav/`)**
- Navegación con links: Home, Instituciones, Mapa, Acerca de, Contacto
- Header con logo y título
- Cierre al seleccionar un link
- Estilos coordinados con navbar

**3. Mapa (`src/app/shared/components/mapa/`)**
- Inicializa con Leaflet centrado en Buenos Aires
- Carga 3 IFTS de ejemplo (12, 20, 15) con marcadores circulares verdes
- Popups con info de cada institución (nombre, dirección, teléfono, sitio web)
- Responsive (zoom 11 móvil, 13 desktop)
- **TODO:** Conectar con API real `BackEnd/api/instituciones.php`

**4. Carrusel (`src/app/shared/components/carrusel/`)**
- **5 slides** con imágenes placeholder (Unsplash)
- Autoplay cada 5 segundos
- Botones prev/next para navegación manual
- Indicadores de puntos (se resaltan al interactuar)
- Transiciones suaves con animations
- **Nota para futuro:** Crear formulario CMS para gestionar slides dinamicamente

#### ✅ Integración en Home
- Home ahora contiene:
  1. Carrusel (arriba)
  2. Mapa (debajo)
- Estructura limpia sin márgenes adicionales
- Responsive en móvil

#### ✅ Estructura de Carpetas
```
FrontEnd/src/app/
├── layouts/
│   ├── navbar/
│   ├── sidenav/
│   └── footer/
├── shared/
│   └── components/
│       ├── mapa/
│       └── carrusel/
├── features/
│   ├── home/
│   ├── auth/
│   └── ...
└── app.ts (actualizado con sidenav integrado)
```

#### ✅ Rutas Actualizadas
- `/home` → Home con carrusel + mapa
- `/mapa` → Componente mapa standalone (reutilizable)
- Otras rutas sin cambios

#### 📝 Estado Actual
- **Servidor:** Ejecutando con `npm start` desde FrontEnd
- **URL base:** `http://localhost:4200`
- **Home visual:** Carrusel → Mapa integrados correctamente
- **Errores:** Resuelto GuestGuard no definido (retirado temporalmente con TODOs)

#### ⏭️ Próximos Pasos
1. ~~Crear servicio para consumir API de instituciones~~ ✅ HECHO
2. ~~Conectar mapa a datos reales desde BackEnd/api/instituciones.php~~ ✅ HECHO
3. Crear sistema de likes (backend + frontend)
4. Implementar autenticación básica con JWT
5. Crear panel admin para gestionar slides del carrusel
6. Validar con usuario (presentar a directora IFTS12)

---

## Cambios Implementados - 28 de enero de 2026

### 🐛 Problema Detectado
- Error `"node" no se reconoce como un comando interno o externo`
- **Causa:** Node.js portable en `C:\node` - PATH no configurado en algunas terminales
- **Contexto:** Node.js instalado como portable por restricciones de permisos (sin admin)

### ⚙️ Configuración Actual
- **Node.js portable:** `C:\node` (v24.13.0)
- **npm:** 11.6.2
- **PATH:** Configurado en terminal PowerShell principal
- **Restricción:** Sin permisos de administrador para instalación estándar
- **Terminal PowerShell:** Funcionando correctamente
- **Terminal "php":** Puede no tener PATH configurado para Node.js

### 📝 Nota Importante
Para nuevas terminales que no reconozcan `node`, asegurar que `C:\node` esté en el PATH de la sesión.

---

## Cambios Implementados - 26 de enero de 2026

### ✅ Frontend Angular Creado
- Proyecto Angular standalone configurado en `/FrontEnd`
- Angular Material instalado (tema Azure/Blue)
- Estructura de carpetas profesional:
  - `features/` (home, instituciones)
  - `layouts/` (header, footer, navbar)
  - `shared/` (services, models, components)
- Sin SSR (decisión MVP: más simple para comenzar)

### ✅ Backend PHP Reestructurado
- Estructura profesional en `/BackEnd`:
  - `api/` - Endpoints REST
  - `config/` - Configuración (database, cors)
  - `models/` - Lógica de negocio **con POO completa**
  - `database/` - Scripts SQL
- **Programación Orientada a Objetos:**
  - ✅ Clases con propiedades privadas
  - ✅ Getters y Setters para todos los atributos
  - ✅ Métodos de instancia (guardar, actualizar, eliminar)
  - ✅ Métodos estáticos (obtenerTodas, buscarPorId, desdeArray)
  - ✅ Método toArray() para serialización JSON
  - ✅ Encapsulación completa
- **Seguridad implementada:**
  - ✅ Credenciales en `.env` (ya NO en código)
  - ✅ `.gitignore` configurado (`.env` no se commitea)
  - ✅ PDO con prepared statements
  - ✅ CORS configurado
  - ✅ Validación de métodos HTTP
- Composer configurado con Dotenv
- API RESTful con respuestas JSON estandarizadas

### 📁 Estructura Final del Proyecto
```
/ComunidadIFTS
  /docs
    historial-conversacion.md
  /FrontEnd
    /src/app
      /features (home, instituciones)
      /layouts (header, footer, navbar)
      /shared (services, models, components)
    angular.json
    package.json
  /BackEnd
    /api
      instituciones.php
      guardar-institucion.php
      like-institucion.php
      carreras.php
    /config
      database.php
      cors.php
    /models
      Institucion.php
      Carrera.php
    /database
    .env (NO commitear - tiene contraseña)
    .env.example (template sin contraseña)
    .gitignore
    composer.json
    README.md
```

### ⚠️ IMPORTANTE - Seguridad
**ACCIÓN REQUERIDA:** Cambiar la contraseña de la base de datos en InfinityFree y actualizar el archivo `.env`.

El archivo `.env` tiene la contraseña actual pero **NO se subirá a GitHub** gracias al `.gitignore`.

### 🎯 Próximos Pasos
1. Crear componentes en Angular (mapa, listado instituciones)
2. Crear servicios para consumir la API
3. Integrar Leaflet en Angular
4. Testear endpoints del backend
5. Preparar demo para la directora

## Última Actualización
26 de enero de 2026 - Proyecto completamente reestructurado con frontend Angular y backend PHP profesional

---

## Cambios Implementados - 29 de enero de 2026

### ✅ Footer Angular Creado
- **Componente Footer:** Replicado del design de IFTS15 con 4 columnas
  1. **Columna 1:** Ubicación + Mapa embed de Google Maps
  2. **Columna 2:** Enlaces útiles (Inicio, Instituciones, Mapa, Contacto)
  3. **Columna 3:** Contacto (Horario, teléfono, email)
  4. **Columna 4:** Redes Sociales (YouTube, Facebook, Instagram)

- **Estilos implementados:**
  - Fondo oscuro (#343a40) como IFTS15
  - Títulos en dorado (#FFD700) con línea divisoria degradada debajo
  - Separador dorado degradado en secciones
  - **Iconos:** Material Icons para UI general + Bootstrap Icons para redes sociales (iconos oficiales de marcas)
  - **Hover effects en enlaces:** Subrayado animado (::before que crece 0→100%) + traslación horizontal 3px + color dorado (idéntico a IFTS15)
  - **Iconos de redes sociales con colores oficiales:**
    - YouTube: Icono `bi-youtube` en rojo #FF0000
    - Facebook: Icono `bi-facebook` en azul #1877F2
    - Instagram: Icono `bi-instagram` en rosa #E4405F
  - **Hover effects en redes sociales:**
    - YouTube: Fondo rojo + escala 1.1 + sombra brillante
    - Facebook: Fondo azul + escala 1.1 + sombra brillante
    - Instagram: Gradiente animado multicolor (naranja→rojo→rosa→violeta) + escala 1.1
  - Redes sociales compactas con separación reducida (gap: 4px)
  - Responsive grid (auto-fit) que se adapta a móvil/desktop
  - Animations suaves con transiciones CSS 0.3s ease

- **TypeScript:**
  - Método `openContactModal()` para futura integración de modal
  - `currentYear` dinámico en copyright
  - CommonModule importado

- **Integración:**
  - Footer ya está en `app.ts` y `app.html`
  - Listo para usar en la aplicación
  - **Estado:** Casi completado (pendiente: decidir si mantener mapa embed o cambiar funcionalidad)

- **Dependencias agregadas:**
  - Bootstrap Icons CDN 1.11.3 (para iconos oficiales de marcas)
  - Material Icons (para iconos generales de UI)

## Última Actualización
29 de enero de 2026 - Footer Angular implementado replicando diseño de IFTS15

---

## Despliegue en InfinityFree - 30 de enero a 15 de febrero de 2026

### 📋 Preparación del Despliegue

**Cuenta InfinityFree:**
- Usuario BD: `if0_41035439`
- Base de datos: `if0_41035439_comunidad_ifts`
- Host BD: `sql113.infinityfree.com`
- Dominio: `comunidadifts.infinityfreeapp.com`

### ✅ Archivos de Configuración Creados

#### 1. **Backend - Configuración de Producción**
- **`.env.production`**: Archivo de configuración con credenciales de InfinityFree
  - Configuración de BD (host, usuario, nombre, contraseña)
  - APP_ENV=production, APP_DEBUG=false
  - CORS_ALLOWED_ORIGINS con dominio de producción
  - Timezone: America/Argentina/Buenos_Aires

- **`.htaccess`**: Configuración Apache
  - Protección de archivos sensibles (.env, .git, composer.json)
  - Headers de seguridad (X-Content-Type-Options, X-Frame-Options, X-XSS-Protection)
  - Compresión GZIP para mejorar performance
  - Cache para archivos estáticos
  - Rewrite rules para Angular (SPA routing)

- **`check-server.php`**: Script de verificación del servidor
  - Verifica versión de PHP (>= 7.4)
  - Verifica extensiones PHP (mysqli, pdo, pdo_mysql, json, mbstring)
  - Verifica existencia de archivos y carpetas
  - Prueba conexión a base de datos
  - Cuenta tablas importadas
  - Interfaz visual con indicadores de éxito/error
  - **Nota:** Eliminar después del despliegue por seguridad

#### 2. **Frontend - Configuración de Producción**
- **`environment.prod.ts`**: URL de API de producción
  ```typescript
  apiUrl: 'https://comunidadifts.infinityfreeapp.com/api'
  ```

- **`angular.json`**: Agregado `fileReplacements`
  - Reemplaza `environment.ts` con `environment.prod.ts` en build de producción
  - **Problema resuelto:** Angular no estaba usando las URLs de producción

#### 3. **Scripts de Automatización**
- **`prepare-deploy.bat`** (Windows): Script automático que:
  1. Instala dependencias Backend (composer install --no-dev)
  2. Instala dependencias Frontend (npm install)
  3. Compila Frontend Angular para producción
  4. Crea carpeta `deploy-infinityfree/`
  5. Copia Backend (vendor, config, api, models, .htaccess, .env)
  6. Copia Frontend compilado (dist/browser)
  7. Genera archivo INSTRUCCIONES.txt

#### 4. **Documentación**
- **`DEPLOYMENT.md`**: Guía completa de despliegue
  - Configuración de cuenta InfinityFree
  - Pasos para importar base de datos
  - Instrucciones de FTP con FileZilla
  - Verificación del servidor
  - Solución de problemas comunes
  - Checklist final

### 🐛 Problemas Resueltos

#### Problema 1: Marcadores no aparecían en otros dispositivos
**Síntoma:** El mapa se veía pero sin marcadores institucionales desde PCs/móviles externos

**Causa raíz:** Error `localhost/.../instituciones.php Failed to load resource: net::ERR_CONNECTION_REFUSED`

**Diagnóstico:**
1. Intentaba conectar a localhost en lugar de dominio de producción
2. Angular NO estaba reemplazando archivos de environment en build de producción
3. Faltaba configuración `fileReplacements` en `angular.json`

**Solución:**
```json
// angular.json - configurations.production
"fileReplacements": [
  {
    "replace": "src/environments/environment.ts",
    "with": "src/environments/environment.prod.ts"
  }
]
```

#### Problema 2: Error ERR_NAME_NOT_RESOLVED
**Síntoma:** `GET https://comunidadifts.infinityfree.com/api/instituciones.php net::ERR_NAME_NOT_RESOLVED`

**Causa:** Dominio incorrecto (faltaba "app")
- ❌ `comunidadifts.infinityfree.com`
- ✅ `comunidadifts.infinityfreeapp.com`

**Solución:** Corregir URLs en todos los archivos de configuración

#### Problema 3: CORS bloqueando peticiones
**Causa:** Variable incorrecta en `.env`
- Backend buscaba: `CORS_ALLOWED_ORIGINS`
- Archivo tenía: `ALLOWED_ORIGINS`

**Solución:**
1. Renombrar variable a `CORS_ALLOWED_ORIGINS`
2. Mejorar `cors.php` para ser más permisivo en producción:
```php
// Permitir cualquier origen si está vacío o en desarrollo
// InfinityFree a veces causa problemas con CORS estricto
header("Access-Control-Allow-Origin: *");
```

#### Problema 4: Frontend no se copiaba a deploy
**Síntoma:** `index.html` y archivos JS/CSS no estaban en `deploy-infinityfree/`

**Causa:** Script de preparación no copiaba correctamente desde `dist/ComunidadIFTS/browser/`

**Solución:**
```bash
xcopy /E /Y "FrontEnd\dist\ComunidadIFTS\browser\*" "deploy-infinityfree\"
```

### 🔧 Mejoras de Debugging

**Logs en componente del mapa:**
```typescript
console.log('🔄 Iniciando carga de instituciones...');
console.log('✅ Instituciones recibidas:', instituciones.length);
console.log('🗺️ Renderizando instituciones en el mapa...');
console.log('❌ ERROR al cargar instituciones:', error);
```

**Alert en caso de error:**
```typescript
alert('Error al cargar instituciones. Ver consola para más detalles.');
```

### 📤 Proceso de Despliegue Final

1. **Preparación local:**
   - Ejecutar `prepare-deploy.bat`
   - Verificar que `.env` tiene contraseña configurada

2. **Subida vía FTP (FileZilla):**
   - Host: `ftpupload.net`
   - Puerto: 21
   - Subir TODO de `deploy-infinityfree/` a `htdocs/`
   - Sobrescribir archivos existentes

3. **Base de datos:**
   - Importar `if0_41035439_comunidad_ifts.sql` en phpMyAdmin
   - Base de datos ya estaba creada y poblada

4. **Verificación:**
   - Visitar `https://comunidadifts.infinityfreeapp.com/check-server.php`
   - Verificar API: `https://comunidadifts.infinityfreeapp.com/api/instituciones.php`
   - Probar frontend: `https://comunidadifts.infinityfreeapp.com`

5. **Seguridad post-despliegue:**
   - Eliminar `check-server.php` del servidor

### ✅ Estado Final

**Funcionando correctamente:**
- ✅ Mapa se visualiza desde cualquier dispositivo
- ✅ Marcadores de instituciones aparecen correctamente
- ✅ API responde con datos correctos
- ✅ CORS configurado correctamente
- ✅ Frontend y Backend integrados
- ✅ Accesible desde PCs, móviles y tablets
- ✅ URLs de producción configuradas
- ✅ Base de datos conectada

**Estructura en servidor:**
```
htdocs/
├── .env                    # Configuración producción
├── .htaccess              # Configuración Apache
├── index.html             # Frontend Angular
├── main-*.js              # JavaScript compilado
├── styles-*.css           # Estilos compilados
├── chunk-*.js             # Lazy loading chunks
├── vendor/                # Dependencias PHP (Composer)
├── config/
│   ├── database.php
│   └── cors.php
├── api/
│   ├── instituciones.php
│   ├── carreras.php
│   ├── guardar-institucion.php
│   └── like-institucion.php
├── models/
│   ├── Institucion.php
│   └── Carrera.php
└── media/                 # Imágenes Leaflet
```

### 🎓 Lecciones Aprendidas

1. **fileReplacements en Angular es CRÍTICO** para que use archivos de producción
2. **Dominio exacto es fundamental** (.infinityfreeapp.com vs .infinityfree.com)
3. **CORS debe configurarse cuidadosamente** en hosting gratuito
4. **Logs detallados ayudan enormemente** en debugging remoto
5. **Scripts de automatización ahorran tiempo** y evitan errores manuales
6. **Verificación sistemática** (check-server.php) facilita troubleshooting

### 📊 Métricas del Proyecto

**Backend:**
- PHP 7.4+
- Base de datos MySQL con tablas de instituciones y carreras
- API REST con 4 endpoints principales
- CORS configurado
- Validación de datos con PDO

**Frontend:**
- Angular 21
- Leaflet para mapas interactivos
- Material Design
- Build optimizado: ~428 KB (inicial) + chunks lazy (~406 KB)
- Responsive design

**Hosting:**
- InfinityFree (gratuito)
- 5GB almacenamiento
- 50,000 hits/día
- Base de datos 400MB

## Última Actualización
15 de febrero de 2026 - Proyecto desplegado exitosamente en InfinityFree y funcionando desde cualquier dispositivo
