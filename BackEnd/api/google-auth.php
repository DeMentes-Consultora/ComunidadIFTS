<?php
/**
 * API: Login / Registro con Google
 * Endpoint: POST /api/google-auth.php
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Institucion.php';
require_once __DIR__ . '/../models/Persona.php';
require_once __DIR__ . '/../models/Rol.php';
require_once __DIR__ . '/../models/Usuario.php';

header('Content-Type: application/json');

function safe_json_encode(array $payload): string
{
    $json = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR
    );

    if ($json !== false) {
        return $json;
    }

    return '{"success":false,"message":"Error serializando respuesta JSON"}';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo safe_json_encode([
        'success' => false,
        'message' => 'Metodo no permitido'
    ]);
    exit;
}

function calcularEdadGoogle($fechaNacimiento)
{
    $nacimiento = DateTime::createFromFormat('Y-m-d', $fechaNacimiento);
    if (!$nacimiento) {
        return null;
    }

    return (int)(new DateTime())->diff($nacimiento)->y;
}

function validarTokenGoogle($idToken)
{
    $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken);

    $responseBody = false;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $responseBody = curl_exec($ch);
        if ($responseBody === false) {
            error_log('Google Auth tokeninfo curl fallo: ' . curl_error($ch));
        }
        curl_close($ch);
    }

    if ($responseBody === false) {
        $context = stream_context_create([
            'http' => [
                'timeout' => 10
            ]
        ]);
        $responseBody = @file_get_contents($url, false, $context);
        if ($responseBody === false) {
            error_log('Google Auth tokeninfo file_get_contents fallo');
        }
    }

    if ($responseBody === false) {
        return null;
    }

    $tokenData = json_decode($responseBody, true);
    return is_array($tokenData) ? $tokenData : null;
}

function subirFotoGoogleACloudinary(string $fotoUrl): ?array
{
    if ($fotoUrl === '') {
        return null;
    }

    try {
        $cloudinaryServicePath = __DIR__ . '/../services/CloudinaryService.php';
        if (!is_file($cloudinaryServicePath)) {
            error_log('Google Auth: no se encontro CloudinaryService.php');
            return null;
        }
        require_once $cloudinaryServicePath;

        $mediaFolders = require __DIR__ . '/../config/media-folders.php';
        $cloudinary = new CloudinaryService($mediaFolders['base'] ?? 'ComunidadIFTS');
        $folderFoto = $mediaFolders['perfiles']['foto'] ?? 'ComunidadIFTS/perfiles';

        $upload = $cloudinary->upload($fotoUrl, $folderFoto, 'image', [
            'overwrite' => false,
            'unique_filename' => true,
            'use_filename' => false,
        ]);

        if (!empty($upload['success']) && !empty($upload['url']) && !empty($upload['public_id'])) {
            return [
                'url' => (string)$upload['url'],
                'public_id' => (string)$upload['public_id'],
            ];
        }
    } catch (Throwable $e) {
        // Fallback silencioso: no bloquear login/registro por fallas de media.
    }

    return null;
}

try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) {
        error_log('Google Auth: payload JSON invalido o vacio');
        $payload = [];
    }

    $mode = trim($payload['mode'] ?? 'login');
    $idToken = trim($payload['id_token'] ?? '');

    if ($idToken === '') {
        http_response_code(400);
        echo safe_json_encode([
            'success' => false,
            'message' => 'Token de Google requerido'
        ]);
        exit;
    }

    $tokenData = validarTokenGoogle($idToken);
    if (!$tokenData || isset($tokenData['error_description'])) {
        error_log('Google Auth: token invalido o no verificable');
        http_response_code(401);
        echo safe_json_encode([
            'success' => false,
            'message' => 'Token de Google invalido o expirado'
        ]);
        exit;
    }

    $googleClientId = trim($_ENV['GOOGLE_CLIENT_ID'] ?? '');
    if ($googleClientId !== '' && ($tokenData['aud'] ?? '') !== $googleClientId) {
        error_log('Google Auth: aud no coincide con GOOGLE_CLIENT_ID');
        http_response_code(401);
        echo safe_json_encode([
            'success' => false,
            'message' => 'Token de Google no valido para esta aplicacion'
        ]);
        exit;
    }

    $email = trim($tokenData['email'] ?? '');
    $emailVerified = ($tokenData['email_verified'] ?? '') === 'true';
    $fotoPerfilGoogle = trim((string)($tokenData['picture'] ?? ''));

    // Fallback: algunos entornos no devuelven picture en tokeninfo.
    if ($fotoPerfilGoogle === '') {
        $fotoPerfilGoogle = trim((string)($payload['foto_perfil_url'] ?? ''));
    }

    if ($email === '' || !$emailVerified) {
        http_response_code(400);
        echo safe_json_encode([
            'success' => false,
            'message' => 'La cuenta de Google debe tener email verificado'
        ]);
        exit;
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Login con Google para usuario existente y aprobado.
    if ($mode === 'login') {
        $usuario = Usuario::buscarPorEmail($pdo, $email);

        if (!$usuario) {
            $estado = Usuario::obtenerEstadoPorEmail($pdo, $email);
            if ($estado && (int)$estado['habilitado'] === 0 && (int)$estado['cancelado'] === 0) {
                http_response_code(403);
                echo safe_json_encode([
                    'success' => false,
                    'message' => 'Tu cuenta esta pendiente de aprobacion por el administrador.',
                    'pendiente_aprobacion' => true
                ]);
                exit;
            }

            http_response_code(200);
            echo safe_json_encode([
                'success' => false,
                'message' => 'No existe una cuenta registrada con este Google. Usa la opcion de registro.',
                'requiere_registro' => true
            ]);
            exit;
        }

        // Si el usuario no tenia foto y Google provee una, se completa automaticamente.
        if (($usuario->getFotoPerfilUrl() ?? '') === '' && $fotoPerfilGoogle !== '') {
            try {
                $fotoCloudinary = subirFotoGoogleACloudinary($fotoPerfilGoogle);
                if ($fotoCloudinary) {
                    Persona::actualizarFotoPerfil(
                        $pdo,
                        $usuario->getIdPersona(),
                        $fotoCloudinary['url'],
                        $fotoCloudinary['public_id']
                    );
                } else {
                    Persona::actualizarFotoPerfil($pdo, $usuario->getIdPersona(), $fotoPerfilGoogle, null);
                }

                $usuarioRefrescado = Usuario::buscarPorEmail($pdo, $email);
                if ($usuarioRefrescado) {
                    $usuario = $usuarioRefrescado;
                }
            } catch (Throwable $e) {
                // No bloquear login por problemas de foto en producción.
                error_log('Google Auth: no se pudo persistir foto de perfil en login: ' . $e->getMessage());
            }
        }

        $_SESSION['logged_in'] = true;
        $_SESSION['id_usuario'] = $usuario->getIdUsuario();
        $_SESSION['email'] = $usuario->getEmail();
        $_SESSION['id_rol'] = $usuario->getIdRol();
        $_SESSION['id_persona'] = $usuario->getIdPersona();
        $_SESSION['id_institucion'] = $usuario->getIdInstitucion();
        $_SESSION['nombre'] = $usuario->getNombre();
        $_SESSION['apellido'] = $usuario->getApellido();

        echo safe_json_encode([
            'success' => true,
            'message' => 'Login con Google correcto',
            'data' => $usuario->toArray()
        ]);
        exit;
    }

    if ($mode !== 'register') {
        http_response_code(400);
        echo safe_json_encode([
            'success' => false,
            'message' => 'Modo de autenticacion invalido'
        ]);
        exit;
    }

    // Registro con Google: mantiene el flujo existente de aprobacion manual.
    $nombre = trim($payload['nombre'] ?? $tokenData['given_name'] ?? '');
    $apellido = trim($payload['apellido'] ?? $tokenData['family_name'] ?? '');
    $dni = trim($payload['dni'] ?? '');
    $fechaNacimiento = trim($payload['fecha_nacimiento'] ?? '');
    $telefono = trim($payload['telefono'] ?? '');
    $idInstitucion = (int)($payload['id_institucion'] ?? 0);
    $idCarrera = (int)($payload['id_carrera'] ?? 0);
    $anioCursada = (int)($payload['anio_cursada'] ?? 0);

    if ($nombre === '' || $apellido === '' || $dni === '' || $fechaNacimiento === '' || $telefono === '' || $idInstitucion <= 0 || $idCarrera <= 0 || $anioCursada <= 0) {
        http_response_code(400);
        echo safe_json_encode([
            'success' => false,
            'message' => 'Faltan datos obligatorios para completar el registro con Google'
        ]);
        exit;
    }

    if (!preg_match('/^\d{7,9}$/', $dni)) {
        http_response_code(400);
        echo safe_json_encode([
            'success' => false,
            'message' => 'DNI invalido'
        ]);
        exit;
    }

    $edad = calcularEdadGoogle($fechaNacimiento);
    if ($edad === null || $edad < 16 || $edad > 99) {
        http_response_code(400);
        echo safe_json_encode([
            'success' => false,
            'message' => 'Fecha de nacimiento invalida'
        ]);
        exit;
    }

    $pdo->beginTransaction();

    if (Usuario::emailExiste($pdo, $email)) {
        $pdo->rollBack();

        $estado = Usuario::obtenerEstadoPorEmail($pdo, $email);
        $pendiente = $estado && (int)$estado['habilitado'] === 0 && (int)$estado['cancelado'] === 0;

        http_response_code(409);
        echo safe_json_encode([
            'success' => false,
            'message' => $pendiente
                ? 'Ese email ya esta registrado y pendiente de aprobacion.'
                : 'El email ya esta registrado'
        ]);
        exit;
    }

    if (Persona::dniExiste($pdo, $dni)) {
        $pdo->rollBack();
        http_response_code(409);
        echo safe_json_encode([
            'success' => false,
            'message' => 'El DNI ya esta registrado'
        ]);
        exit;
    }

    $institucion = Institucion::obtenerActivaPorId($pdo, $idInstitucion);
    if (!$institucion) {
        $pdo->rollBack();
        http_response_code(400);
        echo safe_json_encode([
            'success' => false,
            'message' => 'La institucion seleccionada no es valida'
        ]);
        exit;
    }

    if (!Institucion::tieneCarreraActiva($pdo, $idInstitucion, $idCarrera)) {
        $pdo->rollBack();
        http_response_code(400);
        echo safe_json_encode([
            'success' => false,
            'message' => 'La carrera seleccionada no pertenece a la institución elegida'
        ]);
        exit;
    }

    if ($anioCursada < 1 || $anioCursada > 5) {
        $pdo->rollBack();
        http_response_code(400);
        echo safe_json_encode([
            'success' => false,
            'message' => 'El año de cursada no es válido'
        ]);
        exit;
    }

    $idRolAlumno = Rol::obtenerRolAlumnoActivo($pdo);
    if ($idRolAlumno === null) {
        $pdo->rollBack();
        http_response_code(500);
        echo safe_json_encode([
            'success' => false,
            'message' => 'El rol predeterminado de alumno no esta disponible'
        ]);
        exit;
    }

    $persona = new Persona($apellido, $nombre, $edad, $dni, $fechaNacimiento, $telefono);
    if (!$persona->guardar($pdo)) {
        $pdo->rollBack();
        http_response_code(500);
        echo safe_json_encode([
            'success' => false,
            'message' => 'No fue posible registrar la persona'
        ]);
        exit;
    }

    if ($fotoPerfilGoogle !== '') {
        try {
            $fotoCloudinary = subirFotoGoogleACloudinary($fotoPerfilGoogle);
            if ($fotoCloudinary) {
                Persona::actualizarFotoPerfil(
                    $pdo,
                    $persona->getIdPersona(),
                    $fotoCloudinary['url'],
                    $fotoCloudinary['public_id']
                );
            } else {
                Persona::actualizarFotoPerfil($pdo, $persona->getIdPersona(), $fotoPerfilGoogle, null);
            }
        } catch (Throwable $e) {
            // No bloquear registro por problemas de foto.
            error_log('Google Auth: no se pudo persistir foto de perfil en registro: ' . $e->getMessage());
        }
    }

    $claveTemporal = password_hash($email . '|' . ($tokenData['sub'] ?? uniqid('', true)), PASSWORD_DEFAULT);

    $usuario = new Usuario(
        $email,
        $claveTemporal,
        $persona->getIdPersona(),
        $idRolAlumno,
        $idInstitucion,
        $idCarrera,
        $anioCursada,
        null,
        0,
        0,
        null,
        null,
        false
    );

    if (!$usuario->guardar($pdo)) {
        $pdo->rollBack();
        http_response_code(500);
        echo safe_json_encode([
            'success' => false,
            'message' => 'No fue posible registrar el usuario'
        ]);
        exit;
    }

    $pdo->commit();

    // Notificaciones por email, sin bloquear alta si fallan.
    $emailAdminNotificado = false;
    $emailUsuarioNotificado = false;
    $emailWarning = null;

    try {
        require_once __DIR__ . '/../config/Mailer.php';
        $mailer = new Mailer();

        $datosUsuario = [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $email,
            'institucion' => $institucion['nombre_ifts'] ?? 'No especificada'
        ];

        $emailAdminNotificado = $mailer->notificarNuevoRegistro($datosUsuario);
        if (!$emailAdminNotificado) {
            $emailWarning = $mailer->getLastError() ?: 'No se pudo enviar notificacion al administrador.';
            error_log('Registro Google sin email admin: ' . $emailWarning);
        }

        $emailUsuarioNotificado = $mailer->notificarRegistroPendiente(
            $email,
            $nombre,
            $apellido,
            $institucion['nombre_ifts'] ?? 'No especificada'
        );

        if (!$emailUsuarioNotificado) {
            $warningUsuario = $mailer->getLastError() ?: 'No se pudo enviar confirmacion al usuario.';
            $emailWarning = $emailWarning ? ($emailWarning . ' | ' . $warningUsuario) : $warningUsuario;
            error_log('Registro Google sin email usuario: ' . $warningUsuario);
        }
    } catch (Throwable $e) {
        $emailWarning = 'Excepcion enviando notificacion: ' . $e->getMessage();
        error_log($emailWarning);
    }

    echo safe_json_encode([
        'success' => true,
        'message' => 'Registro con Google exitoso. Tu cuenta quedo pendiente de aprobacion.',
        'pendiente_aprobacion' => true,
        'email_admin_notificado' => $emailAdminNotificado,
        'email_usuario_notificado' => $emailUsuarioNotificado,
        'warning' => $emailWarning
    ]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Google Auth fatal: ' . $e->getMessage());

    http_response_code(500);
    echo safe_json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}
