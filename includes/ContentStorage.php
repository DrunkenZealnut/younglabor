<?php

class ContentUploadException extends RuntimeException {}

class ContentStorage
{
    protected string $root;
    protected string $staging;
    private $uploadMover;

    public function __construct(string $root, ?callable $uploadMover = null)
    {
        $resolved = realpath($root);
        if ($resolved === false || !is_dir($resolved) || !is_writable($resolved)) {
            throw new ContentUploadException('Storage is unavailable.');
        }
        $this->root = rtrim($resolved, DIRECTORY_SEPARATOR);
        $this->staging = $this->root . DIRECTORY_SEPARATOR . '.staging';
        if (!is_dir($this->staging) && !mkdir($this->staging, 0700, true) && !is_dir($this->staging)) {
            throw new ContentUploadException('Staging storage is unavailable.');
        }
        @chmod($this->staging, 0700);
        $this->uploadMover = $uploadMover ?: static function (string $from, string $to): bool {
            return move_uploaded_file($from, $to);
        };
    }

    public function stageCover(array $upload, string $altText): array
    {
        $this->assertUpload($upload, 5 * 1024 * 1024);
        $source = (string)$upload['tmp_name'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file($source);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw new ContentUploadException('Unsupported cover image.');
        }
        $dimensions = @getimagesize($source);
        if ($dimensions === false || $dimensions[0] < 1 || $dimensions[1] < 1 || $dimensions[0] > 6000 || $dimensions[1] > 6000) {
            throw new ContentUploadException('Invalid cover dimensions.');
        }

        $rawPath = $this->staging . DIRECTORY_SEPARATOR . bin2hex(random_bytes(16)) . '.upload';
        if (!(($this->uploadMover)($source, $rawPath))) {
            throw new ContentUploadException('Cover upload could not be staged.');
        }
        @chmod($rawPath, 0600);
        $image = false;
        $stagedPaths = [];
        try {
            $image = $this->decodeImage($rawPath, $mime);
            if ($image === false) {
                throw new ContentUploadException('Cover image could not be decoded.');
            }
            if ($mime === 'image/jpeg') {
                $image = $this->orientJpeg($image, $rawPath);
            }
            $sourceWidth = imagesx($image);
            $sourceHeight = imagesy($image);
            $scale = min(1, 1600 / max($sourceWidth, $sourceHeight));
            $width = max(1, (int)round($sourceWidth * $scale));
            $height = max(1, (int)round($sourceHeight * $scale));
            $storageName = bin2hex(random_bytes(32));
            $stagedPath = $this->staging . DIRECTORY_SEPARATOR . $storageName;
            $stagedPaths[] = $stagedPath;
            $this->writeResizedWebp($image, $sourceWidth, $sourceHeight, $width, $height, $stagedPath);

            $variants = [];
            foreach ([480, 960] as $variantWidth) {
                if ($variantWidth >= $width) {
                    continue;
                }
                $variantHeight = max(1, (int)round($height * ($variantWidth / $width)));
                $variantName = $this->variantStorageName($storageName, $variantWidth);
                $variantPath = $this->staging . DIRECTORY_SEPARATOR . $variantName;
                $stagedPaths[] = $variantPath;
                $this->writeResizedWebp($image, $sourceWidth, $sourceHeight, $variantWidth, $variantHeight, $variantPath);
                $variants[$variantWidth] = [
                    'staged_path' => $variantPath,
                    'storage_name' => $variantName,
                    'width' => $variantWidth,
                    'height' => $variantHeight,
                ];
            }
            return [
                'staged_path' => $stagedPath,
                'purpose' => 'cover',
                'storage_name' => $storageName,
                'original_name' => $this->safeOriginalName((string)$upload['name']),
                'mime' => 'image/webp',
                'byte_size' => filesize($stagedPath),
                'sha256' => hash_file('sha256', $stagedPath),
                'width' => $width,
                'height' => $height,
                'alt_text' => trim($altText),
                'variants' => $variants,
            ];
        } catch (Throwable $error) {
            foreach ($stagedPaths as $path) {
                if (is_file($path)) @unlink($path);
            }
            throw $error;
        } finally {
            if (is_file($rawPath)) @unlink($rawPath);
            if ($image !== false) @imagedestroy($image);
        }
    }

