<?php
/**
 * 팝업 디버그 페이지
 * 팝업 시스템이 제대로 작동하는지 확인
 */

require_once __DIR__ . '/bootstrap/app.php';
require_once __DIR__ . '/admin/services/PopupManager.php';

// 현재 페이지와 사용자 정보
$currentPage = 'home';
$userIP = $_SERVER['REMOTE_ADDR'] ?? '';
$sessionId = session_id();

echo "<h1>팝업 시스템 디버그</h1>";

try {
    // 데이터베이스 연결 확인
    echo "<h2>1. 데이터베이스 연결 확인</h2>";
    echo "PDO 연결: " . (isset($pdo) ? "✅ 성공" : "❌ 실패") . "<br>";
    
    if (!isset($pdo)) {
        $dbConfigPath = __DIR__ . '/data/dbconfig.php';
        if (file_exists($dbConfigPath)) {
            include $dbConfigPath;
            
            // 그누보드 설정에서 변수 추출
            $db_host = defined('G5_MYSQL_HOST') ? G5_MYSQL_HOST : 'localhost';
            $db_user = defined('G5_MYSQL_USER') ? G5_MYSQL_USER : 'root';
            $db_pass = defined('G5_MYSQL_PASSWORD') ? G5_MYSQL_PASSWORD : '';
            $db_name = defined('G5_MYSQL_DB') ? G5_MYSQL_DB : 'hopec';
            
            echo "DB 연결 정보: $db_host, $db_user, $db_name<br>";
            
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "새로운 PDO 연결: ✅ 성공<br>";
        } else {
            echo "DB 설정 파일 없음: ❌<br>";
            exit;
        }
    }
    
    // 팝업 테이블 확인
    echo "<h2>2. 팝업 테이블 확인</h2>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'hopec_popup_settings'");
    $tableExists = $stmt->rowCount() > 0;
    echo "hopec_popup_settings 테이블: " . ($tableExists ? "✅ 존재" : "❌ 없음") . "<br>";
    
    if (!$tableExists) {
        echo "팝업 테이블이 존재하지 않습니다. 테이블을 생성해야 합니다.<br>";
        exit;
    }
    
    // 전체 팝업 목록 조회
    echo "<h2>3. 전체 팝업 목록</h2>";
    $stmt = $pdo->query("SELECT id, title, is_active, start_date, end_date, created_at FROM hopec_popup_settings ORDER BY created_at DESC");
    $allPopups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($allPopups)) {
        echo "❌ 등록된 팝업이 없습니다.<br>";
    } else {
        echo "총 " . count($allPopups) . "개의 팝업이 등록되어 있습니다:<br>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>제목</th><th>활성화</th><th>시작일</th><th>종료일</th><th>생성일</th></tr>";
        foreach ($allPopups as $popup) {
            echo "<tr>";
            echo "<td>" . $popup['id'] . "</td>";
            echo "<td>" . htmlspecialchars($popup['title']) . "</td>";
            echo "<td>" . ($popup['is_active'] ? "✅ 활성" : "❌ 비활성") . "</td>";
            echo "<td>" . ($popup['start_date'] ?: '없음') . "</td>";
            echo "<td>" . ($popup['end_date'] ?: '없음') . "</td>";
            echo "<td>" . $popup['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 팝업 매니저로 활성 팝업 조회
    echo "<h2>4. 활성 팝업 조회 (PopupManager 사용)</h2>";
    $popupManager = new PopupManager($pdo);
    $activePopups = $popupManager->getActivePopups($currentPage, $userIP, $sessionId);
    
    if (empty($activePopups)) {
        echo "❌ 현재 표시할 활성 팝업이 없습니다.<br>";
        
        // 활성화된 팝업이 있는지 직접 확인
        echo "<h3>4-1. 활성화된 팝업 직접 확인</h3>";
        $stmt = $pdo->query("
            SELECT id, title, is_active, start_date, end_date, display_condition 
            FROM hopec_popup_settings 
            WHERE is_active = 1 
            AND (start_date IS NULL OR start_date <= NOW())
            AND (end_date IS NULL OR end_date >= NOW())
        ");
        $directActivePopups = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($directActivePopups)) {
            echo "❌ 조건에 맞는 활성 팝업이 없습니다.<br>";
        } else {
            echo "✅ " . count($directActivePopups) . "개의 조건에 맞는 팝업이 있습니다:<br>";
            foreach ($directActivePopups as $popup) {
                echo "- ID: " . $popup['id'] . ", 제목: " . htmlspecialchars($popup['title']) . "<br>";
                echo "  표시조건: " . $popup['display_condition'] . "<br>";
            }
        }
        
    } else {
        echo "✅ " . count($activePopups) . "개의 활성 팝업이 있습니다:<br>";
        foreach ($activePopups as $popup) {
            echo "- ID: " . $popup['id'] . ", 제목: " . htmlspecialchars($popup['title']) . "<br>";
        }
    }
    
    // 쿠키 확인
    echo "<h2>5. 팝업 쿠키 확인</h2>";
    $popupCookies = [];
    foreach ($_COOKIE as $name => $value) {
        if (strpos($name, 'hopec_popup_') === 0) {
            $popupCookies[$name] = $value;
        }
    }
    
    if (empty($popupCookies)) {
        echo "팝업 관련 쿠키가 없습니다.<br>";
    } else {
        echo "팝업 관련 쿠키:<br>";
        foreach ($popupCookies as $name => $value) {
            echo "- $name = $value<br>";
        }
    }
    
    // JavaScript 라이브러리 확인
    echo "<h2>6. JavaScript 라이브러리 확인</h2>";
    echo "jQuery가 로드되어 있는지 확인: <span id='jquery-status'>확인 중...</span><br>";
    echo "Remodal이 로드되어 있는지 확인: <span id='remodal-status'>확인 중...</span><br>";
    
    echo "<script>
    document.getElementById('jquery-status').textContent = (typeof $ !== 'undefined') ? '✅ 로드됨' : '❌ 없음';
    document.getElementById('remodal-status').textContent = (typeof $ !== 'undefined' && typeof $.fn.remodal !== 'undefined') ? '✅ 로드됨' : '❌ 없음';
    </script>";
    
    // 수동 팝업 테스트
    if (!empty($allPopups)) {
        $firstPopup = $allPopups[0];
        echo "<h2>7. 수동 팝업 테스트</h2>";
        echo "<button onclick='testPopup()'>첫 번째 팝업 테스트</button><br>";
        echo "<div id='popup-test-result'></div>";
        
        echo "<script>
        function testPopup() {
            document.getElementById('popup-test-result').innerHTML = '팝업 테스트 중...';
            
            // 팝업 HTML을 동적으로 생성
            var popupHtml = `
            <div class='remodal' data-remodal-id='test-popup'>
                <div style='padding: 20px;'>
                    <h3>" . htmlspecialchars($firstPopup['title']) . "</h3>
                    <p>테스트 팝업입니다.</p>
                    <button data-remodal-action='close'>닫기</button>
                </div>
            </div>`;
            
            document.body.insertAdjacentHTML('beforeend', popupHtml);
            
            if (typeof $ !== 'undefined' && typeof $.fn.remodal !== 'undefined') {
                var modal = $('[data-remodal-id=test-popup]').remodal();
                modal.open();
                document.getElementById('popup-test-result').innerHTML = '✅ 팝업이 표시되었습니다.';
            } else {
                document.getElementById('popup-test-result').innerHTML = '❌ jQuery 또는 Remodal이 로드되지 않았습니다.';
            }
        }
        </script>";
    }
    
} catch (Exception $e) {
    echo "<h2>오류 발생</h2>";
    echo "오류: " . $e->getMessage() . "<br>";
    echo "파일: " . $e->getFile() . "<br>";
    echo "라인: " . $e->getLine() . "<br>";
}

// jQuery와 Remodal 로드
echo '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';
echo '<script src="/js/remodal/remodal.js"></script>';
echo '<link rel="stylesheet" href="/js/remodal/remodal.css">';
echo '<link rel="stylesheet" href="/js/remodal/remodal-default-theme.css">';
?>