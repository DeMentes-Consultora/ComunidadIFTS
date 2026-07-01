<?php
/**
 * Servicio ForoMediaService
 *
 * Valida y sube archivos adjuntos del foro a Cloudinary.
 * Tipos soportados:
 *   - imagen: jpg, jpeg, png (max 300KB)
 *   - pdf: pdf (max 300KB)
 *   - video: mp4, webm, mov (max 500KB)
 */

require_once __DIR__ . '/CloudinaryService.php';

class ForoMediaService {

    private const MAX_IMAGE_BYTES = 300 * 1024;   // 300 KB
    private const MAX_PDF_BYTES   = 300 * 1024;   // 300 KB
    private const MAX_VIDEO_BYTES = 500 * 1024;   // 500 KB

    private const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png'];
    private const ALLOWED_PDF_EXTENSIONS   = ['pdf'];
    private const ALLOWED_VIDEO_EXTENSIONS = ['mp4', 'webm', 'mov'];

    private const IMAGE_MIMES = ['image/jpeg', 'image/png'];
    private const PDF_MIMES   = ['application/pdf'];
    private const VIDEO_MIMES = ['video/mp4', 'video/webm', 'video/quicktime'];

    private CloudinaryService $cloudinary;

    public function __construct() {
        $mediaFolders = require __DIR__ . '/../config/media-folders.php';
        $this->cloudinary = new CloudinaryService($mediaFolders['base'] ?? 'ComunidadIFTS');
    }

    /**
     * Valida y sube un archivo adjunto.
     * Retorna ['success' => true, 'url', 'public_id', 'tipo', 'nombre_original', 'tamano_bytes']
     * o ['success' => false, 'message']
     */
    public function subir(array $archivo, ?int $idTema = null, ?int $idRespuesta = null): array {
        // Validar que haya archivo
        if (empty($archivo) || !isset($archivo['tmp_name'])) {
            return ['success' => false, 'message' => 'No se recibió archivo.'];
        }

        if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Error en la carga del archivo.'];
        }

        // Determinar tipo y validar
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $tipo = $this->determinarTipo($extension, $archivo['type'] ?? '');

        if ($tipo === null) {
            return [
                'success' => false,
                'message' => 'Tipo de archivo no permitido. Solo se permiten: jpg, jpeg, png, pdf, mp4, webm, mov.'
            ];
        }

        // Validar tamaño
        $maxBytes = $this->obtenerMaxBytes($tipo);
        if ($archivo['size'] > $maxBytes) {
            $maxKB = (int)($maxBytes / 1024);
            return [
                'success' => false,
                'message' => "El archivo supera el tamaño máximo de {$maxKB}KB para archivos de tipo {$tipo}."
            ];
        }

        // Subir a Cloudinary
        $resourceType = $this->obtenerResourceType($tipo);
        $folder = $this->obtenerCarpeta($tipo);

        try {
            $upload = $this->cloudinary->uploadFromFileArray($archivo, $folder, $resourceType);

            if (empty($upload['success'])) {
                return [
                    'success' => false,
                    'message' => $upload['message'] ?? 'Error al subir archivo a Cloudinary.'
                ];
            }

            return [
                'success' => true,
                'url' => $upload['url'],
                'public_id' => $upload['public_id'],
                'tipo' => $tipo,
                'nombre_original' => $archivo['name'],
                'tamano_bytes' => $archivo['size']
            ];
        } catch (\Throwable $e) {
            error_log('ForoMediaService upload error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno al subir archivo.'
            ];
        }
    }

    /**
     * Elimina un archivo de Cloudinary por su public_id.
     */
    public function eliminar(string $publicId, string $tipo): bool {
        if (empty($publicId)) {
            return false;
        }

        $resourceType = $this->obtenerResourceType($tipo);
        $result = $this->cloudinary->delete($publicId, $resourceType);
        return !empty($result['success']);
    }

    private function determinarTipo(string $extension, string $mimeType): ?string {
        if (in_array($extension, self::ALLOWED_IMAGE_EXTENSIONS) || in_array($mimeType, self::IMAGE_MIMES)) {
            return 'imagen';
        }
        if (in_array($extension, self::ALLOWED_PDF_EXTENSIONS) || in_array($mimeType, self::PDF_MIMES)) {
            return 'pdf';
        }
        if (in_array($extension, self::ALLOWED_VIDEO_EXTENSIONS) || in_array($mimeType, self::VIDEO_MIMES)) {
            return 'video';
        }
        return null;
    }

    private function obtenerMaxBytes(string $tipo): int {
        return match($tipo) {
            'imagen' => self::MAX_IMAGE_BYTES,
            'pdf'    => self::MAX_PDF_BYTES,
            'video'  => self::MAX_VIDEO_BYTES,
            default  => self::MAX_PDF_BYTES,
        };
    }

    private function obtenerResourceType(string $tipo): string {
        return match($tipo) {
            'imagen' => 'image',
            'pdf'    => 'raw',
            'video'  => 'video',
            default  => 'auto',
        };
    }

    private function obtenerCarpeta(string $tipo): string {
        return match($tipo) {
            'imagen' => 'ComunidadIFTS/foro/imagenes',
            'pdf'    => 'ComunidadIFTS/foro/pdfs',
            'video'  => 'ComunidadIFTS/foro/videos',
            default  => 'ComunidadIFTS/foro/adjuntos',
        };
    }
}
