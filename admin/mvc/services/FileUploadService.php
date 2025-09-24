<?php

/**
 * FileUploadService - 파일 업로드 서비스 클래스
 * Admin_templates의 파일 업로드 기능을 MVC 패턴으로 구현
 */
class FileUploadService 
{
    private $uploadDir;
    private $allowedTypes;
    private $maxFileSize;
    private $allowedImageTypes;
    private $allowedDocumentTypes;
    
    public function __construct($config = []) 
    {
        $this->uploadDir = $config['upload_dir'] ?? '../uploads/';
        $this->maxFileSize = $config['max_file_size'] ?? 5242880; // 5MB
        
        $this->allowedImageTypes = $config['allowed_image_types'] ?? [
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'
        ];
        
        $this->allowedDocumentTypes = $config['allowed_document_types'] ?? [
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 
            'hwp', 'txt', 'zip', 'rar', '7z'
        ];
        
        $this->allowedTypes = array_merge($this->allowedImageTypes, $this->allowedDocumentTypes);
        
        // 업로드 디렉토리 생성
        $this->createUploadDirectories();
    }
    
    /**
     * 업로드 디렉토리 생성
     */
    private function createUploadDirectories()
    {
        $directories = [
            $this->uploadDir,
            $this->uploadDir . 'editor_images/',
            $this->uploadDir . 'board_documents/',
            $this->uploadDir . 'events/',
            $this->uploadDir . 'settings/',
            $this->uploadDir . 'temp/'
        ];
        
        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
    
    /**
     * 파일 업로드 처리
     */
    public function upload($file, $options = [])
    {
        try {
            // 파일 유효성 검사
            $validation = $this->validateFile($file, $options);
            if (!$validation['success']) {
                return [
                    'success' => false,
                    'error' => $validation['error']
                ];
            }
            
            // 업로드 타입에 따른 디렉토리 설정
            $uploadType = $options['type'] ?? 'general';
            $subDir = $this->getUploadDirectory($uploadType);
            $targetDir = $this->uploadDir . $subDir;
            
            // 파일명 생성
            $fileName = $this->generateFileName($file['name'], $options);
            $targetPath = $targetDir . $fileName;
            
            // 파일 이동
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // 이미지인 경우 썸네일 생성
                if ($this->isImageFile($fileName) && ($options['create_thumbnail'] ?? false)) {
                    $this->createThumbnail($targetPath, $options['thumbnail_size'] ?? [200, 200]);
                }
                
                return [
                    'success' => true,
                    'file_path' => $subDir . $fileName,
                    'file_name' => $fileName,
                    'original_name' => $file['name'],
                    'file_size' => $file['size'],
                    'file_type' => $this->getFileType($fileName),
                    'upload_time' => date('Y-m-d H:i:s')
                ];
            } else {
                return [
                    'success' => false,
                    'error' => '파일 업로드 중 오류가 발생했습니다.'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => '파일 업로드 처리 중 오류가 발생했습니다: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 다중 파일 업로드 처리
     */
    public function uploadMultiple($files, $options = [])
    {
        $results = [];
        
        // $_FILES 배열 정규화
        $normalizedFiles = $this->normalizeFilesArray($files);
        
        foreach ($normalizedFiles as $file) {
            if ($file['error'] === UPLOAD_ERR_OK) {
                $results[] = $this->upload($file, $options);
            } else {
                $results[] = [
                    'success' => false,
                    'error' => $this->getUploadErrorMessage($file['error']),
                    'original_name' => $file['name']
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * 에디터 이미지 업로드 (Summernote 등)
     */
    public function uploadEditorImage($file)
    {
        return $this->upload($file, [
            'type' => 'editor_image',
            'allowed_types' => $this->allowedImageTypes,
            'max_size' => 2097152, // 2MB
            'create_thumbnail' => false
        ]);
    }
    
    /**
     * 첨부파일 업로드 (게시판용)
     */
    public function uploadAttachment($file)
    {
        return $this->upload($file, [
            'type' => 'attachment',
            'allowed_types' => $this->allowedTypes,
            'max_size' => $this->maxFileSize
        ]);
    }
    
    /**
     * 이벤트 썸네일 업로드
     */
    public function uploadEventThumbnail($file)
    {
        return $this->upload($file, [
            'type' => 'event',
            'allowed_types' => $this->allowedImageTypes,
            'max_size' => 3145728, // 3MB
            'create_thumbnail' => true,
            'thumbnail_size' => [300, 200]
        ]);
    }
    
    /**
     * 파일 유효성 검사
     */
    private function validateFile($file, $options = [])
    {
        // 파일 업로드 에러 체크
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'error' => $this->getUploadErrorMessage($file['error'])
            ];
        }
        
        // 파일 크기 체크
        $maxSize = $options['max_size'] ?? $this->maxFileSize;
        if ($file['size'] > $maxSize) {
            return [
                'success' => false,
                'error' => '파일 크기가 최대 허용 크기(' . $this->formatFileSize($maxSize) . ')를 초과했습니다.'
            ];
        }
        
        // 파일 확장자 체크
        $allowedTypes = $options['allowed_types'] ?? $this->allowedTypes;
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedTypes)) {
            return [
                'success' => false,
                'error' => '허용되지 않는 파일 형식입니다. (' . implode(', ', $allowedTypes) . ')'
            ];
        }
        
        // MIME 타입 검사 (보안 강화)
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            $allowedMimes = $this->getAllowedMimeTypes($allowedTypes);
            if (!in_array($mimeType, $allowedMimes)) {
                return [
                    'success' => false,
                    'error' => '파일의 실제 형식이 확장자와 일치하지 않습니다.'
                ];
            }
        }
        
        // 이미지 파일인 경우 추가 검사
        if ($this->isImageFile($file['name'])) {
            if (!$this->validateImage($file['tmp_name'])) {
                return [
                    'success' => false,
                    'error' => '올바른 이미지 파일이 아닙니다.'
                ];
            }
        }
        
        return ['success' => true];
    }
    
    /**
     * 이미지 파일 검증
     */
    private function validateImage($filePath)
    {
        $imageInfo = @getimagesize($filePath);
        
        if ($imageInfo === false) {
            return false;
        }
        
        // 최대 이미지 크기 제한 (5000x5000)
        if ($imageInfo[0] > 5000 || $imageInfo[1] > 5000) {
            return false;
        }
        
        // 지원하는 이미지 타입 확인
        $allowedImageTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP, IMAGETYPE_BMP];
        return in_array($imageInfo[2], $allowedImageTypes);
    }
    
    /**
     * 허용된 MIME 타입 조회
     */
    private function getAllowedMimeTypes($allowedExtensions)
    {
        $mimeMap = [
            // 이미지
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            
            // 문서
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'hwp' => 'application/x-hwp',
            'txt' => 'text/plain',
            
            // 압축파일
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            '7z' => 'application/x-7z-compressed'
        ];
        
        $allowedMimes = [];
        foreach ($allowedExtensions as $ext) {
            if (isset($mimeMap[$ext])) {
                $allowedMimes[] = $mimeMap[$ext];
            }
        }
        
        return $allowedMimes;
    }
    
    /**
     * 업로드 디렉토리 결정
     */
    private function getUploadDirectory($type)
    {
        switch ($type) {
            case 'editor_image':
                return 'editor_images/';
            case 'attachment':
                return 'board_documents/';
            case 'event':
                return 'events/';
            case 'setting':
                return 'settings/';
            default:
                return '';
        }
    }
    
    /**
     * 파일명 생성
     */
    private function generateFileName($originalName, $options = [])
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        
        if ($options['keep_original_name'] ?? false) {
            // 원본 파일명 유지 (안전한 문자로 변환)
            $basename = pathinfo($originalName, PATHINFO_FILENAME);
            $safeBasename = preg_replace('/[^a-zA-Z0-9가-힣\-_]/', '_', $basename);
            return date('Ymd_') . $safeBasename . '.' . $extension;
        } else {
            // 고유한 파일명 생성
            return date('Ymd_His_') . uniqid() . '.' . $extension;
        }
    }
    
    /**
     * 썸네일 생성
     */
    private function createThumbnail($imagePath, $size = [200, 200])
    {
        if (!$this->isImageFile($imagePath)) {
            return false;
        }
        
        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) {
            return false;
        }
        
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $type = $imageInfo[2];
        
        // 원본 이미지 로드
        switch ($type) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($imagePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($imagePath);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($imagePath);
                break;
            default:
                return false;
        }
        
        if (!$sourceImage) {
            return false;
        }
        
        // 썸네일 크기 계산
        list($thumbWidth, $thumbHeight) = $size;
        $ratio = min($thumbWidth / $width, $thumbHeight / $height);
        $newWidth = intval($width * $ratio);
        $newHeight = intval($height * $ratio);
        
        // 썸네일 생성
        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
        
        // PNG 투명도 처리
        if ($type == IMAGETYPE_PNG) {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
            imagefill($thumbnail, 0, 0, $transparent);
        }
        
        imagecopyresampled($thumbnail, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // 썸네일 저장
        $thumbPath = dirname($imagePath) . '/thumb_' . basename($imagePath);
        
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($thumbnail, $thumbPath, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumbnail, $thumbPath, 6);
                break;
            case IMAGETYPE_GIF:
                imagegif($thumbnail, $thumbPath);
                break;
        }
        
        imagedestroy($sourceImage);
        imagedestroy($thumbnail);
        
        return $thumbPath;
    }
    
