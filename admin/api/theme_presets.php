<?php
/**
 * 테마 프리셋 관리 API 엔드포인트
 * 
 * 사용자 정의 테마 색상 프리셋의 CRUD 작업을 처리
 */

header('Content-Type: application/json; charset=utf-8');

// 인증 확인
require_once '../auth.php';

// MVC 시스템 로드
require_once '../mvc/bootstrap.php';
require_once '../mvc/services/ThemeService.php';

try {
    // CSRF 토큰 확인
    if ($_SERVER['REQUEST_METHOD'] !== 'GET' && (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token']))) {
        throw new Exception('CSRF 토큰이 유효하지 않습니다.');
    }
    
    // 테마 서비스 초기화
    $themeService = new ThemeService($pdo);
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    switch ($method) {
        case 'GET':
            handleGetRequest($themeService, $action);
            break;
            
        case 'POST':
            handlePostRequest($themeService, $action);
            break;
            
        case 'PUT':
        case 'PATCH':
            handlePutRequest($themeService, $action);
            break;
            
        case 'DELETE':
            handleDeleteRequest($themeService, $action);
            break;
            
        default:
            throw new Exception('지원하지 않는 HTTP 메소드입니다.');
    }
    
} catch (Exception $e) {
    // 오류 응답
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * GET 요청 처리
 */
function handleGetRequest($themeService, $action)
{
    switch ($action) {
        case 'list':
            // 테마 프리셋 목록 조회
            $activeOnly = ($_GET['active_only'] ?? 'true') === 'true';
            $presets = $themeService->getThemePresets($activeOnly);
            
            echo json_encode([
                'success' => true,
                'data' => $presets,
                'count' => count($presets),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'get':
            // 특정 테마 프리셋 조회
            $presetId = $_GET['id'] ?? null;
            
            if (!$presetId) {
                throw new Exception('프리셋 ID가 필요합니다.');
            }
            
            $preset = $themeService->getThemePreset($presetId);
            
            echo json_encode([
                'success' => true,
                'data' => $preset,
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        default:
            // 기본적으로 목록 조회
            $presets = $themeService->getThemePresets(true);
            
            echo json_encode([
                'success' => true,
                'data' => $presets,
                'count' => count($presets),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
    }
}

/**
 * POST 요청 처리 (생성)
 */
function handlePostRequest($themeService, $action)
{
    switch ($action) {
        case 'save':
        case 'create':
            // 새 테마 프리셋 저장
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '') ?: null;
            $createdBy = $_SESSION['admin_username'] ?? 'admin';
            
            if (empty($name)) {
                throw new Exception('테마 이름을 입력해주세요.');
            }
            
            // 색상 데이터 처리
            $colors = [];
            if (isset($_POST['colors'])) {
                if (is_string($_POST['colors'])) {
                    $colors = json_decode($_POST['colors'], true);
                } else {
                    $colors = $_POST['colors'];
                }
            }
            
            if (empty($colors)) {
                throw new Exception('색상 데이터가 필요합니다.');
            }
            
            $presetId = $themeService->saveThemePreset($name, $colors, $description, $createdBy);
            
            echo json_encode([
                'success' => true,
                'message' => '테마가 성공적으로 저장되었습니다.',
                'preset_id' => $presetId,
                'preset_name' => $name,
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'save_current':
            // 현재 테마를 새 프리셋으로 저장
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '') ?: null;
            $createdBy = $_SESSION['admin_username'] ?? 'admin';
            
            if (empty($name)) {
                throw new Exception('테마 이름을 입력해주세요.');
            }
            
            $presetId = $themeService->saveCurrentThemeAsPreset($name, $description, $createdBy);
            
            echo json_encode([
                'success' => true,
                'message' => '현재 테마가 성공적으로 저장되었습니다.',
                'preset_id' => $presetId,
                'preset_name' => $name,
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'load':
            // 테마 프리셋 불러오기 (현재 테마에 적용)
            $presetId = $_POST['id'] ?? null;
            
            if (!$presetId) {
                throw new Exception('프리셋 ID가 필요합니다.');
            }
            
            $cssFile = $themeService->loadThemePreset($presetId);
            $preset = $themeService->getThemePreset($presetId);
            
            echo json_encode([
                'success' => true,
                'message' => '테마가 성공적으로 적용되었습니다.',
                'preset_name' => $preset['preset_name'],
                'css_file' => basename($cssFile),
                'colors' => $preset['colors'],
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        default:
            throw new Exception('알 수 없는 액션입니다: ' . $action);
    }
}

/**
 * PUT/PATCH 요청 처리 (업데이트)
 */
function handlePutRequest($themeService, $action)
{
    // PUT 데이터 파싱
    parse_str(file_get_contents("php://input"), $putData);
    
    switch ($action) {
        case 'update':
            $presetId = $putData['id'] ?? null;
            
            if (!$presetId) {
                throw new Exception('프리셋 ID가 필요합니다.');
            }
            
            $name = isset($putData['name']) ? trim($putData['name']) : null;
            $description = isset($putData['description']) ? trim($putData['description']) : null;
            $colors = null;
            
            if (isset($putData['colors'])) {
                if (is_string($putData['colors'])) {
                    $colors = json_decode($putData['colors'], true);
                } else {
                    $colors = $putData['colors'];
                }
            }
            
            $themeService->updateThemePreset($presetId, $name, $colors, $description);
            
            echo json_encode([
                'success' => true,
                'message' => '테마가 성공적으로 업데이트되었습니다.',
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'reorder':
            $presetId = $putData['id'] ?? null;
            $newOrder = $putData['order'] ?? null;
            
            if (!$presetId || !is_numeric($newOrder)) {
                throw new Exception('프리셋 ID와 순서가 필요합니다.');
            }
            
            $themeService->updatePresetOrder($presetId, (int)$newOrder);
            
            echo json_encode([
                'success' => true,
                'message' => '테마 순서가 성공적으로 변경되었습니다.',
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        default:
            throw new Exception('알 수 없는 액션입니다: ' . $action);
    }
}

/**
 * DELETE 요청 처리
 */
function handleDeleteRequest($themeService, $action)
{
    // DELETE 데이터 파싱
    parse_str(file_get_contents("php://input"), $deleteData);
    
    $presetId = $deleteData['id'] ?? $_GET['id'] ?? null;
    
    if (!$presetId) {
        throw new Exception('프리셋 ID가 필요합니다.');
    }
    
    $themeService->deleteThemePreset($presetId);
    
    echo json_encode([
        'success' => true,
        'message' => '테마가 성공적으로 삭제되었습니다.',
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE);
}
?>