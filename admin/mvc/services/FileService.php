<?php
/**
 * FileService - 파일 처리 서비스
 * board_templates 보안 패턴 적용
 */

class FileService 
{
    private $allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif'];
    private $allowedDocumentTypes = ['pdf', 'doc', 'docx', 'hwp', 'hwpx', 'txt'];
    private $maxFileSize = 5 * 1024 * 1024; // 5MB
    
    /**
     * 이미지 업로드
     */
    public function uploadImage($file, $uploadPath) 
    {
        return $this->uploadFile($file, $uploadPath, $this->allowedImageTypes);
    }
    
    /**
     * 문서 업로드
     */
    public function uploadDocument($file, $uploadPath) 
    {
        return $this->uploadFile($file, $uploadPath, $this->allowedDocumentTypes);
    }
    
    /**
     * 파일 업로드 (범용)
     */
    public function uploadFile($file, $uploadPath, $allowedTypes = null) 
    {
        try {
            // 기본 허용 타입 설정
            if ($allowedTypes === null) {
                $allowedTypes = array_merge($this->allowedImageTypes, $this->allowedDocumentTypes);
            }
            
            // 파일 검증
            $this->validateFile($file, $allowedTypes);
            
            // 업로드 디렉토리 생성
            $this->ensureDirectory($uploadPath);
            
            // 안전한 파일명 생성
            $filename = $this->generateSafeFilename($file);
            $targetPath = $uploadPath . '/' . $filename;
            
            // 파일 이동
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new Exception('파일 저장 중 오류가 발생했습니다.');
            }
            
            // 파일 권한 설정
            chmod($targetPath, 0644);
            
            return $filename;
            
        } catch (Exception $e) {
            logSecurityEvent('FILE_UPLOAD_ERROR', $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 파일 검증
     */
    protected function validateFile($file, $allowedTypes) 
    {
        // 업로드 오류 확인
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception($this->getUploadErrorMessage($file['error']));
        }
        
        // 파일 크기 확인
        if ($file['size'] > $this->maxFileSize) {
            $maxSizeMB = $this->maxFileSize / (1024 * 1024);
            throw new Exception("파일 크기는 {$maxSizeMB}MB를 초과할 수 없습니다.");
        }
        
        // 파일 확장자 확인
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedTypes)) {
            throw new Exception('허용되지 않은 파일 형식입니다. 허용 형식: ' . implode(', ', $allowedTypes));
        }
        
        // 실제 MIME 타입 확인
        $this->validateMimeType($file, $extension);
        
