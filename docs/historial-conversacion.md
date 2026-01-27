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
