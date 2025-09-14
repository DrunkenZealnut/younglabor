<?php
/**
 * 설정 가져오기 API 엔드포인트
 */

header('Content-Type: application/json; charset=utf-8');

session_start();

// 기본 인증 체크
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // 개발 환경에서는 자동 로그인
    if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
        strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false ||
        strpos($_SERVER['HTTP_HOST'], '.local') !== false) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => '인증이 필요합니다.']);
        exit;
    }
}

require_once __DIR__ . '/../includes/DatabaseSettingsManager.php';

use BoardTemplates\Admin\DatabaseSettingsManager;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('POST 요청만 지원됩니다.');
    }
    
    // 파일 업로드 또는 JSON 데이터 처리
    $configData = null;
    
    if (isset($_FILES['config_file'])) {
        // 파일 업로드 처리
        $uploadedFile = $_FILES['config_file'];
        
        if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('파일 업로드 중 오류가 발생했습니다.');
        }
        
        $fileContent = file_get_contents($uploadedFile['tmp_name']);
        $configData = json_decode($fileContent, true);
        
        if ($configData === null) {
            throw new Exception('유효하지 않은 JSON 파일입니다.');
        }
    } else {
        // JSON 데이터 직접 처리
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['config_data'])) {
            throw new Exception('설정 데이터가 없습니다.');
        }
        
        $configData = $input['config_data'];
    }
    
    $settingsManager = new DatabaseSettingsManager();
    $result = $settingsManager->importSettings($configData);
    
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => '설정을 성공적으로 가져왔습니다.',
            'imported_settings' => $result['imported_settings'] ?? [],
            'warnings' => $result['warnings'] ?? []
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception($result['message']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>