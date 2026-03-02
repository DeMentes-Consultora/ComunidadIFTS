<?php
/**
 * API: Gestión de Carreras y Materias
 * Endpoint: /api/gestion-carreras.php
 * - GET: listado de carreras con sus materias + materias disponibles
 * - POST: asociar/desasociar materia de carrera
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Materia.php';

session_start();

header('Content-Type: application/json');

function validarAccesoAdmin()
{
    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }

    $rolesPermitidos = [1, 3, 7];
    if (!isset($_SESSION['id_rol']) || !in_array((int)$_SESSION['id_rol'], $rolesPermitidos, true)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No tiene permisos para gestionar carreras']);
        exit;
    }
}

function obtenerEstadoGestion($pdo)
{
    $stmtCarreras = $pdo->query(
        "SELECT id_carrera, nombre_carrera
         FROM carrera
         WHERE cancelado = 0
         ORDER BY nombre_carrera ASC"
    );
    $carrerasRows = $stmtCarreras->fetchAll();

    $stmtMaterias = $pdo->query(
        "SELECT id_materia, nombre_materia
         FROM materia
         WHERE cancelado = 0
         ORDER BY nombre_materia ASC"
    );
    $materiasRows = $stmtMaterias->fetchAll();

    $stmtRelacion = $pdo->query(
        "SELECT
            cm.id_carrera,
            cm.id_materia,
            m.nombre_materia
         FROM carrera_materia cm
         INNER JOIN materia m ON m.id_materia = cm.id_materia
         WHERE cm.cancelado = 0
           AND m.cancelado = 0"
    );
    $relaciones = $stmtRelacion->fetchAll();

    $materiasPorCarrera = [];
    $materiasAsignadas = [];

    foreach ($relaciones as $relacion) {
        $idCarrera = (int)$relacion['id_carrera'];
        $idMateria = (int)$relacion['id_materia'];

        if (!isset($materiasPorCarrera[$idCarrera])) {
            $materiasPorCarrera[$idCarrera] = [];
        }

        $materiasPorCarrera[$idCarrera][] = [
            'id_materia' => $idMateria,
            'nombre_materia' => $relacion['nombre_materia'],
        ];

        $materiasAsignadas[$idMateria] = true;
    }

    $carreras = [];
    foreach ($carrerasRows as $carrera) {
        $idCarrera = (int)$carrera['id_carrera'];
        $carreras[] = [
            'id_carrera' => $idCarrera,
            'nombre_carrera' => $carrera['nombre_carrera'],
            'materias' => $materiasPorCarrera[$idCarrera] ?? [],
        ];
    }

    $materiasDisponibles = [];
    foreach ($materiasRows as $materia) {
        $idMateria = (int)$materia['id_materia'];
        if (!isset($materiasAsignadas[$idMateria])) {
            $materiasDisponibles[] = [
                'id_materia' => $idMateria,
                'nombre_materia' => $materia['nombre_materia'],
            ];
        }
    }

    return [
        'materias' => $materiasDisponibles,
        'carreras' => $carreras,
    ];
}

try {
    validarAccesoAdmin();

    $db = Database::getInstance();
    $pdo = $db->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        echo json_encode([
            'success' => true,
            'data' => obtenerEstadoGestion($pdo),
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

    if ($idCarrera <= 0 || $idMateria <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }

    if ($accion === 'asociar') {
        Materia::asociarACarrera($pdo, $idCarrera, $idMateria);
        echo json_encode(['success' => true, 'message' => 'Materia asociada correctamente']);
        exit;
    }

    if ($accion === 'desasociar') {
        Materia::desasociarDeCarrera($pdo, $idCarrera, $idMateria);
        echo json_encode(['success' => true, 'message' => 'Materia desasociada correctamente']);
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
