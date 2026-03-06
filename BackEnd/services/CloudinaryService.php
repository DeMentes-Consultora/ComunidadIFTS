<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Cloudinary\Api\Admin\AdminApi;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;

class CloudinaryService
{
    private UploadApi $uploadApi;
    private AdminApi $adminApi;
    private string $baseFolder;

    public function __construct(?string $baseFolder = null)
    {
        $this->configureCloudinary();

        $this->uploadApi = new UploadApi();
        $this->adminApi = new AdminApi();
        $this->baseFolder = trim($baseFolder ?? ($_ENV['CLOUDINARY_BASE_FOLDER'] ?? 'ComunidadIFTS'), '/');
    }

    /**
     * Sube cualquier recurso a Cloudinary.
     */
    public function upload(string $filePath, string $folder, string $resourceType = 'image', array $options = []): array
    {
        if (!is_file($filePath)) {
            return [
                'success' => false,
                'message' => 'Archivo no encontrado para subir.',
            ];
        }

        $fullFolder = $this->normalizeFolder($folder);

        $defaultOptions = [
            'folder' => $fullFolder,
            'resource_type' => $resourceType,
            'overwrite' => false,
            'unique_filename' => true,
            'use_filename' => true,
        ];

        try {
            $result = $this->uploadApi->upload($filePath, array_merge($defaultOptions, $options));

            return [
                'success' => true,
                'url' => $result['secure_url'] ?? null,
                'public_id' => $result['public_id'] ?? null,
                'resource_type' => $result['resource_type'] ?? $resourceType,
                'format' => $result['format'] ?? null,
                'bytes' => $result['bytes'] ?? null,
                'raw' => $result,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Error al subir a Cloudinary.',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Helper para subir usando $_FILES['campo'].
     */
    public function uploadFromFileArray(array $file, string $folder, string $resourceType = 'image', array $options = []): array
    {
        if (empty($file) || !isset($file['tmp_name'])) {
            return [
                'success' => false,
                'message' => 'No se recibio archivo en el request.',
            ];
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'message' => 'Error en la carga del archivo PHP.',
                'php_upload_error' => $file['error'],
            ];
        }

        return $this->upload($file['tmp_name'], $folder, $resourceType, $options);
    }

    /**
     * Elimina un recurso por public_id.
     */
    public function delete(string $publicId, string $resourceType = 'image'): array
    {
        if ($publicId === '') {
            return [
                'success' => false,
                'message' => 'public_id vacio.',
            ];
        }

        try {
            $result = $this->uploadApi->destroy($publicId, ['resource_type' => $resourceType]);
            $deleted = ($result['result'] ?? '') === 'ok';

            return [
                'success' => $deleted,
                'message' => $deleted ? 'Recurso eliminado.' : 'Cloudinary no elimino el recurso.',
                'raw' => $result,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar en Cloudinary.',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Reemplaza un recurso: elimina el viejo (si existe) y sube el nuevo.
     */
    public function replace(string $newFilePath, ?string $oldPublicId, string $folder, string $resourceType = 'image', array $options = []): array
    {
        if (!empty($oldPublicId)) {
            $this->delete($oldPublicId, $resourceType);
        }

        return $this->upload($newFilePath, $folder, $resourceType, $options);
    }

    /**
     * Elimina un recurso de Cloudinary a partir de su URL.
     */
    public function deleteByUrl(string $assetUrl, string $resourceType = 'image'): array
    {
        $publicId = $this->extractPublicIdFromUrl($assetUrl);
        if ($publicId === null) {
            return [
                'success' => false,
                'message' => 'No se pudo obtener public_id desde la URL.',
            ];
        }

        return $this->delete($publicId, $resourceType);
    }

    /**
     * Extrae public_id desde una URL de Cloudinary.
     */
    public function extractPublicIdFromUrl(string $assetUrl): ?string
    {
        if ($assetUrl === '' || strpos($assetUrl, 'res.cloudinary.com') === false) {
            return null;
        }

        $parts = parse_url($assetUrl);
        if (!$parts || empty($parts['path'])) {
            return null;
        }

        $path = trim($parts['path'], '/');
        $segments = explode('/', $path);

        // Formato esperado: /<cloud>/image/upload/v123/folder/file.ext
        $uploadIndex = array_search('upload', $segments, true);
        if ($uploadIndex === false || !isset($segments[$uploadIndex + 1])) {
            return null;
        }

        $publicIdSegments = array_slice($segments, $uploadIndex + 1);
        if (empty($publicIdSegments)) {
            return null;
        }

        // Saltar version si existe (v12345)
        if (preg_match('/^v\d+$/', $publicIdSegments[0])) {
            array_shift($publicIdSegments);
        }

        if (empty($publicIdSegments)) {
            return null;
        }

        $last = array_pop($publicIdSegments);
        $last = preg_replace('/\.[^.]+$/', '', $last);
        $publicIdSegments[] = $last;

        return implode('/', $publicIdSegments);
    }

    /**
     * Lista recursos de una carpeta (util para debug/administracion).
     */
    public function listByFolder(string $folder, string $resourceType = 'image', int $maxResults = 50): array
    {
        $fullFolder = $this->normalizeFolder($folder);

        try {
            $result = $this->adminApi->assetsByAssetFolder($fullFolder, [
                'resource_type' => $resourceType,
                'max_results' => $maxResults,
            ]);

            return [
                'success' => true,
                'resources' => $result['resources'] ?? [],
                'raw' => $result,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Error al listar carpeta en Cloudinary.',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function configureCloudinary(): void
    {
        $cloudName = $_ENV['CLOUDINARY_CLOUD_NAME'] ?? '';
        $apiKey = $_ENV['CLOUDINARY_API_KEY'] ?? '';
        $apiSecret = $_ENV['CLOUDINARY_API_SECRET'] ?? '';

        if ($cloudName === '' || $apiKey === '' || $apiSecret === '') {
            throw new RuntimeException('Faltan credenciales de Cloudinary en .env');
        }

        Configuration::instance([
            'cloud' => [
                'cloud_name' => $cloudName,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
            ],
            'url' => [
                'secure' => true,
            ],
        ]);
    }

    private function normalizeFolder(string $folder): string
    {
        $folder = trim($folder, '/');

        if ($folder === '') {
            return $this->baseFolder;
        }

        if (strpos($folder, $this->baseFolder . '/') === 0 || $folder === $this->baseFolder) {
            return $folder;
        }

        return $this->baseFolder . '/' . $folder;
    }
}