        // 악성 파일 검사
        $this->scanMalicious($file);
    }
    
    /**
     * MIME 타입 검증
     */
    protected function validateMimeType($file, $extension) 
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimes = [
            'jpg' => ['image/jpeg', 'image/jpg'],
            'jpeg' => ['image/jpeg', 'image/jpg'], 
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'hwp' => ['application/haansofthwp', 'application/x-hwp'],
            'hwpx' => ['application/haansofthwpx'],
            'txt' => ['text/plain']
        ];
        
        if (isset($allowedMimes[$extension])) {
            if (!in_array($mimeType, $allowedMimes[$extension])) {
                throw new Exception('파일 형식이 올바르지 않습니다.');
            }
        }
    }
    
    /**
     * 악성 파일 검사
     */
    protected function scanMalicious($file) 
    {
        // 파일 시그니처 검사
        $handle = fopen($file['tmp_name'], 'rb');
        if (!$handle) {
            throw new Exception('파일을 읽을 수 없습니다.');
        }
        
        $header = fread($handle, 1024);
        fclose($handle);
        
        // PHP 태그 검사
        if (strpos($header, '<?php') !== false || strpos($header, '<?=') !== false) {
            throw new Exception('보안상 위험한 파일입니다.');
        }
        
        // 스크립트 태그 검사
        if (preg_match('/<script|javascript:/i', $header)) {
            throw new Exception('보안상 위험한 파일입니다.');
        }
    }
    
    /**
     * 안전한 파일명 생성
     */
    protected function generateSafeFilename($file) 
    {
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $timestamp = date('YmdHis');
        $random = uniqid();
        
        return "{$timestamp}_{$random}.{$extension}";
    }
    
    /**
     * 디렉토리 생성
     */
    protected function ensureDirectory($path) 
    {
        if (!is_dir($path)) {
            if (!mkdir($path, 0755, true)) {
                throw new Exception('업로드 디렉토리를 생성할 수 없습니다.');
            }
        }
        
        if (!is_writable($path)) {
            throw new Exception('업로드 디렉토리에 쓰기 권한이 없습니다.');
        }
    }
    
    /**
     * 파일 삭제
     */
    public function deleteFile($filePath) 
    {
        if (file_exists($filePath)) {
            if (!unlink($filePath)) {
                logSecurityEvent('FILE_DELETE_ERROR', "Failed to delete: {$filePath}");
                throw new Exception('파일 삭제 중 오류가 발생했습니다.');
            }
        }
        
        return true;
    }
    
    /**
     * 이미지 크기 조정
     */
    public function resizeImage($sourcePath, $targetPath, $maxWidth = 800, $maxHeight = 600) 
    {
        try {
            $imageInfo = getimagesize($sourcePath);
            if (!$imageInfo) {
                throw new Exception('유효하지 않은 이미지 파일입니다.');
            }
            
            list($originalWidth, $originalHeight, $imageType) = $imageInfo;
            
            // 크기 조정이 필요한지 확인
            if ($originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
                copy($sourcePath, $targetPath);
                return true;
            }
            
            // 비율 계산
            $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
            $newWidth = round($originalWidth * $ratio);
            $newHeight = round($originalHeight * $ratio);
            
            // 소스 이미지 생성
            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    $sourceImage = imagecreatefromjpeg($sourcePath);
                    break;
                case IMAGETYPE_PNG:
                    $sourceImage = imagecreatefrompng($sourcePath);
                    break;
                case IMAGETYPE_GIF:
                    $sourceImage = imagecreatefromgif($sourcePath);
                    break;
                default:
                    throw new Exception('지원되지 않는 이미지 형식입니다.');
            }
            
            // 새 이미지 생성
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // PNG와 GIF의 투명도 처리
            if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
            }
            
            // 이미지 크기 조정
            imagecopyresampled(
                $newImage, $sourceImage,
                0, 0, 0, 0,
                $newWidth, $newHeight,
                $originalWidth, $originalHeight
            );
            
            // 이미지 저장
            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    imagejpeg($newImage, $targetPath, 85);
                    break;
                case IMAGETYPE_PNG:
                    imagepng($newImage, $targetPath);
                    break;
                case IMAGETYPE_GIF:
                    imagegif($newImage, $targetPath);
                    break;
            }
            
            // 메모리 해제
            imagedestroy($sourceImage);
            imagedestroy($newImage);
            
            return true;
            
        } catch (Exception $e) {
            logSecurityEvent('IMAGE_RESIZE_ERROR', $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 업로드 오류 메시지
     */
    protected function getUploadErrorMessage($errorCode) 
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return '파일 크기가 서버 설정을 초과합니다.';
            case UPLOAD_ERR_FORM_SIZE:
                return '파일 크기가 허용 한도를 초과합니다.';
            case UPLOAD_ERR_PARTIAL:
                return '파일이 부분적으로만 업로드되었습니다.';
            case UPLOAD_ERR_NO_FILE:
                return '파일이 업로드되지 않았습니다.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return '임시 디렉토리가 없습니다.';
            case UPLOAD_ERR_CANT_WRITE:
                return '디스크 쓰기에 실패했습니다.';
            case UPLOAD_ERR_EXTENSION:
                return '확장에 의해 업로드가 중단되었습니다.';
            default:
                return '알 수 없는 업로드 오류가 발생했습니다.';
        }
    }
    
    /**
     * 파일 크기 포맷팅
     */
    public function formatFileSize($bytes) 
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}