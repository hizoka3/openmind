<?php // src/Services/ImageUploadService.php
namespace Openmind\Services;

class ImageUploadService {

    const MAX_FILES = 5;
    const THUMBNAIL_SIZE = 800;
    const QUALITY = 80;
    const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public static function upload(array $file, string $entry_type): ?array {
        // Validar tipo
        if (!in_array($file['type'], self::ALLOWED_TYPES)) {
            return ['error' => 'Formato no permitido. Solo JPG, PNG, WebP'];
        }

        // Validar tamaño (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            return ['error' => 'Archivo muy pesado. Máximo 5MB'];
        }

        $upload_dir = self::getUploadDir($entry_type);

        if (!file_exists($upload_dir)) {
            wp_mkdir_p($upload_dir);
        }

        $file_name = self::generateFileName($file['name']);
        $file_path = $upload_dir . '/' . $file_name;

        // Procesar imagen
        $image_data = self::processImage($file['tmp_name'], $file['type']);

        if (!$image_data) {
            return ['error' => 'Error al procesar imagen'];
        }

        // Guardar
        if (!file_put_contents($file_path, $image_data)) {
            return ['error' => 'Error al guardar archivo'];
        }

        $relative_path = str_replace(ABSPATH, '/', $file_path);

        return [
            'success' => true,
            'file_name' => $file_name,
            'file_path' => $relative_path,
            'file_type' => $file['type'],
            'file_size' => filesize($file_path)
        ];
    }

    private static function processImage(string $tmp_path, string $mime_type): ?string {
        switch ($mime_type) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($tmp_path);
                break;
            case 'image/png':
                $image = imagecreatefrompng($tmp_path);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($tmp_path);
                break;
            default:
                return null;
        }

        if (!$image) return null;

        // Redimensionar
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width > self::THUMBNAIL_SIZE || $height > self::THUMBNAIL_SIZE) {
            $ratio = min(self::THUMBNAIL_SIZE / $width, self::THUMBNAIL_SIZE / $height);
            $new_width = round($width * $ratio);
            $new_height = round($height * $ratio);

            $thumb = imagecreatetruecolor($new_width, $new_height);

            // Preservar transparencia para PNG
            if ($mime_type === 'image/png') {
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true);
            }

            imagecopyresampled($thumb, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            imagedestroy($image);
            $image = $thumb;
        }

        // Convertir a string
        ob_start();
        switch ($mime_type) {
            case 'image/jpeg':
                imagejpeg($image, null, self::QUALITY);
                break;
            case 'image/png':
                imagepng($image, null, 9);
                break;
            case 'image/webp':
                imagewebp($image, null, self::QUALITY);
                break;
        }
        $data = ob_get_clean();

        imagedestroy($image);

        return $data;
    }

    private static function getUploadDir(string $entry_type): string {
        $base = wp_upload_dir()['basedir'] . '/openmind';
        $folder = $entry_type === 'diary' ? 'journal' : 'session-notes';
        return $base . '/' . $folder . '/' . date('Y/m');
    }

    private static function generateFileName(string $original): string {
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        return uniqid('img_') . '_' . time() . '.' . $ext;
    }
}