    /**
     * 파일 삭제
     */
    public function deleteFile($filePath)
    {
        $fullPath = $this->uploadDir . ltrim($filePath, '/');
        
        if (file_exists($fullPath)) {
            if (unlink($fullPath)) {
                // 썸네일도 삭제
                $thumbPath = dirname($fullPath) . '/thumb_' . basename($fullPath);
                if (file_exists($thumbPath)) {
                    unlink($thumbPath);
                }
                return true;
            }
        }
        return false;
    }
    
    /**
     * $_FILES 배열 정규화
     */
    private function normalizeFilesArray($files)
    {
        $normalized = [];
        
        if (isset($files['name']) && is_array($files['name'])) {
            // 다중 파일 업로드
            $fileCount = count($files['name']);
            for ($i = 0; $i < $fileCount; $i++) {
                $normalized[] = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
            }
        } else {
            // 단일 파일 업로드
            $normalized[] = $files;
        }
        
        return $normalized;
    }
    
    /**
     * 업로드 에러 메시지
     */
    private function getUploadErrorMessage($errorCode)
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'PHP 설정상 최대 업로드 크기를 초과했습니다.';
            case UPLOAD_ERR_FORM_SIZE:
                return 'HTML 폼에서 지정한 최대 파일 크기를 초과했습니다.';
            case UPLOAD_ERR_PARTIAL:
                return '파일이 부분적으로만 업로드되었습니다.';
            case UPLOAD_ERR_NO_FILE:
                return '업로드된 파일이 없습니다.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return '임시 디렉토리가 없습니다.';
            case UPLOAD_ERR_CANT_WRITE:
                return '디스크에 파일을 쓸 수 없습니다.';
            case UPLOAD_ERR_EXTENSION:
                return 'PHP 확장에 의해 파일 업로드가 중단되었습니다.';
            default:
                return '알 수 없는 업로드 오류가 발생했습니다.';
        }
    }
    
    /**
     * 이미지 파일 여부 확인
     */
    private function isImageFile($fileName)
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        return in_array($extension, $this->allowedImageTypes);
    }
    
    /**
     * 파일 타입 분류
     */
    private function getFileType($fileName)
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        if (in_array($extension, $this->allowedImageTypes)) {
            return 'image';
        } elseif (in_array($extension, ['pdf', 'doc', 'docx', 'hwp', 'txt'])) {
            return 'document';
        } elseif (in_array($extension, ['xls', 'xlsx'])) {
            return 'spreadsheet';
        } elseif (in_array($extension, ['ppt', 'pptx'])) {
            return 'presentation';
        } elseif (in_array($extension, ['zip', 'rar', '7z'])) {
            return 'archive';
        } else {
            return 'other';
        }
    }
    
    /**
     * 파일 크기 포맷팅
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}