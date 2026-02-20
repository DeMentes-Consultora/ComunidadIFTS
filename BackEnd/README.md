# Backend - ComunidadIFTS

Backend PHP para la plataforma ComunidadIFTS.

## Requisitos

- PHP >= 7.4
- MySQL
- Composer

## Instalación

1. Instalar dependencias:
```bash
composer install
```

2. Configurar variables de entorno:
```bash
cp .env.example .env
```

3. Editar `.env` con tus credenciales de base de datos.

4. Importar la base de datos desde `database/comunidad_ifts.sql`.

## Estructura

```
BackEnd/
├── api/                    # Endpoints de la API
│   ├── instituciones.php
│   ├── guardar-institucion.php
│   ├── like-institucion.php
│   ├── carreras.php
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── config/                 # Configuración
│   ├── database.php
│   └── cors.php
├── models/                 # Modelos de datos
│   ├── Institucion.php
│   ├── Carrera.php
│   ├── Persona.php
│   └── Usuario.php
├── database/               # Scripts SQL
│   └── comunidad_ifts.sql
├── .env.example           # Template de variables de entorno
├── .gitignore
└── composer.json
```

## API Endpoints

### GET /api/instituciones.php
Obtiene todas las instituciones con sus carreras.

### POST /api/guardar-institucion.php
Crea una nueva institución.

**Body:**
```json
{
  "nombre": "IFTS 12",
  "direccion": "Calle ejemplo 123",
  "telefono": "1234-5678",
  "email": "info@ifts12.edu.ar",
  "sitio_web": "https://ifts12.edu.ar",
  "latitud": -34.6037,
  "longitud": -58.3816,
  "carreras": ["Análisis de Sistemas", "Desarrollo de Software"],
  "observaciones": "Instituto líder en tecnología"
}
```

### POST /api/like-institucion.php
Incrementa el contador de likes de una institución.

**Body:**
```json
{
  "id": 1
}
```

### GET /api/carreras.php
Obtiene todas las carreras disponibles.

### POST /api/login.php
Autentica un usuario habilitado y devuelve sus datos de sesión.

**Body:**
```json
{
  "email": "usuario@correo.com",
  "clave": "tu_clave"
}
```

### POST /api/register.php
Registra una nueva cuenta de alumno y devuelve la sesión iniciada.

**Body:**
```json
{
  "nombre": "Juan",
  "apellido": "Pérez",
  "dni": "12345678",
  "fecha_nacimiento": "2000-05-10",
  "telefono": "1112345678",
  "id_institucion": 3,
  "email": "juan.perez@correo.com",
  "clave": "secreto123",
  "confirmar_clave": "secreto123"
}
```

### POST /api/logout.php
Cierra la sesión activa en servidor.

**Respuesta exitosa:**
```json
{
  "success": true,
  "message": "Sesión cerrada correctamente"
}
```

## Servidor Local

Para desarrollo con PHP incorporado:
```bash
php -S localhost:8000 -t .
```

## Seguridad

- ✅ Credenciales en `.env` (no en código)
- ✅ PDO con prepared statements (previene SQL injection)
- ✅ CORS configurado
- ✅ Validación de métodos HTTP
- ✅ Login, registro y logout implementados