    public function stageAttachment(array $upload): array
    {
        $this->assertUpload($upload, 20 * 1024 * 1024);
        $source = (string)$upload['tmp_name'];
        $originalName = $this->safeOriginalName((string)$upload['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'hwp', 'hwpx', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip'];
        if (!in_array($extension, $allowed, true)) {
            throw new ContentUploadException('Unsupported attachment extension.');
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file($source);
        $prefix = (string)file_get_contents($source, false, null, 0, 8);
        $isZip = substr($prefix, 0, 4) === "PK\x03\x04" || substr($prefix, 0, 4) === "PK\x05\x06" || substr($prefix, 0, 4) === "PK\x07\x08";
        $isOle = $prefix === "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1";
        $isPdf = substr($prefix, 0, 5) === '%PDF-';

        if ($extension === 'pdf') {
            if (!$isPdf || $mime !== 'application/pdf') {
                throw new ContentUploadException('Attachment content does not match PDF.');
            }
        } elseif (in_array($extension, ['hwp', 'doc', 'xls', 'ppt'], true)) {
            $oleMimes = ['application/x-ole-storage', 'application/vnd.ms-office', 'application/msword', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint', 'application/x-hwp', 'application/vnd.hancom.hwp', 'application/octet-stream'];
            if (!$isOle || !in_array($mime, $oleMimes, true)) {
                throw new ContentUploadException('Attachment content does not match its document type.');
            }
        } else {
            $zipMimes = ['application/zip', 'application/x-zip', 'application/x-zip-compressed', 'application/octet-stream'];
            if (!$isZip || !in_array($mime, $zipMimes, true)) {
                throw new ContentUploadException('Attachment content does not match a ZIP container.');
            }
            $this->inspectZip($source, $extension);
        }

        $storageName = bin2hex(random_bytes(32));
        $stagedPath = $this->staging . DIRECTORY_SEPARATOR . $storageName;
        if (!(($this->uploadMover)($source, $stagedPath))) {
            throw new ContentUploadException('Attachment upload could not be staged.');
        }
        @chmod($stagedPath, 0600);
        return [
            'staged_path' => $stagedPath,
            'purpose' => 'attachment',
            'storage_name' => $storageName,
            'original_name' => $originalName,
            'mime' => $mime,
            'byte_size' => filesize($stagedPath),
            'sha256' => hash_file('sha256', $stagedPath),
            'width' => null,
            'height' => null,
            'alt_text' => null,
        ];
    }

    public function stageForType(string $type, array $upload, string $altText = ''): array
    {
        if ($type === 'activity') {
            return $this->stageCover($upload, $altText);
        }
        if ($type === 'resource') {
            return $this->stageAttachment($upload);
        }
        throw new ContentUploadException('This content type does not accept uploads.');
    }

    public function promote(array $staged): array
    {
        $items = [[
            'storage_name' => (string)($staged['storage_name'] ?? ''),
            'staged_path' => (string)($staged['staged_path'] ?? ''),
        ]];
        foreach (($staged['variants'] ?? []) as $variant) {
            $items[] = $variant;
        }
        foreach ($items as $item) {
            if (!preg_match('/^[a-f0-9]{64}$/', (string)($item['storage_name'] ?? ''))
                || !is_file((string)($item['staged_path'] ?? ''))
                || dirname((string)$item['staged_path']) !== $this->staging) {
                throw new ContentUploadException('Invalid staged file.');
            }
        }
        $promoted = [];
        try {
            foreach ($items as $item) {
                $destination = $this->root . DIRECTORY_SEPARATOR . $item['storage_name'];
                if (!rename($item['staged_path'], $destination)) {
                    throw new ContentUploadException('Staged file could not be promoted.');
                }
                @chmod($destination, 0600);
                $promoted[] = $destination;
            }
        } catch (Throwable $error) {
            foreach ($promoted as $path) {
                if (is_file($path)) @unlink($path);
            }
            throw $error;
        }
        $stored = $staged;
        unset($stored['staged_path']);
        foreach ($stored['variants'] ?? [] as &$variant) {
            unset($variant['staged_path']);
        }
        unset($variant);
        return $stored;
    }

    public function discard(array $staged): void
    {
        $paths = [(string)($staged['staged_path'] ?? '')];
        foreach (($staged['variants'] ?? []) as $variant) {
            $paths[] = (string)($variant['staged_path'] ?? '');
        }
        foreach ($paths as $path) {
            if ($path !== '' && dirname($path) === $this->staging && is_file($path)) @unlink($path);
        }
    }

    public function remove(string $storageName): bool
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $storageName)) {
            return false;
        }
        $removed = true;
        foreach (array_merge([$storageName], array_map(function (int $width) use ($storageName): string {
            return $this->variantStorageName($storageName, $width);
        }, [480, 960])) as $name) {
            $path = $this->root . DIRECTORY_SEPARATOR . $name;
            if (is_file($path) && !@unlink($path)) $removed = false;
        }
        return $removed;
    }

