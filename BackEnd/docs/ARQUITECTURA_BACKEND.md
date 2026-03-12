# Arquitectura Backend (ComunidadIFTS)

Este documento define el estandar de arquitectura para el backend PHP.

## Objetivo

- Separar responsabilidades para facilitar mantenimiento, pruebas y escalabilidad.
- Evitar mezclar SQL con logica HTTP de endpoints.

## Capas

1. `api/` (Controlador HTTP)
- Lee request (`GET/POST/PUT`, body JSON, params).
- Valida entrada y permisos.
- Orquesta llamadas a modelos/servicios.
- Devuelve respuesta JSON + status code.
- No contiene SQL (`prepare`, `query`, `SELECT`, `INSERT`, `UPDATE`, `DELETE`).

2. `models/` (Acceso a datos)
- Contiene todo el SQL y la persistencia.
- Encapsula consultas, updates, inserts, soft delete y transacciones cuando corresponda.
- Expone metodos claros de dominio (ej.: `Usuario::obtenerPendientesAprobacion(...)`).

3. `services/` (Integraciones / casos de uso)
- Orquesta logica entre modelos y servicios externos (Cloudinary, Mail, Google, etc.).
- Evita SQL directo. Si necesita datos, usa modelos.

4. `config/`
- Conexion a base (`database.php`), CORS, mailer, configuracion global.

## Reglas obligatorias

1. SQL solo en `models/`.
2. Endpoints sin SQL inline.
3. Metodos de modelo con nombres de intencion (no genericos).
4. Validaciones de entrada y permisos en `api/`.
5. Mensajes y codigos HTTP consistentes.
6. Manejo de transacciones en operaciones compuestas.

## Convenciones sugeridas

- Nombres de metodos:
  - `obtener...` para lecturas
  - `crear...`/`guardar...` para alta
  - `actualizar...` para modificaciones
  - `eliminar...` o `softDelete...` para baja
  - `existe...` para checks booleanos
- Retornos:
  - Lecturas: `array|null` u objeto de modelo.
  - Escrituras: `bool`, `rowCount` o id creado segun necesidad.

## Checklist de PR (Backend)

Antes de aprobar un PR, verificar:

1. Ningun endpoint en `api/` contiene SQL inline.
2. Toda consulta nueva esta encapsulada en `models/`.
3. No hay duplicacion de SQL entre endpoints.
4. Se respetan permisos/roles en endpoints sensibles.
5. Errores usan status HTTP correcto (`400`, `401`, `403`, `404`, `409`, `500`).
6. Operaciones multi-tabla usan transaccion cuando corresponde.
7. `php -l` sin errores en archivos modificados.
8. `README`/docs actualizados si cambia comportamiento o arquitectura.

## Antipatrones a evitar

- `api/*.php` con `prepare()` o `query()`.
- Logica de negocio compleja en endpoints.
- Consultas repetidas copiadas y pegadas.
- Respuestas HTTP ambiguas (siempre `200` para errores).

## Estado actual

A la fecha, el patron fue aplicado en los endpoints de `BackEnd/api/`.
