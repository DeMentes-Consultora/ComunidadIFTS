# AGENTS.md — ComunidadIFTS

## Qué es esto

Angular 21 + backend PHP. Plataforma comunitaria para instituciones IFTS (mapa, auth, administración, bolsa de trabajo, perfiles, tienda).

## Comandos de desarrollo

### Frontend (Angular 21, Vitest)

```bash
cd FrontEnd
npm install          # primera vez o tras cambios de dependencias
ng serve             # servidor dev en localhost:4200, proxea /api → localhost:3000
ng test              # tests unitarios (Vitest)
ng build             # build producción → dist/ComunidadIFTS
```

Config del proxy: `FrontEnd/proxy.conf.json` — todas las requests `/api/*` se reenvían a `localhost:3000`.

### Backend (PHP 7.4+, MySQL, Composer)

```bash
cd BackEnd
cp .env.example .env          # configurar credenciales DB, SMTP, Cloudinary, Google
composer install              # instalar dependencias PHP
php -S localhost:8000 -t .    # servidor dev local
php -l api/file.php           # chequeo de sintaxis antes de commitear
```

Setup de DB: importar `BackEnd/database/comunidad_ifts.sql` en MySQL.

### Deploy (InfinityFree)

```bash
# Desde la raíz del repo:
prepare-deploy.bat              # deploy incremental (salta vendor/)
prepare-deploy.bat --with-vendor  # deploy completo incluyendo vendor/
```

El output va a `deploy-infinityfree/`. Editar `.env` en esa carpeta con credenciales reales de producción antes de subir por FTP.

## Arquitectura

### Capas del backend (separación estricta)

- `api/*.php` — Solo HTTP: leer request, validar, llamar modelos, devolver JSON. **Sin SQL.**
- `models/*.php` — Todo el SQL y la persistencia vive acá.
- `services/*.php` — Orquestación entre modelos y servicios externos (Cloudinary, Mail, Google).
- `config/` — Conexión a DB, CORS, config de mailer.

**Regla**: si ves `prepare()`, `query()` o SQL crudo en `api/`, es un bug.

### Estructura del frontend

```
FrontEnd/src/app/
├── features/        # módulos: home, admin, auth, bolsa-trabajo, etc.
├── shared/          # guards, servicios, modelos, componentes, estilos
├── layouts/         # wrappers de layout
├── app.routes.ts    # definición de rutas (routing standalone de Angular)
├── app.config.ts    # providers de la app
└── app.ts           # componente raíz
```

### Roles (por ID, no por nombre)

| ID | Rol | Acceso |
|----|-----|--------|
| 1 | AdministradorComunidad | `/admin/*` |
| 2 | Alumno | `/bolsa-trabajo`, `/perfil` |
| 3 | AdministradorIFTS | `/crear-oferta`, `/perfil-institucion` |

Los roles viven en la DB. Guards de rutas en `FrontEnd/src/app/shared/guards/`.

## Archivos clave

- `docs/estado-actual.md` — arquitectura actual, rutas, módulos, stack
- `docs/deploy.md` — guía de deploy y variables de entorno
- `BackEnd/docs/ARQUITECTURA_BACKEND.md` — reglas de capas del backend
- `BackEnd/docs/ENDPOINTS.md` — catálogo de endpoints
- `docs/roles.md` — definición de roles
- `BackEnd/.env.example` — todas las variables de entorno con valores por defecto

## Cuidados

- El `.env` del backend está en gitignored. Nunca commitear credenciales. Usar `.env.example` como plantilla.
- `vendor/` está en gitignored. Ejecutar `composer install` después de clonar.
- `node_modules/` del frontend está en gitignored. Ejecutar `npm install` después de clonar.
- El build de Angular usa `fileReplacements` para producción: `environment.ts` → `environment.prod.ts`.
- Presupuesto del build de producción: 1.05MB warning, 1.4MB error.
- `deploy-infinityfree/` es un snapshot del output compilado — mantenerlo sincronizado tras cambios en el frontend.
- `BackEnd/.htaccess` bloquea acceso a `.env`, `composer.json`, `composer.lock` en el servidor.
- El proxy solo funciona durante `ng serve`. En producción, frontend y backend se sirven del mismo host de InfinityFree.
- Los tests de componentes están deshabilitados por defecto (`skipTests: true` en schematics de angular.json).
