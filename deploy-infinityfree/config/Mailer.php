<?php
/**
 * Clase Mailer - Servicio de envío de correos electrónicos
 * Utiliza PHPMailer con configuración SMTP
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../vendor/autoload.php';

class Mailer {
    private $mail;
    private $configured = false;
    private $lastError = '';

    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->configurar();
    }

    private function env(string $key, $default = null) {
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }

        $value = getenv($key);
        if ($value !== false && $value !== '') {
            return $value;
        }

        return $default;
    }

    /**
     * Configura PHPMailer con variables de entorno
     */
    private function configurar() {
        try {
            // Cargar variables de entorno si no están cargadas
            if ($this->env('MAIL_HOST') === null && file_exists(__DIR__ . '/../.env')) {
                $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
                $dotenv->safeLoad();
            }

            $required = [
                'MAIL_HOST',
                'MAIL_PORT',
                'MAIL_USERNAME',
                'MAIL_PASSWORD',
                'MAIL_FROM_ADDRESS'
            ];

            $missing = [];
            foreach ($required as $varName) {
                $value = $this->env($varName);
                if ($value === false || trim((string)$value) === '') {
                    $missing[] = $varName;
                }
            }

            if (!empty($missing)) {
                $this->lastError = 'Faltan variables de entorno SMTP: ' . implode(', ', $missing);
                error_log($this->lastError);
                $this->configured = false;
                return;
            }

            // Configuración del servidor SMTP
            $this->mail->isSMTP();
            $this->mail->Host       = $this->env('MAIL_HOST');
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = $this->env('MAIL_USERNAME');
            $this->mail->Password   = $this->env('MAIL_PASSWORD');
            $this->mail->SMTPSecure = $this->env('MAIL_ENCRYPTION', 'tls') === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port       = (int)$this->env('MAIL_PORT');
            $this->mail->CharSet    = 'UTF-8';

            // Remitente por defecto
            $fromAddress = $this->env('MAIL_FROM_ADDRESS');
            $fromName = $this->env('MAIL_FROM_NAME', 'ComunidadIFTS');
            
            if ($fromAddress) {
                $this->mail->setFrom($fromAddress, $fromName);
            }

            // Para desarrollo: desactivar verificación de certificados SSL
            if ($this->env('APP_ENV') === 'development') {
                $this->mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );
            }

            $this->configured = true;
        } catch (\Throwable $e) {
            $this->lastError = "Error configurando Mailer: {$e->getMessage()}";
            error_log($this->lastError);
            $this->configured = false;
        }
    }

    /**
     * Envía un correo electrónico
     * 
     * @param string $destinatario Email del destinatario
     * @param string $nombreDestinatario Nombre del destinatario
     * @param string $asunto Asunto del correo
     * @param string $cuerpoHTML Cuerpo del mensaje en HTML
     * @param string|null $cuerpoTexto Cuerpo alternativo en texto plano
     * @return bool True si se envió correctamente, false en caso contrario
     */
    public function enviar($destinatario, $nombreDestinatario, $asunto, $cuerpoHTML, $cuerpoTexto = null) {
        if (!$this->configured) {
            if (trim($this->lastError) === '') {
                $this->lastError = 'Mailer no configurado correctamente';
            }
            error_log($this->lastError);
            return false;
        }

        try {
            // Destinatario
            $this->mail->addAddress($destinatario, $nombreDestinatario);

            // Contenido
            $this->mail->isHTML(true);
            $this->mail->Subject = $asunto;
            $this->mail->Body    = $cuerpoHTML;
            
            if ($cuerpoTexto) {
                $this->mail->AltBody = $cuerpoTexto;
            }

            $resultado = $this->mail->send();
            $this->lastError = '';
            return $resultado;
        } catch (\Throwable $e) {
            $this->lastError = "Error enviando email: {$this->mail->ErrorInfo}";
            error_log($this->lastError);
            return false;
        } finally {
            // Limpiar destinatarios para el siguiente envío
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
        }
    }

    public function getLastError() {
        return $this->lastError;
    }

    /**
     * Envía notificación al administrador sobre nuevo registro
     * 
     * @param array $datosUsuario Datos del usuario: nombre, apellido, email, institucion
     * @return bool
     */
    public function notificarNuevoRegistro($datosUsuario) {
        $adminEmail = $this->env('ADMIN_EMAIL');
        
        if (!$adminEmail) {
            error_log("ADMIN_EMAIL no configurado en .env");
            return false;
        }

        $nombre = htmlspecialchars($datosUsuario['nombre']);
        $apellido = htmlspecialchars($datosUsuario['apellido']);
        $email = htmlspecialchars($datosUsuario['email']);
        $institucion = htmlspecialchars($datosUsuario['institucion'] ?? 'No especificada');
        
        $asunto = "Nuevo usuario pendiente de aprobación - ComunidadIFTS";
        
        $cuerpoHTML = $this->plantillaNuevoRegistro($nombre, $apellido, $email, $institucion);
        
        $cuerpoTexto = "Nuevo Usuario Pendiente de Aprobación\n\n"
                     . "Nombre: $nombre $apellido\n"
                     . "Email: $email\n"
                     . "Institución: $institucion\n\n"
                     . "Accede al panel de administración para aprobar o rechazar este registro.";

        return $this->enviar($adminEmail, 'Administrador', $asunto, $cuerpoHTML, $cuerpoTexto);
    }

    /**
     * Envía notificación al usuario de que su cuenta fue aprobada
     * 
     * @param string $email Email del usuario
     * @param string $nombre Nombre del usuario
     * @return bool
     */
    public function notificarAprobacion($email, $nombre) {
        $asunto = "¡Cuenta aprobada! - ComunidadIFTS";
        
        $cuerpoHTML = $this->plantillaAprobacion($nombre);
        
        $cuerpoTexto = "¡Hola $nombre!\n\n"
                     . "Tu cuenta en ComunidadIFTS ha sido aprobada exitosamente.\n\n"
                     . "Ya puedes iniciar sesión y acceder a todas las funcionalidades de la plataforma.\n\n"
                     . "¡Bienvenido a la comunidad!\n\n"
                     . "Saludos,\nEquipo de ComunidadIFTS";

        return $this->enviar($email, $nombre, $asunto, $cuerpoHTML, $cuerpoTexto);
    }

    /**
     * Envía notificación al usuario de que su cuenta fue rechazada
     * 
     * @param string $email Email del usuario
     * @param string $nombre Nombre del usuario
     * @param string|null $motivo Motivo del rechazo (opcional)
     * @return bool
     */
    public function notificarRechazo($email, $nombre, $motivo = null) {
        $asunto = "Solicitud de registro - ComunidadIFTS";
        
        $cuerpoHTML = $this->plantillaRechazo($nombre, $motivo);
        
        $motivoTexto = $motivo ? "\n\nMotivo: $motivo" : "";
        $cuerpoTexto = "Hola $nombre,\n\n"
                     . "Lamentamos informarte que tu solicitud de registro en ComunidadIFTS no ha sido aprobada.$motivoTexto\n\n"
                     . "Si tienes preguntas, por favor contacta al administrador.\n\n"
                     . "Saludos,\nEquipo de ComunidadIFTS";

        return $this->enviar($email, $nombre, $asunto, $cuerpoHTML, $cuerpoTexto);
    }

    /**
     * Plantilla HTML para notificación de nuevo registro al admin
     */
    private function plantillaNuevoRegistro($nombre, $apellido, $email, $institucion) {
        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Registro Pendiente</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #006633 0%, #008844 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .info-box { background-color: #f9f9f9; border-left: 4px solid #006633; padding: 15px; margin: 20px 0; }
        .info-box p { margin: 8px 0; color: #333; }
        .info-box strong { color: #006633; }
        .button { display: inline-block; background-color: #006633; color: white; text-decoration: none; padding: 12px 30px; border-radius: 5px; margin-top: 20px; font-weight: bold; }
        .button:hover { background-color: #004d26; }
        .footer { background-color: #f9f9f9; padding: 20px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 Nuevo Usuario Pendiente</h1>
        </div>
        <div class="content">
            <p>Se ha registrado un nuevo usuario en <strong>ComunidadIFTS</strong> y está esperando tu aprobación:</p>
            
            <div class="info-box">
                <p><strong>Nombre:</strong> $nombre $apellido</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Institución:</strong> $institucion</p>
            </div>
            
            <p>Accede al panel de administración para revisar y aprobar este registro.</p>
            
            <center>
                <a href="{urlFrontend}/admin/gestion-usuarios" class="button">Ver Panel de Administración</a>
            </center>
        </div>
        <div class="footer">
            <p>Este es un correo automático de ComunidadIFTS. Por favor no respondas a este mensaje.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Plantilla HTML para notificación de aprobación al usuario
     */
    private function plantillaAprobacion($nombre) {
        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuenta Aprobada</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #006633 0%, #008844 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .success-icon { font-size: 60px; text-align: center; margin: 20px 0; }
        .button { display: inline-block; background-color: #006633; color: white; text-decoration: none; padding: 12px 30px; border-radius: 5px; margin-top: 20px; font-weight: bold; }
        .button:hover { background-color: #004d26; }
        .footer { background-color: #f9f9f9; padding: 20px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>¡Bienvenido a ComunidadIFTS!</h1>
        </div>
        <div class="content">
            <div class="success-icon">✅</div>
            
            <p>¡Hola <strong>$nombre</strong>!</p>
            
            <p>Nos complace informarte que tu cuenta en <strong>ComunidadIFTS</strong> ha sido <strong>aprobada exitosamente</strong>.</p>
            
            <p>Ya puedes iniciar sesión y acceder a todas las funcionalidades de la plataforma:</p>
            
            <ul>
                <li>Explorar instituciones educativas en el mapa</li>
                <li>Filtrar por carreras de interés</li>
                <li>Acceder a información detallada de cada IFTS</li>
            </ul>
            
            <center>
                <a href="{urlFrontend}/login" class="button">Iniciar Sesión</a>
            </center>
        </div>
        <div class="footer">
            <p>Este es un correo automático de ComunidadIFTS. Por favor no respondas a este mensaje.</p>
            <p>Si tienes alguna pregunta, contacta al administrador.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Plantilla HTML para notificación de rechazo
     */
    private function plantillaRechazo($nombre, $motivo) {
        $motivoHTML = $motivo ? "<p><strong>Motivo:</strong> " . htmlspecialchars($motivo) . "</p>" : "";
        
        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Registro</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background-color: #666; color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .footer { background-color: #f9f9f9; padding: 20px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Solicitud de Registro</h1>
        </div>
        <div class="content">
            <p>Hola <strong>$nombre</strong>,</p>
            
            <p>Lamentamos informarte que tu solicitud de registro en <strong>ComunidadIFTS</strong> no ha sido aprobada.</p>
            
            $motivoHTML
            
            <p>Si consideras que se trata de un error o tienes preguntas, por favor contacta al administrador del sistema.</p>
        </div>
        <div class="footer">
            <p>Este es un correo automático de ComunidadIFTS.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
