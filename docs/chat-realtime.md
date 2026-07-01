# Chat realtime de Comunidad IFTS

## Estado actual

El foro usa Firebase Realtime Database para dos flujos distintos: un chat realtime tipo WhatsApp y la actividad en vivo del tema.

- La pantalla visible del chat está en `FrontEnd/src/app/features/chat/chat.ts`.
- El motor de tiempo real del chat está en `FrontEnd/src/app/shared/services/foro-chat.service.ts`.
- La actividad del tema está en `FrontEnd/src/app/shared/services/foro-realtime.service.ts`.
- La navegación al chat sigue usando la ruta `/chat`.
- El acceso está restringido a usuarios autenticados por los guards de Angular.

## Tecnología usada

- Angular standalone en el frontend.
- Firebase Authentication anónima para acceso a Realtime Database.
- Firebase Realtime Database para mensajes, presencia y estado "escribiendo".

## Estructura lógica

El realtime maneja tres flujos principales:

- Mensajes en tiempo real.
- Presencia de usuarios conectados.
- Indicador de escritura.

Y el foro maneja otro canal realtime adicional:

- Eventos de temas y respuestas.
- Presencia por tema en `/foro/presence/tema_<id>/usuario_<id>`.

## Reglas recomendadas para Realtime Database

Ajustar estas reglas en la consola de Firebase o en el proyecto que administre la base:

```json
{
  "rules": {
    ".read": false,
    ".write": false,
    "foro": {
      "chat": {
        ".read": "auth != null",
        ".write": "auth != null"
      },
      "events": {
        ".read": "auth != null",
        ".write": "auth != null"
      },
      "presence": {
        ".read": "auth != null",
        ".write": "auth != null"
      }
    }
  }
}
```

La app escribe en `foro/chat/messages`, `foro/chat/presence`, `foro/events` y `foro/presence`, así que las reglas tienen que cubrir esa rama exacta.

## Cómo crear la base

1. Entrá a Firebase Console y abrí el proyecto `Comunidad IFTS`.
2. En el menú lateral, andá a `Build` > `Realtime Database`.
3. Si no existe la base, tocá `Create Database`.
4. Elegí la ubicación y arrancá en modo bloqueado, no en modo abierto.
5. En la pestaña `Rules`, pegá las reglas del archivo `database.rules.json`.
6. Guardá los cambios.
7. Andá a `Build` > `Authentication` > `Sign-in method`.
8. Activá `Anonymous` y guardá.
9. Probá con dos usuarios logueados al mismo tiempo desde dos navegadores o una ventana incógnito.

## Cómo probarlo

1. Iniciá sesión en la app con un usuario real.
2. Abrí la app en otra ventana o en incógnito con otro usuario.
3. Entrá a `/foro` desde ambas sesiones.
4. Escribí un mensaje desde una sesión.
5. Verificá que aparezca en la otra sesión casi al instante.
6. Revisá que la lista de conectados muestre ambos usuarios.
7. Escribí en el cuadro de texto y confirmá que el estado `escribiendo...` se vea en la otra ventana.

## Si algo falla

- Si no conecta, revisá que `Anonymous` esté habilitado en Authentication.
- Si no llegan mensajes, revisá que las reglas de Realtime Database permitan `auth != null` en la rama `foro/chat`.
- Si el chat carga vacío, confirmá que el usuario pasó por login en la app antes de entrar a `/foro`.

## Archivos creados en el repo

- `firebase.json`
- `database.rules.json`

## Nota operativa

El proyecto no incluye un `firebase.json`, así que esta configuración debe aplicarse en Firebase manualmente o en el repositorio donde se gestione el despliegue de Firebase.

## Observación importante

El chat usa los datos de sesión de la app para mostrar nombre, apellido e imagen de perfil, pero Firebase solo se usa como canal realtime.
