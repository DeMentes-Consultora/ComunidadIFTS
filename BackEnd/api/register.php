<?php
/**
 * API: Registro de usuario
 * Endpoint: POST /api/register.php
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Persona.php';
require_once __DIR__ . '/../models/Usuario.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit;
}

function calcularEdadDesdeFecha($fechaNacimiento) {
    $nacimiento = DateTime::createFromFormat('Y-m-d', $fechaNacimiento);
    if (!$nacimiento) {
        return null;
    }

    $hoy = new DateTime();
    return (int)$hoy->diff($nacimiento)->y;
}

function obtenerRolAlumno($pdo) {
    $sql = "SELECT id_rol
            FROM rol
            WHERE nombre_rol = 'Alumno'
              AND habilitado = 1
              AND cancelado = 0
            ORDER BY id_rol ASC
            LIMIT 1";

    $stmt = $pdo->query($sql);
    $row = $stmt->fetch();
    return $row ? (int)$row['id_rol'] : null;
}

try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $payload = json_decode(file_get_contents('php://input'), true);

    $nombre = trim($payload['nombre'] ?? '');
    $apellido = trim($payload['apellido'] ?? '');
    $dni = trim($payload['dni'] ?? '');
    $fechaNacimiento = trim($payload['fecha_nacimiento'] ?? '');
    $telefono = trim($payload['telefono'] ?? '');
    $email = trim($payload['email'] ?? '');
    $clave = trim($payload['clave'] ?? '');
    $confirmarClave = trim($payload['confirmar_clave'] ?? '');
    $idInstitucion = (int)($payload['id_institucion'] ?? 0);

    if (
        $nombre === '' || $apellido === '' || $dni === '' || $fechaNacimiento === '' ||
        $telefono === '' || $email === '' || $clave === '' || $confirmarClave === '' || $idInstitucion <= 0
    ) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email inválido']);
        exit;
    }

    if ($clave !== $confirmarClave) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']);
        exit;
    }

    if (strlen($clave) < 6) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
        exit;
    }

    if (!preg_match('/^\d{7,9}$/', $dni)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'DNI inválido']);
        exit;
    }

    $edad = calcularEdadDesdeFecha($fechaNacimiento);
    if ($edad === null || $edad < 16 || $edad > 99) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'La fecha de nacimiento no es válida']);
        exit;
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $pdo->beginTransaction();

    $stmtInst = $pdo->prepare("SELECT id_institucion FROM institucion WHERE id_institucion = ? AND habilitado = 1 AND cancelado = 0 LIMIT 1");
    $stmtInst->execute([$idInstitucion]);
    if (!$stmtInst->fetch()) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'La institución seleccionada no es válida']);
        exit;
    }

    if (Usuario::emailExiste($pdo, $email)) {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'El email ya está registrado']);
        exit;
    }

    if (Persona::dniExiste($pdo, $dni)) {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'El DNI ya está registrado']);
        exit;
    }

    $idRolAlumno = obtenerRolAlumno($pdo);
    if ($idRolAlumno === null) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'No existe un rol Alumno habilitado']);
        exit;
    }

    $persona = new Persona($apellido, $nombre, $edad, $dni, $fechaNacimiento, $telefono);
    if (!$persona->guardar($pdo)) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'No fue posible registrar la persona']);
        exit;
    }

    $usuario = new Usuario(
        $email,
        $clave,
        $persona->getIdPersona(),
        $idRolAlumno,
        $idInstitucion
    );

    if (!$usuario->guardar($pdo)) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'No fue posible registrar el usuario']);
        exit;
    }

    $pdo->commit();

    $usuarioCompleto = Usuario::buscarPorEmail($pdo, $email);

    $_SESSION['logged_in'] = true;
    $_SESSION['id_usuario'] = $usuarioCompleto->getIdUsuario();
    $_SESSION['email'] = $usuarioCompleto->getEmail();
    $_SESSION['id_rol'] = $usuarioCompleto->getIdRol();
    $_SESSION['id_persona'] = $usuarioCompleto->getIdPersona();
    $_SESSION['id_institucion'] = $usuarioCompleto->getIdInstitucion();
    $_SESSION['nombre'] = $usuarioCompleto->getNombre();
    $_SESSION['apellido'] = $usuarioCompleto->getApellido();

    echo json_encode([
        'success' => true,
        'message' => 'Registro correcto',
        'data' => $usuarioCompleto->toArray()
    ]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}
