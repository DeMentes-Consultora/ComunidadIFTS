<?php
/**
 * API: Actualizar institución existente
 * Endpoint: PUT /api/actualizar-institucion.php
 * 
 * Permisos: Solo roles ID 1 (AdministradorComunidad) y ID 7 (AdministradorIFTS)
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Institucion.php';

session_start();

header('Content-Type: application/json');

// Solo permitir PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar permisos: solo roles 1 y 7 pueden actualizar IFTS
$rolesPermitidos = [1, 7];
if (!in_array($_SESSION['id_rol'], $rolesPermitidos)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No tiene permisos para modificar instituciones']);
    exit;
}

try {
    // Obtener datos del body
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
        exit;
    }

    // Validar que se envíe el ID
    if (empty($input['id']) && empty($input['id_institucion'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El ID de la institución es requerido']);
        exit;
    }

    // Validar campos requeridos
    if (empty($input['nombre']) && empty($input['nombre_ifts'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
        exit;
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Obtener el ID (puede venir como 'id' o 'id_institucion')
    $idInstitucion = $input['id'] ?? $input['id_institucion'];
    
    // Verificar que la institución existe
    $stmt = $pdo->prepare("SELECT id_institucion FROM institucion WHERE id_institucion = ? LIMIT 1");
    $stmt->execute([$idInstitucion]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Institución no encontrada']);
        exit;
    }
    
    // Crear objeto Institución desde los datos
    $institucion = Institucion::desdeArray($input);
    $institucion->setId($idInstitucion);
    
    // Actualizar en la base de datos
    $institucion->actualizar($pdo);

    echo json_encode([
        'success' => true,
        'message' => 'Institución actualizada correctamente',
        'id' => $institucion->getId(),
        'data' => $institucion->toArray()
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar la institución',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}
