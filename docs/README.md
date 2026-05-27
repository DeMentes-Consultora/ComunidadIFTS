# Documentacion ComunidadIFTS

Esta carpeta pasa a ser la fuente de verdad para la documentacion humana del proyecto.

La estructura fue alineada con el estandar compartido definido en `../../ESTANDAR_DOCUMENTACION_PROYECTOS.md`.

## Que vive aqui

- `estado-actual.md`: arquitectura real, stack, estructura, rutas, roles y modulos vigentes.
- `deploy.md`: guia vigente de despliegue, variables de entorno, checklist y problemas frecuentes.
- `historial-tecnico.md`: hitos tecnicos consolidados y decisiones relevantes por fecha.
- `auditoria-estado-2026-05-27.md`: corte de auditoria funcional sobre implementacion real versus estado documental.
- `roles.md`: definicion vigente de roles y permisos principales.
- `aprobacion-usuarios.md`: flujo de aprobacion manual de cuentas y dependencias operativas.
- `requerimientos-historicos.md`: pedidos funcionales viejos y backlog historico preservado.
- `QA_EJECUCION_16-ABRIL-2026.md`: ejecucion concreta de QA con 7 casos pass.
- `QA_ROLES_Y_BOLSA_TRABAJO.md`: guia manual de validacion por roles y bolsa de trabajo.
- `historial-conversacion.md`: snapshot historico largo con decisiones y cambios de sesiones.
- `HISTORIAL_CONVERSACION_2026-03-02.md`: snapshot de la etapa de gestion de instituciones y carreras.
- `LEAFLET_MARKERCLUSTER_GUIA.md`: referencia tecnica puntual del mapa.

## Documentacion historica o de apoyo

- `historial-conversacion.md` y `HISTORIAL_CONVERSACION_2026-03-02.md` se conservan como contexto historico extendido.
- Los QA siguen siendo documentos validos de comprobacion, pero no reemplazan `estado-actual.md` ni la auditoria reciente.

## Documentacion tecnica complementaria

- `../BackEnd/docs/ARQUITECTURA_BACKEND.md`: reglas de arquitectura backend.
- `../BackEnd/docs/ENDPOINTS.md`: catalogo operativo de endpoints.
- `BackEnd/README.md`: resumen tecnico del backend.

## Reglas de mantenimiento

- Si cambia el estado vigente del proyecto, actualizar primero `estado-actual.md`.
- Los markdown de raiz pueden seguir existiendo como snapshots o checklists operativos, pero no deben convertirse en una segunda fuente de verdad.
- Si una documentacion enumera roles, rutas o endpoints, debe contrastarse con el codigo real antes de asumir que sigue vigente.

## Mapa rapido

1. Empezar por `estado-actual.md`.
2. Ir a `auditoria-estado-2026-05-27.md` si queres saber rapido que esta implementado con mayor confianza.
3. Ir a `deploy.md` si la tarea es de despliegue o preparacion de entorno.
4. Ir a `historial-tecnico.md` si queres entender que cambios importantes se hicieron y cuando.
5. Ir a `roles.md` y `aprobacion-usuarios.md` si la tarea toca permisos o onboarding.
6. Ir a los QA si queres entender comportamiento validado por rol.
7. Ir a `BackEnd/docs/ENDPOINTS.md` si queres tocar APIs.