    public function pathFor(string $storageName): ?string
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $storageName)) {
            return null;
        }
        return $this->root . DIRECTORY_SEPARATOR . $storageName;
    }

    public function pathForVariant(string $storageName, int $width): ?string
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $storageName) || !in_array($width, [480, 960], true)) {
            return null;
        }
        return $this->root . DIRECTORY_SEPARATOR . $this->variantStorageName($storageName, $width);
    }

    private function variantStorageName(string $storageName, int $width): string
    {
        return hash('sha256', $storageName . ':w' . $width);
    }

    private function writeResizedWebp($image, int $sourceWidth, int $sourceHeight, int $width, int $height, string $path): void
    {
        $output = imagecreatetruecolor($width, $height);
        if ($output === false) throw new ContentUploadException('Cover image could not be resized.');
        try {
            imagealphablending($output, false);
            imagesavealpha($output, true);
            $transparent = imagecolorallocatealpha($output, 0, 0, 0, 127);
            imagefilledrectangle($output, 0, 0, $width, $height, $transparent);
            if (!imagecopyresampled($output, $image, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight)) {
                throw new ContentUploadException('Cover image could not be resized.');
            }
            if (!imagewebp($output, $path, 82)) {
                throw new ContentUploadException('Cover image could not be encoded.');
            }
            @chmod($path, 0600);
        } finally {
            imagedestroy($output);
        }
    }

    private function assertUpload(array $upload, int $maxBytes): void
    {
        if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new ContentUploadException('Upload did not complete.');
        }
        $source = (string)($upload['tmp_name'] ?? '');
        $size = (int)($upload['size'] ?? -1);
        if ($source === '' || !is_file($source) || $size < 1 || $size > $maxBytes || filesize($source) !== $size) {
            throw new ContentUploadException('Upload size is invalid.');
        }
    }

    private function decodeImage(string $path, string $mime)
    {
        if ($mime === 'image/jpeg') {
            return @imagecreatefromjpeg($path);
        }
        if ($mime === 'image/png') {
            return @imagecreatefrompng($path);
        }
        return @imagecreatefromwebp($path);
    }

    private function orientJpeg($image, string $path)
    {
        if (!function_exists('exif_read_data')) {
            return $image;
        }
        $exif = @exif_read_data($path);
        $orientation = (int)($exif['Orientation'] ?? 1);
        $rotated = false;
        if ($orientation === 3) {
            $rotated = imagerotate($image, 180, 0);
        } elseif ($orientation === 6) {
            $rotated = imagerotate($image, -90, 0);
        } elseif ($orientation === 8) {
            $rotated = imagerotate($image, 90, 0);
        }
        if ($rotated !== false) {
            imagedestroy($image);
            return $rotated;
        }
        return $image;
    }

    private function inspectZip(string $path, string $extension): void
    {
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CHECKCONS) !== true) {
            throw new ContentUploadException('Invalid ZIP archive.');
        }
        try {
            if ($zip->numFiles > 1000) {
                throw new ContentUploadException('ZIP archive has too many entries.');
            }
            $expanded = 0;
            $familyFound = $extension === 'zip';
            $familyPrefix = ['docx'=>'word/', 'xlsx'=>'xl/', 'pptx'=>'pptx/', 'hwpx'=>'Contents/content.hpf'][$extension] ?? null;
            if ($extension === 'pptx') {
                $familyPrefix = 'ppt/';
            }
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $stat = $zip->statIndex($index);
                if (!is_array($stat)) {
                    throw new ContentUploadException('ZIP entry is unreadable.');
                }
                $name = (string)$stat['name'];
                if ($name === '' || strpos($name, "\0") !== false || preg_match('#^(?:/|\\\\|[A-Za-z]:)#', $name)) {
                    throw new ContentUploadException('ZIP entry path is unsafe.');
                }
                $segments = preg_split('#[\\\\/]#', $name);
                if (in_array('..', $segments ?: [], true)) {
                    throw new ContentUploadException('ZIP entry path is unsafe.');
                }
                $expanded += (int)($stat['size'] ?? 0);
                if ($expanded > 200 * 1024 * 1024) {
                    throw new ContentUploadException('ZIP archive expands beyond the allowed size.');
                }
                $entryExtension = strtolower(pathinfo(rtrim($name, '/'), PATHINFO_EXTENSION));
                if (in_array($entryExtension, ['php','phtml','phar','html','htm','svg','js','exe','dll','sh','bat'], true)) {
                    throw new ContentUploadException('ZIP archive contains an unsafe file type.');
                }
                $opsys = 0;
                $attributes = 0;
                if ($zip->getExternalAttributesIndex($index, $opsys, $attributes)) {
                    $mode = ($attributes >> 16) & 0170000;
                    if ($mode === 0120000) {
                        throw new ContentUploadException('ZIP archive contains a symbolic link.');
                    }
                }
                if ($familyPrefix !== null && ($name === $familyPrefix || strpos($name, $familyPrefix) === 0)) {
                    $familyFound = true;
                }
            }
            if (!$familyFound) {
                throw new ContentUploadException('ZIP document family marker is missing.');
            }
        } finally {
            $zip->close();
        }
    }

    private function safeOriginalName(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));
        $name = preg_replace('/[[:cntrl:]\\\\\/]+/u', ' ', $name) ?? '';
        $name = trim(preg_replace('/\s+/u', ' ', $name) ?? '');
        $name = mb_substr($name, 0, 255, 'UTF-8');
        return $name !== '' ? $name : 'download';
    }
}
