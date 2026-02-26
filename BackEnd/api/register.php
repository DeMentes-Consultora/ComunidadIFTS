<?php
/**
 * API: Registro de usuario
 * Endpoint: POST /api/register.php
 * 
 * Sistema de Roles (por ID):
 * - ID 1: AdministradorComunidad (permisos totales)
 * - ID 2: Alumno regular (solo lectura por defecto)
 * - ID 3: Alumno no regular
 * - ID 4: Alumno recibido
 * - ID 7: AdministradorIFTS (puede editar IFTS)
 * 
 * Permisos de edición IFTS: Solo roles ID 1 y 7
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Persona.php';
require_once __DIR__ . '/../models/Usuario.php';

ini_set('display_errors', '0');
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

/**
 * Obtiene el ID del rol predeterminado para nuevos usuarios (Alumno regular)
 * ID 2 = Alumno regular
 */
function obtenerRolAlumno($pdo) {
    $idRolAlumno = 2; // ID del rol "Alumno regular"
    
    // Verificar que el rol existe y está habilitado
    $sql = "SELECT id_rol
            FROM rol
            WHERE id_rol = ?
              AND habilitado = 1
              AND cancelado = 0
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idRolAlumno]);
    $row = $stmt->fetch();
    return $row ? (int)$row['id_rol'] : null;
}

try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $rawBody = file_get_contents('php://input');
    $payload = json_decode($rawBody, true);

    if (!is_array($payload)) {
        if (!empty($_POST)) {
            $payload = $_POST;
        } else {
            $formPayload = [];
            parse_str((string)$rawBody, $formPayload);
            $payload = is_array($formPayload) ? $formPayload : null;
        }
    }

    if (!is_array($payload) || empty($payload)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos de registro inválidos']);
        exit;
    }

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
        echo json_encode(['success' => false, 'message' => 'El rol de usuario predeterminado (ID 2) no está disponible']);
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
        $idInstitucion,
        null,      // id_usuario
        0,         // habilitado = 0 (pendiente de aprobación)
        0          // cancelado = 0
    );

    if (!$usuario->guardar($pdo)) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'No fue posible registrar el usuario']);
        exit;
    }

    $pdo->commit();

    // Enviar email al administrador notificando el nuevo registro
    $emailAdminNotificado = false;
    $emailWarning = null;
    try {
        require_once __DIR__ . '/../config/Mailer.php';
        $mailer = new Mailer();
        
        // Obtener nombre de la institución
        $stmtInstNombre = $pdo->prepare("SELECT nombre_ifts FROM institucion WHERE id_institucion = ? LIMIT 1");
        $stmtInstNombre->execute([$idInstitucion]);
        $institucion = $stmtInstNombre->fetch();
        $nombreInstitucion = $institucion ? $institucion['nombre_ifts'] : 'No especificada';
        
        $datosUsuario = [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $email,
            'institucion' => $nombreInstitucion
        ];

        $emailAdminNotificado = $mailer->notificarNuevoRegistro($datosUsuario);
        if (!$emailAdminNotificado) {
            $emailWarning = $mailer->getLastError() ?: 'No se pudo enviar la notificación por email al administrador.';
            error_log('Registro exitoso sin email de notificación: ' . $emailWarning);
        }
    } catch (\Throwable $e) {
        // Log error pero no fallar el registro
        $emailWarning = 'Excepción enviando notificación: ' . $e->getMessage();
        error_log($emailWarning);
    }

    // NO establecer sesión - usuario debe esperar aprobación
    echo json_encode([
        'success' => true,
        'message' => 'Registro exitoso. Tu solicitud está pendiente de aprobación por el administrador. Recibirás un email cuando sea aprobada.',
        'pendiente_aprobacion' => true,
        'email_admin_notificado' => $emailAdminNotificado,
        'warning' => $emailWarning
    ]);
} catch (\Throwable $e) {
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
