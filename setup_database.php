<?php
/**
 * Database Setup Script for hopec_site_settings
 * Natural-Green 테마와 Admin 8색상 시스템 통합을 위한 데이터베이스 설정
 */

// Bootstrap 포함
require_once dirname(__DIR__) . '/hopec/admin/bootstrap.php';

// 데이터베이스 연결 확인
if (!isset($pdo)) {
    die("Error: Database connection not available. Please check bootstrap.php");
}

echo "<h1>hopec_site_settings 테이블 설정</h1>\n";

try {
    // SQL 파일 읽기
    $sqlFile = __DIR__ . '/setup_hopec_site_settings.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL 파일을 찾을 수 없습니다: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    if ($sql === false) {
        throw new Exception("SQL 파일을 읽을 수 없습니다");
    }
    
    // SQL 명령들을 분리하여 실행
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );
    
    echo "<h2>데이터베이스 설정 실행 중...</h2>\n";
    
    // 트랜잭션 시작
    $pdo->beginTransaction();
    
    foreach ($statements as $statement) {
        // 주석 제거
        $statement = preg_replace('/--.*$/m', '', $statement);
        $statement = trim($statement);
        
        if (empty($statement)) continue;
        
        echo "<p>실행 중: " . substr($statement, 0, 50) . "...</p>\n";
        $pdo->exec($statement);
    }
    
    // 트랜잭션 커밋
    $pdo->commit();
    
    echo "<h2 style='color: green;'>✅ 데이터베이스 설정 완료!</h2>\n";
    
    // 설정된 색상 확인
    echo "<h3>설정된 테마 색상:</h3>\n";
    $stmt = $pdo->prepare("
        SELECT setting_key, setting_value, setting_description 
        FROM hopec_site_settings 
        WHERE setting_group = 'theme' 
        AND setting_key LIKE '%_color' 
        ORDER BY 
            CASE setting_key
                WHEN 'primary_color' THEN 1
                WHEN 'secondary_color' THEN 2
                WHEN 'success_color' THEN 3
                WHEN 'info_color' THEN 4
                WHEN 'warning_color' THEN 5
                WHEN 'danger_color' THEN 6
                WHEN 'light_color' THEN 7
                WHEN 'dark_color' THEN 8
                ELSE 9
            END
    ");
    $stmt->execute();
    $colors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; margin: 20px 0;'>\n";
    echo "<tr style='background-color: #f5f5f5;'><th>색상명</th><th>색상값</th><th>미리보기</th><th>설명</th></tr>\n";
    
    foreach ($colors as $color) {
        $colorName = str_replace('_color', '', $color['setting_key']);
        $colorValue = $color['setting_value'];
        $description = $color['setting_description'];
        
        echo "<tr>\n";
        echo "<td><strong>" . ucfirst($colorName) . "</strong></td>\n";
        echo "<td><code>$colorValue</code></td>\n";
        echo "<td><div style='width: 40px; height: 40px; background-color: $colorValue; border: 1px solid #ccc; border-radius: 4px;'></div></td>\n";
        echo "<td>$description</td>\n";
        echo "</tr>\n";
    }
    
    echo "</table>\n";
    
    // 통계 정보
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM hopec_site_settings");
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "<p><strong>총 설정 항목:</strong> $total 개</p>\n";
    
    echo "<h3>다음 단계:</h3>\n";
    echo "<ul>\n";
    echo "<li>✅ 1단계: hopec_site_settings 테이블 생성 완료</li>\n";
    echo "<li>✅ 1단계: Natural-Green 테마 색상으로 초기화 완료</li>\n";
    echo "<li>⏳ 2단계: ThemeService CSS 템플릿에 Natural-Green 변수 추가 필요</li>\n";
    echo "<li>⏳ 3단계: Admin UI 색상 라벨 개선 필요</li>\n";
    echo "<li>⏳ 4단계: 프론트엔드 CSS 로딩 순서 최적화 필요</li>\n";
    echo "<li>⏳ 5단계: 테마 변경 테스트 및 검증 필요</li>\n";
    echo "</ul>\n";
    
} catch (Exception $e) {
    // 트랜잭션 롤백
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    echo "<h2 style='color: red;'>❌ 오류 발생:</h2>\n";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p><strong>위치:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>\n";
}
?>