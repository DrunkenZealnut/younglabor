<?php
require_once __DIR__ . '/src/Core/BoardServiceContainer.php';

use BoardTemplates\Core\BoardServiceContainer;

// Board Templates 서비스 컨테이너 초기화
// 전역 변수로 설정하여 모든 템플릿에서 접근 가능하도록 함
if (!isset($GLOBALS['board_service_container'])) {
    $GLOBALS['board_service_container'] = BoardServiceContainer::createAuto();
}

// 이전 버전과의 호환성을 위한 상수 정의
$config = $GLOBALS['board_service_container']->get('config');
$fileConfig = $config->getFileConfig();
$urlConfig = $config->getUrlConfig();

// 파일 물리 저장 경로
if (!defined('BOARD_TEMPLATES_FILE_BASE_PATH')) {
    define('BOARD_TEMPLATES_FILE_BASE_PATH', $fileConfig['upload_base_path']);
}

// 파일 베이스 URL
if (!defined('BOARD_TEMPLATES_FILE_BASE_URL')) {
    define('BOARD_TEMPLATES_FILE_BASE_URL', $fileConfig['upload_base_url']);
}

// 다운로드 권한 체크
if (!defined('BOARD_TEMPLATES_DOWNLOAD_OPEN')) {
    define('BOARD_TEMPLATES_DOWNLOAD_OPEN', !$fileConfig['download_permission']);
}

?>


