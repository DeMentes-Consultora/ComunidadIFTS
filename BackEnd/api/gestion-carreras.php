<?php
/**
 * API: Gestión de Carreras y Materias
 * Endpoint: /api/gestion-carreras.php
 * - GET: listado de carreras con sus materias + materias disponibles
 * - POST: asociar/desasociar materia de carrera + alta/edición/baja de carreras + alta/edición/baja de materias
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Materia.php';
require_once __DIR__ . '/../models/Carrera.php';

session_start();

header('Content-Type: application/json');

function validarAccesoAdmin()
{
    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }

    $rolesPermitidos = [1, 3];
    if (!isset($_SESSION['id_rol']) || !in_array((int)$_SESSION['id_rol'], $rolesPermitidos, true)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No tiene permisos para gestionar carreras']);
        exit;
    }
}

function obtenerNombreNormalizado(array $input, string $clave): string
{
    $valor = trim((string)($input[$clave] ?? ''));
    return preg_replace('/\s+/', ' ', $valor) ?? '';
}

try {
    validarAccesoAdmin();

    $db = Database::getInstance();
    $pdo = $db->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        echo json_encode([
            'success' => true,
            'data' => Materia::obtenerEstadoGestion($pdo),
        ]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $accion = $input['accion'] ?? '';
    $idCarrera = (int)($input['id_carrera'] ?? 0);
    $idMateria = (int)($input['id_materia'] ?? 0);

    if ($accion === 'asociar') {
        if ($idCarrera <= 0 || $idMateria <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            exit;
        }

        Materia::asociarACarrera($pdo, $idCarrera, $idMateria);
        echo json_encode(['success' => true, 'message' => 'Materia asociada correctamente']);
        exit;
    }

    if ($accion === 'desasociar') {
        if ($idCarrera <= 0 || $idMateria <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            exit;
        }

        Materia::desasociarDeCarrera($pdo, $idCarrera, $idMateria);
        echo json_encode(['success' => true, 'message' => 'Materia desasociada correctamente']);
        exit;
    }

    if ($accion === 'crear_carrera') {
        $nombreCarrera = obtenerNombreNormalizado($input, 'nombre_carrera');
        if ($nombreCarrera === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'El nombre de la carrera es obligatorio']);
            exit;
        }

        $rowExiste = Carrera::existePorNombreIncluyendoCanceladas($pdo, $nombreCarrera);

        if ($rowExiste && (int)$rowExiste['cancelado'] === 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'La carrera ya existe']);
            exit;
        }

        if ($rowExiste) {
            Carrera::reactivarPorNombre($pdo, (int)$rowExiste['id_carrera'], $nombreCarrera);
        } else {
            $nuevaCarrera = new Carrera($nombreCarrera);
            $nuevaCarrera->guardar($pdo);
        }

        echo json_encode(['success' => true, 'message' => 'Carrera creada correctamente']);
        exit;
    }

    if ($accion === 'editar_carrera') {
        if ($idCarrera <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Carrera inválida']);
            exit;
        }

        $nombreCarrera = obtenerNombreNormalizado($input, 'nombre_carrera');
        if ($nombreCarrera === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'El nombre de la carrera es obligatorio']);
            exit;
        }

        $rowExiste = Carrera::existeActivaPorNombre($pdo, $nombreCarrera);
        if ($rowExiste && (int)$rowExiste['id_carrera'] !== $idCarrera) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Ya existe una carrera con ese nombre']);
            exit;
        }

        $rowCount = Carrera::actualizarNombre($pdo, $idCarrera, $nombreCarrera);
        if ($rowCount === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'No se encontró la carrera']);
            exit;
        }

        echo json_encode(['success' => true, 'message' => 'Carrera actualizada correctamente']);
        exit;
    }

    if ($accion === 'eliminar_carrera') {
        if ($idCarrera <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Carrera inválida']);
            exit;
        }

        $ok = Carrera::softDeleteConRelaciones($pdo, $idCarrera);
        if ($ok === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'No se encontró la carrera']);
            exit;
        }

        echo json_encode(['success' => true, 'message' => 'Carrera eliminada correctamente']);
        exit;
    }

    if ($accion === 'crear_materia') {
        $nombreMateria = obtenerNombreNormalizado($input, 'nombre_materia');
        if ($nombreMateria === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'El nombre de la materia es obligatorio']);
            exit;
        }

        $rowExiste = Materia::existePorNombreIncluyendoCanceladas($pdo, $nombreMateria);

        if ($rowExiste && (int)$rowExiste['cancelado'] === 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'La materia ya existe']);
            exit;
        }

        if ($rowExiste) {
            Materia::reactivarPorNombre($pdo, (int)$rowExiste['id_materia'], $nombreMateria);
        } else {
            Materia::crear($pdo, $nombreMateria);
        }

        echo json_encode(['success' => true, 'message' => 'Materia creada correctamente']);
        exit;
    }

    if ($accion === 'editar_materia') {
        if ($idMateria <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Materia inválida']);
            exit;
        }

        $nombreMateria = obtenerNombreNormalizado($input, 'nombre_materia');
        if ($nombreMateria === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'El nombre de la materia es obligatorio']);
            exit;
        }

        $rowExiste = Materia::existeActivaPorNombre($pdo, $nombreMateria);
        if ($rowExiste && (int)$rowExiste['id_materia'] !== $idMateria) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Ya existe una materia con ese nombre']);
            exit;
        }

        $rowCount = Materia::actualizarNombre($pdo, $idMateria, $nombreMateria);
        if ($rowCount === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'No se encontró la materia']);
            exit;
        }

        echo json_encode(['success' => true, 'message' => 'Materia actualizada correctamente']);
        exit;
    }

    if ($accion === 'eliminar_materia') {
        if ($idMateria <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Materia inválida']);
            exit;
        }

        $ok = Materia::softDeleteConRelaciones($pdo, $idMateria);
        if ($ok === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'No se encontró la materia']);
            exit;
        }

        echo json_encode(['success' => true, 'message' => 'Materia eliminada correctamente']);
        exit;
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Acción inválida']);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en gestión de carreras',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null,
    ]);
}
