# Estándar Angular tipo LaCanchita

Guía base para mantener todos los proyectos con la misma arquitectura, permisos y escalabilidad.

## 1) Estructura de carpetas

Usar esta base dentro de `src/app`:

- `features/` → pantallas por dominio (home, admin, auth, dashboard, etc.)
- `layouts/` → navbar, sidenav, footer, header
- `shared/components/` → componentes reutilizables
- `shared/services/` → servicios de API, sesión, utilidades
- `shared/guards/` → guards reutilizables
- `shared/interfaces/` o `shared/models/` → contratos tipados

## 2) Guards (regla clave)

Separar siempre:

- `authGuard`: valida autenticación (usuario logueado)
- `roleGuard`: valida autorización por rol (desde rutas)
- `adminGuard`/otros: opcionales, como alias semánticos, reutilizando `roleGuard`

Evitar lógica de permisos en componentes cuando puede resolverse en routing.

## 3) Ruteo recomendado

Patrón obligatorio para secciones protegidas:

- Ruta padre por dominio (ejemplo: `admin`)
- `canActivate: [authGuard, roleGuard]`
- `data: { roles: [ ... ] }` en la ruta protegida
- Hijos dentro de `children` para escalar sin repetir guards

Ejemplo conceptual:

- `path: 'admin'`
- `canActivate: [authGuard, roleGuard]`
- `data: { roles: [1] }`
- `children: ['gestion-usuarios', ...]`

## 4) Menú y UX de permisos

- Mostrar opciones del menú según rol (solo para mejorar UX)
- Nunca depender solo del menú para seguridad
- La seguridad real debe estar en los guards de rutas

## 5) Barrels (index.ts)

Crear siempre `index.ts` en carpetas compartidas:

- `shared/guards/index.ts`
- `shared/services/index.ts` (si aplica)
- `shared/components/index.ts` (si aplica)

Objetivo: imports limpios y consistentes.

## 6) Contratos y tipado

- Definir interfaces/modelos de usuario con `id_rol` y demás campos necesarios
- Evitar `any` en auth, rutas y guards
- Centralizar tipos de auth en un único archivo de modelos/interfaces

## 7) Checklist por cada nueva pantalla protegida

1. Crear componente en `features/...`
2. Agregar ruta dentro del módulo/rama adecuada (`admin`, `dashboard`, etc.)
3. Aplicar `authGuard` + `roleGuard` con `data.roles`
4. Agregar opción de menú condicional por rol
5. Verificar navegación directa por URL (debe bloquear si no corresponde)

## 8) Criterios de calidad mínimos

- Sin lógica duplicada de permisos
- Guards reutilizables
- Rutas agrupadas por dominio
- Imports centralizados por barrel
- Tipado estricto en autenticación y autorización

## 9) Regla de equipo (acordada)

A partir de ahora, todos los proyectos Angular se implementan con este patrón tipo LaCanchita, salvo excepción explícita del proyecto.
