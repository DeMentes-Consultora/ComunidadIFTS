<?php
/**
 * API: Subir archivo adjunto al foro
 * Endpoint: POST /api/foro-adjunto-subir.php
 * Roles: 1 (AdminComunidad), 2 (Alumno), 3 (AdminIFTS)
 * Espera multipart/form-data con campo 'archivo'
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ForoTema.php';
require_once __DIR__ . '/../models/ForoRespuesta.php';
require_once __DIR__ . '/../models/ForoAdjunto.php';
require_once __DIR__ . '/../services/ForoMediaService.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'No autenticado']);
        exit;
    }

    $rol = (int)($_SESSION['id_rol'] ?? 0);
    if (!in_array($rol, [1, 2, 3])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
        exit;
    }

    $archivo = $_FILES['archivo'] ?? null;
    if (!$archivo) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No se envió ningún archivo (campo esperado: archivo)']);
        exit;
    }

    $idTema = isset($_POST['id_tema']) ? (int)$_POST['id_tema'] : null;
    $idRespuesta = isset($_POST['id_respuesta']) ? (int)$_POST['id_respuesta'] : null;

    if ($idTema === null && $idRespuesta === null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Se requiere id_tema o id_respuesta']);
        exit;
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Verificar que el tema/respuesta exista
    if ($idTema !== null) {
        $tema = ForoTema::obtenerPorId($pdo, $idTema);
        if (!$tema) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Tema no encontrado']);
            exit;
        }
    }

    if ($idRespuesta !== null) {
        $respuesta = ForoRespuesta::obtenerPorId($pdo, $idRespuesta);
        if (!$respuesta) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Respuesta no encontrada']);
            exit;
        }
    }

    // Subir archivo
    $mediaService = new ForoMediaService();
    $resultado = $mediaService->subir($archivo, $idTema, $idRespuesta);

    if (empty($resultado['success'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $resultado['message'] ?? 'Error al subir archivo'
        ]);
        exit;
    }

    // Registrar en base de datos
    $adjuntoId = ForoAdjunto::crear(
        $pdo,
        $resultado['tipo'],
        $resultado['url'],
        $resultado['public_id'],
        $resultado['nombre_original'],
        $resultado['tamano_bytes'],
        $idTema,
        $idRespuesta
    );

    if ($adjuntoId === null) {
        throw new RuntimeException('No se pudo registrar el adjunto en la base de datos');
    }

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Archivo subido correctamente',
        'adjunto' => [
            'id_adjunto' => $adjuntoId,
            'tipo' => $resultado['tipo'],
            'url' => $resultado['url'],
            'nombre_original' => $resultado['nombre_original'],
            'tamano_bytes' => $resultado['tamano_bytes']
        ]
    ]);
} catch (Throwable $e) {
    error_log('Error foro-adjunto-subir.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}
