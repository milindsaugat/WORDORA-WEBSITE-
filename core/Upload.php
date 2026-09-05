<?php
/**
 * WORDORA — Secure File Upload Handler
 */
class Upload {
    private array $allowedImages = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    private array $allowedVideos = ['video/mp4', 'video/webm'];
    private int $maxSize;
    private string $dir;

    public function __construct(string $subdir = 'general') {
        $cfg = require ROOT_PATH . '/config/config.php';
        $this->maxSize = $cfg['upload_max_size'] ?? 52428800;
        $this->dir = ROOT_PATH . '/uploads/' . $subdir . '/';
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0755, true);
        }
    }

    public function handle(array $file, bool $allowVideo = false): array {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'msg' => 'Upload error code: ' . $file['error']];
        }
        if ($file['size'] > $this->maxSize) {
            $mb = round($this->maxSize / 1048576);
            return ['success' => false, 'msg' => "File too large (max {$mb}MB)"];
        }

        $allowed = $this->allowedImages;
        if ($allowVideo) {
            $allowed = array_merge($allowed, $this->allowedVideos);
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed)) {
            return ['success' => false, 'msg' => 'Invalid file type: ' . $mime];
        }

        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $safe = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4', 'webm'];
        if (!in_array($ext, $safe)) {
            return ['success' => false, 'msg' => 'Invalid file extension'];
        }

        $name = uniqid('wdr_', true) . '.' . $ext;
        $dest = $this->dir . $name;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $rel = '/uploads/' . basename(rtrim($this->dir, '/')) . '/' . $name;
            return ['success' => true, 'path' => $rel, 'filename' => $name];
        }
        return ['success' => false, 'msg' => 'Failed to move uploaded file'];
    }

    /**
     * Static convenience helper for uploading an image
     */
    public static function image(array $file, string $subdir = 'general'): string {
        $uploader = new self($subdir);
        $res = $uploader->handle($file, false);
        return $res['success'] ? $res['path'] : '';
    }

    /**
     * Static convenience helper for uploading a file (image or video)
     */
    public static function file(array $file, string $subdir = 'general', bool $allowVideo = false): string {
        $uploader = new self($subdir);
        $res = $uploader->handle($file, $allowVideo);
        return $res['success'] ? $res['path'] : '';
    }
}
