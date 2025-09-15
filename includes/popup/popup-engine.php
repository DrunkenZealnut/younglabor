<?php
/**
 * 팝업 엔진 - 프론트엔드 팝업 표시 로직
 * 
 * includes/header.php에서 호출되어 조건에 맞는 팝업을 표시합니다.
 */

// 팝업 시스템이 이미 로드되었는지 확인
if (defined('HOPEC_POPUP_ENGINE_LOADED')) {
    return;
}
define('HOPEC_POPUP_ENGINE_LOADED', true);

// 필요한 파일들 로드
require_once __DIR__ . '/../../admin/services/PopupManager.php';

// 현재 페이지와 사용자 정보
$currentPage = isset($currentSlug) ? $currentSlug : 'home';
$userIP = $_SERVER['REMOTE_ADDR'] ?? '';
$sessionId = session_id();
$currentUrl = $_SERVER['REQUEST_URI'] ?? '';

try {
    // 데이터베이스 연결 확인
    if (!isset($pdo)) {
        $dbConfigPath = __DIR__ . '/../../data/dbconfig.php';
        if (file_exists($dbConfigPath)) {
            include $dbConfigPath;
            
            // 그누보드 설정에서 변수 추출
            $db_host = defined('G5_MYSQL_HOST') ? G5_MYSQL_HOST : 'localhost';
            $db_user = defined('G5_MYSQL_USER') ? G5_MYSQL_USER : 'root';
            $db_pass = defined('G5_MYSQL_PASSWORD') ? G5_MYSQL_PASSWORD : '';
            $db_name = defined('G5_MYSQL_DB') ? G5_MYSQL_DB : 'hopec';
            
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } else {
            // 데이터베이스 연결 실패 시 조용히 종료
            return;
        }
    }
    
    // 팝업 매니저 초기화
    $popupManager = new PopupManager($pdo);
    
    // 활성 팝업 조회
    error_log("Popup Engine: Checking for active popups - Page: {$currentPage}, IP: {$userIP}");
    $activePopups = $popupManager->getActivePopups($currentPage, $userIP, $sessionId);
    
    error_log("Popup Engine: Found " . count($activePopups) . " active popups");
    
    if (empty($activePopups)) {
        error_log("Popup Engine: No active popups to display");
        return; // 표시할 팝업이 없음
    }
    
    // 첫 번째 팝업만 표시 (우선순위 순으로 정렬됨)
    $popup = $activePopups[0];
    
    // 팝업 데이터 파싱
    $popupId = $popup['id'];
    $title = htmlspecialchars($popup['title'], ENT_QUOTES, 'UTF-8');
    $content = $popup['content']; // HTML이므로 이스케이프하지 않음
    $styles = json_decode($popup['style_settings'], true) ?: [];
    
    // 조회 기록
    $popupManager->recordPopupView($popupId, $userIP, $sessionId, 'viewed', $currentUrl);
    
    // 팝업 HTML 생성
    $popupHtml = generatePopupHtml($popupId, $title, $content, $styles);
    
    // JavaScript 초기화 코드 생성
    $initScript = generatePopupScript($popupId, $styles);
    
    // 출력
    echo $popupHtml;
    echo $initScript;
    
} catch (Exception $e) {
    // 오류 발생 시 로그에 기록하고 조용히 실패
    error_log("Popup Engine Error: " . $e->getMessage());
    return;
}

/**
 * 팝업 HTML 생성
 */
