<?php
/**
 * Servicio ForoEmailService
 *
 * Envía emails de notificación relacionados con el foro.
 * Sigue el patrón de Mailer.php existente.
 */

require_once __DIR__ . '/../config/Mailer.php';

class ForoEmailService {

    private Mailer $mailer;

    public function __construct() {
        $this->mailer = new Mailer();
    }

    /**
     * Notifica al creador que su tema fue cerrado por inactividad.
     */
    public function notificarTemaCerradoPorInactividad(
        string $email,
        string $nombre,
        string $tituloTema,
        int $diasInactividad
    ): bool {
        $nombreSeguro = htmlspecialchars($nombre);
        $tituloSeguro = htmlspecialchars($tituloTema);
        $asunto = "Tu tema fue cerrado por inactividad - ComunidadIFTS";

        $cuerpoHTML = $this->plantillaTemaCerrado($nombreSeguro, $tituloSeguro, $diasInactividad);
        $cuerpoTexto = "Hola $nombre,\n\n"
            . "Tu tema \"$tituloTema\" fue cerrado automáticamente porque no tuvo actividad en $diasInactividad días.\n"
            . "El tema permanece visible para consulta.\n\n"
            . "Saludos,\nEquipo de ComunidadIFTS";

        return $this->mailer->enviar($email, $nombre, $asunto, $cuerpoHTML, $cuerpoTexto);
    }

    /**
     * Plantilla HTML para notificación de cierre de tema por inactividad.
     */
    private function plantillaTemaCerrado(string $nombre, string $titulo, int $dias): string {
        return <<<HTML
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">
<style>
  body{font-family:'Segoe UI',sans-serif;background:#f4f4f4;margin:0;padding:20px}
  .c{max-width:600px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.1);overflow:hidden}
  .h{background:linear-gradient(135deg,#ff9800,#f57c00);color:#fff;padding:30px;text-align:center}
  .h h1{margin:0;font-size:22px}
  .b{padding:30px}
  .box{background:#fff3e0;border-left:4px solid #ff9800;padding:15px;margin:20px 0}
  .box p{margin:6px 0;color:#333}
  .box strong{color:#e65100}
  .f{background:#f9f9f9;padding:20px;text-align:center;font-size:12px;color:#666}
</style></head><body>
<div class="c">
  <div class="h"><h1>📌 Tema Cerrado por Inactividad</h1></div>
  <div class="b">
    <p>Hola <strong>$nombre</strong>,</p>
    <p>Tu tema en el foro fue <strong>cerrado automáticamente</strong> porque no recibió respuesta en <strong>$dias días</strong>.</p>
    <div class="box">
      <p><strong>Tema:</strong> $titulo</p>
      <p><strong>Estado:</strong> Cerrado (solo lectura)</p>
    </div>
    <p>El tema permanece visible en el foro para que otros puedan consultarlo. Si necesitás abrir un tema nuevo, podés hacerlo desde el panel del foro.</p>
  </div>
  <div class="f"><p>Correo automático de ComunidadIFTS. No respondas este mensaje.</p></div>
</div>
</body></html>
HTML;
    }
}
