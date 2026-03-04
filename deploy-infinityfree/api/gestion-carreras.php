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

        $stmtExiste = $pdo->prepare(
            "SELECT id_carrera, cancelado
             FROM carrera
             WHERE LOWER(TRIM(nombre_carrera)) = LOWER(TRIM(?))
             LIMIT 1"
        );
        $stmtExiste->execute([$nombreCarrera]);
        $rowExiste = $stmtExiste->fetch();

        if ($rowExiste && (int)$rowExiste['cancelado'] === 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'La carrera ya existe']);
            exit;
        }

        if ($rowExiste) {
            $stmtReactivar = $pdo->prepare(
                "UPDATE carrera
                 SET nombre_carrera = ?, cancelado = 0, habilitado = 1
                 WHERE id_carrera = ?"
            );
            $stmtReactivar->execute([$nombreCarrera, (int)$rowExiste['id_carrera']]);
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

        $stmtExiste = $pdo->prepare(
            "SELECT id_carrera
             FROM carrera
             WHERE id_carrera <> ?
               AND cancelado = 0
               AND LOWER(TRIM(nombre_carrera)) = LOWER(TRIM(?))
             LIMIT 1"
        );
        $stmtExiste->execute([$idCarrera, $nombreCarrera]);
        if ($stmtExiste->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Ya existe una carrera con ese nombre']);
            exit;
        }

        $stmtActualizar = $pdo->prepare(
            "UPDATE carrera
             SET nombre_carrera = ?
             WHERE id_carrera = ? AND cancelado = 0"
        );
        $stmtActualizar->execute([$nombreCarrera, $idCarrera]);

        if ($stmtActualizar->rowCount() === 0) {
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

        $pdo->beginTransaction();
        try {
            $stmtRelacion = $pdo->prepare(
                "UPDATE carrera_materia
                 SET cancelado = 1
                 WHERE id_carrera = ?"
            );
            $stmtRelacion->execute([$idCarrera]);

            $stmtInstitucion = $pdo->prepare(
                "UPDATE institucion_carrera
                 SET cancelado = 1
                 WHERE id_carrera = ?"
            );
            $stmtInstitucion->execute([$idCarrera]);

            $stmtCarrera = $pdo->prepare(
                "UPDATE carrera
                 SET cancelado = 1, habilitado = 0
                 WHERE id_carrera = ? AND cancelado = 0"
            );
            $stmtCarrera->execute([$idCarrera]);

            if ($stmtCarrera->rowCount() === 0) {
                $pdo->rollBack();
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'No se encontró la carrera']);
                exit;
            }

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Carrera eliminada correctamente']);
            exit;
        } catch (\Throwable $txe) {
            $pdo->rollBack();
            throw $txe;
        }
    }

    if ($accion === 'crear_materia') {
        $nombreMateria = obtenerNombreNormalizado($input, 'nombre_materia');
        if ($nombreMateria === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'El nombre de la materia es obligatorio']);
            exit;
        }

        $stmtExiste = $pdo->prepare(
            "SELECT id_materia, cancelado
             FROM materia
             WHERE LOWER(TRIM(nombre_materia)) = LOWER(TRIM(?))
             LIMIT 1"
        );
        $stmtExiste->execute([$nombreMateria]);
        $rowExiste = $stmtExiste->fetch();

        if ($rowExiste && (int)$rowExiste['cancelado'] === 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'La materia ya existe']);
            exit;
        }

        if ($rowExiste) {
            $stmtReactivar = $pdo->prepare(
                "UPDATE materia
                 SET nombre_materia = ?, cancelado = 0, habilitado = 1
                 WHERE id_materia = ?"
            );
            $stmtReactivar->execute([$nombreMateria, (int)$rowExiste['id_materia']]);
        } else {
            $stmtInsert = $pdo->prepare(
                "INSERT INTO materia (nombre_materia, habilitado, cancelado)
                 VALUES (?, 1, 0)"
            );
            $stmtInsert->execute([$nombreMateria]);
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

        $stmtExiste = $pdo->prepare(
            "SELECT id_materia
             FROM materia
             WHERE id_materia <> ?
               AND cancelado = 0
               AND LOWER(TRIM(nombre_materia)) = LOWER(TRIM(?))
             LIMIT 1"
        );
        $stmtExiste->execute([$idMateria, $nombreMateria]);
        if ($stmtExiste->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Ya existe una materia con ese nombre']);
            exit;
        }

        $stmtActualizar = $pdo->prepare(
            "UPDATE materia
             SET nombre_materia = ?
             WHERE id_materia = ? AND cancelado = 0"
        );
        $stmtActualizar->execute([$nombreMateria, $idMateria]);

        if ($stmtActualizar->rowCount() === 0) {
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

        $pdo->beginTransaction();
        try {
            $stmtRelacion = $pdo->prepare(
                "UPDATE carrera_materia
                 SET cancelado = 1
                 WHERE id_materia = ?"
            );
            $stmtRelacion->execute([$idMateria]);

            $stmtMateria = $pdo->prepare(
                "UPDATE materia
                 SET cancelado = 1, habilitado = 0
                 WHERE id_materia = ? AND cancelado = 0"
            );
            $stmtMateria->execute([$idMateria]);

            if ($stmtMateria->rowCount() === 0) {
                $pdo->rollBack();
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'No se encontró la materia']);
                exit;
            }

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Materia eliminada correctamente']);
            exit;
        } catch (\Throwable $txe) {
            $pdo->rollBack();
            throw $txe;
        }
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
