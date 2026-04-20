<?php
/**
 * API: Postularse a una oferta laboral
 * Endpoint: POST /api/postularse.php (multipart/form-data)
 * Requiere: Autenticación + Rol alumno (2)
 *
 * Form fields:
 *   id_bolsaDeTrabajo  (int)
 *   cv                 (file: PDF/DOC/DOCX, máx 5 MB)
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/Mailer.php';
require_once __DIR__ . '/../models/BolsaTrabajo.php';
require_once __DIR__ . '/../models/Postulacion.php';
require_once __DIR__ . '/../models/Usuario.php';

header('Content-Type: application/json');

function uploadErrorText(int $code): string
{
    switch ($code) {
        case UPLOAD_ERR_OK:
            return 'OK';
        case UPLOAD_ERR_INI_SIZE:
            return 'El archivo supera upload_max_filesize';
        case UPLOAD_ERR_FORM_SIZE:
            return 'El archivo supera MAX_FILE_SIZE del formulario';
        case UPLOAD_ERR_PARTIAL:
            return 'El archivo se subio parcialmente';
        case UPLOAD_ERR_NO_FILE:
            return 'No se envio archivo';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Falta carpeta temporal en servidor';
        case UPLOAD_ERR_CANT_WRITE:
            return 'No se pudo escribir archivo temporal';
        case UPLOAD_ERR_EXTENSION:
            return 'Una extension de PHP bloqueo la subida';
        default:
            return 'Error de subida desconocido';
    }
}

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

    $idRol           = (int)($_SESSION['id_rol'] ?? 0);
    $rolesPermitidos = [2];

    if (!in_array($idRol, $rolesPermitidos)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Solo los alumnos pueden postularse a ofertas laborales']);
        exit;
    }

    $idOferta  = (int)($_POST['id_bolsaDeTrabajo'] ?? 0);
    $idUsuario = (int)($_SESSION['id_usuario'] ?? 0);

    if ($idOferta <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'id_bolsaDeTrabajo es obligatorio']);
        exit;
    }

    // Validar archivo CV
    if (empty($_FILES['cv']) || $_FILES['cv']['error'] === UPLOAD_ERR_NO_FILE) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El CV es obligatorio para postularse']);
        exit;
    }

    $archivoCV = $_FILES['cv'];

    if ($archivoCV['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Error al recibir el archivo del CV',
            'details' => [
                'upload_error_code' => (int)$archivoCV['error'],
                'upload_error_text' => uploadErrorText((int)$archivoCV['error']),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
            ]
        ]);
        exit;
    }

    // Validar tipo MIME (PDF, DOC, DOCX)
    $tiposPermitidos = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $archivoCV['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $tiposPermitidos)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El CV debe ser un archivo PDF, DOC o DOCX']);
        exit;
    }

    // Validar tamaño (máx 5 MB)
    $maxBytes = 5 * 1024 * 1024;
    if ($archivoCV['size'] > $maxBytes) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El CV no puede superar los 5 MB']);
        exit;
    }

    $db  = Database::getInstance();
    $pdo = $db->getConnection();

    // Verificar que la oferta existe y está publicada
    $oferta = BolsaTrabajo::obtenerPorId($pdo, $idOferta);
    if (!$oferta || (int)$oferta['habilitado'] !== 1 || (int)$oferta['cancelado'] !== 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'La oferta no existe o no está disponible']);
        exit;
    }

    // Verificar si ya existe una postulacion activa o cancelada
    $postulacionExistente = Postulacion::obtenerPorOfertaYUsuario($pdo, $idOferta, $idUsuario);

    if ($postulacionExistente && (int)$postulacionExistente['cancelado'] === 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Ya estás postulado a esta oferta']);
        exit;
    }

    // Subir CV a Cloudinary (recurso raw)
    $cloudinaryServicePath = __DIR__ . '/../services/CloudinaryService.php';
    if (!is_file($cloudinaryServicePath)) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'No se encontro el servicio de Cloudinary en el servidor'
        ]);
        exit;
    }

    require_once $cloudinaryServicePath;

    try {
        $cloudinary = new CloudinaryService('ComunidadIFTS/CVs');
    } catch (Throwable $e) {
        error_log('Cloudinary init fallo (postularse): ' . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo inicializar Cloudinary en el servidor',
            'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null,
        ]);
        exit;
    }

    $subida = $cloudinary->uploadFromFileArray($archivoCV, 'CVs', 'raw');

    if (!$subida['success']) {
        error_log('Cloudinary upload CV fallo (postularse): ' . ($subida['error'] ?? $subida['message'] ?? 'sin detalle'));
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo subir el CV. Intenta de nuevo.',
            'error' => ($_ENV['APP_DEBUG'] ?? false) ? ($subida['error'] ?? null) : null,
            'details' => [
                'tmp_name_exists' => is_file((string)($archivoCV['tmp_name'] ?? '')),
                'file_size_bytes' => (int)($archivoCV['size'] ?? 0),
                'mime' => (string)$mimeType,
            ]
        ]);
        exit;
    }

    $cvUrl      = $subida['url'] ?? null;
    $cvPublicId = $subida['public_id'] ?? null;

    // Guardar postulación en DB
    $pdo->beginTransaction();

    if ($postulacionExistente && (int)$postulacionExistente['cancelado'] === 1) {
        $ok = Postulacion::reactivarPostulacion(
            $pdo,
            (int)$postulacionExistente['id_postulacion'],
            $cvUrl,
            $cvPublicId
        );

        $idPostulacion = $ok ? (int)$postulacionExistente['id_postulacion'] : null;
    } else {
        $idPostulacion = Postulacion::crearPostulacion($pdo, $idOferta, $idUsuario, $cvUrl, $cvPublicId);
    }

    if (!$idPostulacion) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'No se pudo guardar la postulación']);
        exit;
    }

    $pdo->commit();

    // Traer datos completos del alumno desde DB para no depender solo de la sesion.
    $usuarioAlumno = Usuario::buscarPorId($pdo, $idUsuario);

    $datosAlumno = [
        'nombre'   => method_exists($usuarioAlumno, 'getNombre') ? (string)$usuarioAlumno->getNombre() : ((string)($_SESSION['nombre'] ?? '')),
        'apellido' => method_exists($usuarioAlumno, 'getApellido') ? (string)$usuarioAlumno->getApellido() : ((string)($_SESSION['apellido'] ?? '')),
        'email'    => method_exists($usuarioAlumno, 'getEmail') ? (string)$usuarioAlumno->getEmail() : ((string)($_SESSION['email'] ?? '')),
        'telefono' => method_exists($usuarioAlumno, 'getTelefono') ? (string)$usuarioAlumno->getTelefono() : ((string)($_SESSION['telefono'] ?? ''))
    ];

    $emailIFTS  = $oferta['email_ifts'] ?? '';
    $nombreIFTS = $oferta['nombre_ifts'] ?? '';
    $titulo     = $oferta['tituloOferta'] ?? '';

    $mailIftsEnviado = false;
    $mailAlumnoEnviado = false;
    $mailIftsError = null;
    $mailAlumnoError = null;

    // Mail 1: a la institucion con link al CV
    if ($emailIFTS !== '' && $cvUrl) {
        try {
            $mailer = new Mailer();
            $mailIftsEnviado = $mailer->notificarNuevaPostulacionIFTS($emailIFTS, $nombreIFTS, $titulo, $datosAlumno, $cvUrl);
            if (!$mailIftsEnviado) {
                $mailIftsError = $mailer->getLastError();
            }
        } catch (Exception $e) {
            $mailIftsError = $e->getMessage();
            error_log("Error mail postulacion IFTS: " . $mailIftsError);
        }
    }

    // Mail 2: confirmación al alumno
    $emailAlumno  = $datosAlumno['email'];
    $nombreAlumno = trim(($datosAlumno['nombre'] ?? '') . ' ' . ($datosAlumno['apellido'] ?? ''));

    if ($emailAlumno !== '') {
        try {
            $mailer = new Mailer();
            $mailAlumnoEnviado = $mailer->notificarPostulacionAlumno($emailAlumno, $nombreAlumno, $titulo, $nombreIFTS);
            if (!$mailAlumnoEnviado) {
                $mailAlumnoError = $mailer->getLastError();
            }
        } catch (Exception $e) {
            $mailAlumnoError = $e->getMessage();
            error_log("Error mail postulacion alumno: " . $mailAlumnoError);
        }
    } else {
        $mailAlumnoError = 'El usuario no tiene email disponible para notificacion';
        error_log('Error mail postulacion alumno: ' . $mailAlumnoError);
    }

    $response = [
        'success'        => true,
        'message'        => '¡Te postulaste exitosamente! Recibirás un correo de confirmación.',
        'id_postulacion' => $idPostulacion,
        'mail' => [
            'alumno_enviado' => $mailAlumnoEnviado,
            'ifts_enviado' => $mailIftsEnviado
        ]
    ];

    if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
        $response['mail']['alumno_error'] = $mailAlumnoError;
        $response['mail']['ifts_error'] = $mailIftsError;
    }

    echo json_encode($response);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Error postularse.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error'   => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}