function generatePopupHtml($popupId, $title, $content, $styles) {
    // 기본 스타일 설정
    $width = $styles['width'] ?? '500';
    $bgColor = $styles['bg_color'] ?? '#ffffff';
    $borderRadius = $styles['border_radius'] ?? '12';
    $animation = $styles['animation'] ?? 'fade';
    $overlayColor = $styles['overlay_color'] ?? 'rgba(0,0,0,0.5)';
    
    // 애니메이션 클래스
    $animationClass = '';
    switch ($animation) {
        case 'slide':
            $animationClass = 'popup-slide-in';
            break;
        case 'bounce':
            $animationClass = 'popup-bounce-in';
            break;
        case 'fade':
        default:
            $animationClass = 'popup-fade-in';
            break;
    }
    
    $html = '
    <!-- Hopec 팝업 시스템 -->
    <div class="remodal hopec-popup ' . $animationClass . '" 
         data-remodal-id="hopec-popup-' . $popupId . '"
         data-remodal-options="hashTracking: false, closeOnOutsideClick: true, modifier: fade"
         style="max-width: ' . $width . 'px; background-color: ' . $bgColor . '; border-radius: ' . $borderRadius . 'px;">
         
        <!-- 팝업 헤더 -->
        <div class="hopec-popup-header">
            <h3 class="hopec-popup-title">' . $title . '</h3>
            <button data-remodal-action="close" class="remodal-close hopec-popup-close" 
                    onclick="hopecPopupClosed(' . $popupId . ')">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        
        <!-- 팝업 내용 -->
        <div class="hopec-popup-content">
            ' . $content . '
        </div>
        
        <!-- 팝업 액션 -->
        <div class="hopec-popup-actions">
            <div class="hopec-popup-checkbox">
                <label class="checkbox-label">
                    <input type="checkbox" id="hopec-popup-no-show-' . $popupId . '" 
                           onchange="hopecPopupCheckboxChanged(' . $popupId . ', this.checked)">
                    <span class="checkbox-text">24시간 동안 안보이기</span>
                </label>
            </div>
            <div class="hopec-popup-buttons">
                <button data-remodal-action="close" class="btn btn-secondary btn-sm" 
                        onclick="hopecPopupClosed(' . $popupId . ')">
                    닫기
                </button>
            </div>
        </div>
    </div>
    
    <!-- 팝업 스타일 -->
    <style>
    .hopec-popup {
        font-family: "Noto Sans KR", Arial, sans-serif;
        padding: 0;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        transition: opacity 0.3s ease-out, transform 0.3s ease-out;
    }
    
    .hopec-popup-header {
        background: linear-gradient(135deg, #84cc16, #22c55e);
        color: white;
        padding: 20px;
        position: relative;
        border-radius: ' . $borderRadius . 'px ' . $borderRadius . 'px 0 0;
    }
    
    .hopec-popup-title {
        margin: 0;
        font-size: 1.2em;
        font-weight: 600;
        padding-right: 40px;
    }
    
    .hopec-popup-close {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .hopec-popup-close:hover {
        background: rgba(255, 255, 255, 0.3);
    }
    
    .hopec-popup-content {
        padding: 30px;
        line-height: 1.6;
        color: #333;
    }
    
    .hopec-popup-content h1,
    .hopec-popup-content h2,
    .hopec-popup-content h3,
    .hopec-popup-content h4,
    .hopec-popup-content h5,
    .hopec-popup-content h6 {
        margin-top: 0;
        margin-bottom: 15px;
        color: #84cc16;
    }
    
    .hopec-popup-content p {
        margin-bottom: 15px;
    }
    
    .hopec-popup-actions {
        padding: 20px 30px;
        border-top: 1px solid #e5e7eb;
        background-color: #f9fafb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 0 0 ' . $borderRadius . 'px ' . $borderRadius . 'px;
    }
    
    .hopec-popup-checkbox {
        display: flex;
        align-items: center;
    }
    
    .hopec-popup-checkbox .checkbox-label {
        display: flex;
        align-items: center;
        cursor: pointer;
        font-size: 14px;
        color: #6b7280;
        margin: 0;
    }
    
    .hopec-popup-checkbox input[type="checkbox"] {
        width: 16px;
        height: 16px;
        margin-right: 8px;
        accent-color: #84cc16;
        cursor: pointer;
    }
    
    .hopec-popup-checkbox .checkbox-text {
        user-select: none;
    }
    
    .hopec-popup-buttons {
        display: flex;
        gap: 10px;
    }
    
    /* 애니메이션 효과 - 깜빡임 방지 개선 */
    .hopec-popup.popup-fade-in {
        opacity: 0;
        visibility: hidden;
        transform: scale(0.95);
        transition: opacity 0.3s ease-out, transform 0.3s ease-out, visibility 0s 0.3s;
    }
    
    .hopec-popup.popup-slide-in {
        opacity: 0;
        visibility: hidden;
        transform: translateY(-20px);
        transition: opacity 0.3s ease-out, transform 0.3s ease-out, visibility 0s 0.3s;
    }
    
    .hopec-popup.popup-bounce-in {
        opacity: 0;
        visibility: hidden;
        transform: scale(0.9);
        transition: opacity 0.3s ease-out, transform 0.3s ease-out, visibility 0s 0.3s;
    }
    
    /* Remodal 상태별 스타일 - 깜빡임 방지 */
    .remodal.hopec-popup.remodal-is-opened {
        opacity: 1 !important;
        visibility: visible !important;
        transform: scale(1) !important;
        transition: opacity 0.3s ease-out, transform 0.3s ease-out, visibility 0s;
    }
    
    .remodal.hopec-popup.remodal-is-opening {
        animation: none !important;
        opacity: 1 !important;
        visibility: visible !important;
    }
    
    .remodal.hopec-popup.remodal-is-closing {
        animation: none !important;
        transition: opacity 0.2s ease-in, transform 0.2s ease-in, visibility 0s 0.2s;
    }
    
    /* Remodal 오버레이 - 안정적인 표시 */
    .remodal-overlay {
        transition: opacity 0.3s ease-out;
    }
    
    .remodal-overlay.remodal-is-opened {
        opacity: 1 !important;
    }
    
    .remodal-wrapper.remodal-is-opened {
        opacity: 1 !important;
    }
    
    /* 모바일 최적화 */
    @media (max-width: 768px) {
        .hopec-popup {
            margin: 20px;
            max-width: calc(100vw - 40px) !important;
        }
        
        .hopec-popup-content {
            padding: 20px;
        }
        
        .hopec-popup-actions {
            padding: 15px 20px;
        }
    }
    
    /* 오버레이 색상 */
    .remodal-overlay {
        background-color: ' . $overlayColor . ';
    }
    </style>';
    
    return $html;
}

/**
 * 팝업 JavaScript 초기화 코드 생성
 */
function generatePopupScript($popupId, $styles) {
    $delay = $styles['show_delay'] ?? 100; // 기본 0.1초 후 표시
    
    $script = '
    <script>
    // 팝업 쿠키 관리
    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }
    
    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(";");
        for(var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == " ") c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }
    
    // 24시간 안보이기 체크박스 변경 이벤트
    function hopecPopupCheckboxChanged(popupId, checked) {
        if (checked) {
            // 24시간 동안 안보이기 쿠키 설정
            setCookie("hopec_popup_" + popupId + "_no_show_24h", "1", 1);
            console.log("24시간 동안 팝업 " + popupId + " 안보이기 설정됨");
        } else {
            // 쿠키 삭제
            setCookie("hopec_popup_" + popupId + "_no_show_24h", "", -1);
            console.log("24시간 안보이기 설정 해제됨");
        }
    }
    
    // 팝업 닫기 이벤트
    function hopecPopupClosed(popupId) {
        // 24시간 안보이기가 체크되어 있는지 확인
        var noShowCheckbox = document.getElementById("hopec-popup-no-show-" + popupId);
        var noShow24h = noShowCheckbox && noShowCheckbox.checked;
        
        // 조회 기록 전송
        fetch("/admin/settings/popup/api/record-action.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                popup_id: popupId,
                action: "closed",
                no_show_24h: noShow24h
            })
        }).catch(function(error) {
            console.warn("팝업 액션 기록 실패:", error);
        });
        
        if (noShow24h) {
            // 24시간 동안 안보이기 쿠키 설정
            setCookie("hopec_popup_" + popupId + "_no_show_24h", "1", 1);
        }
        // 일반 닫기는 쿠키 설정하지 않음 (즉시 재표시 가능)
    }
    
    // 팝업 클릭 이벤트
    function hopecPopupClicked(popupId) {
        // 조회 기록 전송
        fetch("/admin/settings/popup/api/record-action.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                popup_id: popupId,
                action: "clicked"
            })
        }).catch(function(error) {
            console.warn("팝업 액션 기록 실패:", error);
        });
    }
    
    // 팝업 표시 로직 - 개선된 초기화 (깜빡임 방지)
    function initializePopup() {
        // 24시간 안보이기 쿠키 확인
        if (getCookie("hopec_popup_' . $popupId . '_no_show_24h")) {
            console.log("팝업 ' . $popupId . ' - 24시간 안보이기 설정으로 인해 차단됨");
            return;
        }
        
        // 중복 실행 방지
        if (window.hopecPopupInitialized_' . $popupId . ') {
            console.log("팝업 ' . $popupId . ' - 이미 초기화됨");
            return;
        }
        window.hopecPopupInitialized_' . $popupId . ' = true;
        
        // Remodal 라이브러리 확인
        if (typeof $ === "undefined" || typeof $.fn.remodal === "undefined") {
            console.warn("Remodal 라이브러리가 로드되지 않았습니다.");
            return;
        }
        
        // 팝업 요소가 DOM에 있는지 확인
        var popupElement = $("[data-remodal-id=hopec-popup-' . $popupId . ']");
        if (popupElement.length === 0) {
            console.warn("팝업 요소를 찾을 수 없습니다.");
            return;
        }
        
        // 팝업 요소 미리 설정 (깜빡임 방지)
        popupElement.css({
            "opacity": "0",
            "visibility": "hidden",
            "transform": "scale(0.95)"
        });
        
        // Remodal 인스턴스 미리 생성
        var modal = popupElement.remodal({
            hashTracking: false,
            closeOnOutsideClick: true,
            closeOnEscape: true
        });
        
        // 지연 후 팝업 표시 (한 번에 처리)
        setTimeout(function() {
            try {
                // 팝업 내 링크 클릭 이벤트 추가
                popupElement.find("a").on("click", function() {
                    hopecPopupClicked(' . $popupId . ');
                });
                
                // 부드러운 표시를 위한 사전 설정
                popupElement.css({
                    "visibility": "visible",
                    "transition": "opacity 0.3s ease-out, transform 0.3s ease-out"
                });
                
                // 모달 열기
                modal.open();
                
                console.log("팝업 ' . $popupId . ' 성공적으로 표시됨");
            } catch(error) {
                console.error("팝업 표시 중 오류:", error);
                window.hopecPopupInitialized_' . $popupId . ' = false;
            }
        }, ' . $delay . ');
    }
    
    // DOM 로드 완료 후 초기화 시작
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initializePopup);
    } else {
        // DOM이 이미 로드된 경우 즉시 실행
        initializePopup();
    }
    </script>';
    
    return $script;
}
?>