-- ============================================================
-- Migracion: Eliminar tablas del chat antiguo
-- Fecha: 30/06/2026
-- Descripcion: foro_mensajes y foro_adjuntos pertenecen al
--              sistema de chat anterior, ahora migrado a Firebase.
-- ============================================================

DROP TABLE IF EXISTS `foro_adjuntos`;
DROP TABLE IF EXISTS `foro_mensajes`;
