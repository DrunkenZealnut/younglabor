<?php
/**
 * 팝업 관리 시스템 데이터베이스 테이블 설정 스크립트
 * 한 번만 실행하여 필요한 테이블들을 생성합니다.
 */

require_once 'auth.php';
require_once 'db.php';

try {
    echo "<h2>팝업 관리 시스템 테이블 설정</h2>\n";
    
    // 팝업 설정 테이블 생성
    $sql1 = "
    CREATE TABLE IF NOT EXISTS hopec_popup_settings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL COMMENT '팝업 제목',
        content TEXT COMMENT '팝업 내용 (HTML 지원)',
        popup_type ENUM('notice', 'promotion', 'announcement', 'custom') DEFAULT 'notice' COMMENT '팝업 유형',
        display_condition JSON COMMENT '표시 조건 (페이지, 시간, 사용자 그룹)',
        style_settings JSON COMMENT '스타일 설정 (크기, 색상, 애니메이션)',
        is_active BOOLEAN DEFAULT 1 COMMENT '활성화 상태',
        show_frequency ENUM('once', 'daily', 'weekly', 'always') DEFAULT 'once' COMMENT '표시 빈도',
        start_date DATETIME NULL COMMENT '시작 날짜',
        end_date DATETIME NULL COMMENT '종료 날짜',
        priority INT DEFAULT 1 COMMENT '우선순위 (높을수록 우선)',
        view_count INT DEFAULT 0 COMMENT '총 조회수',
        click_count INT DEFAULT 0 COMMENT '총 클릭수',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_active (is_active, start_date, end_date),
        INDEX idx_priority (priority DESC)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='팝업 설정 테이블'
    ";
    
    $pdo->exec($sql1);
    echo "<p>✅ hopec_popup_settings 테이블이 생성되었습니다.</p>\n";
    
    // 팝업 조회 로그 테이블 생성
    $sql2 = "
    CREATE TABLE IF NOT EXISTS hopec_popup_views (
        id INT PRIMARY KEY AUTO_INCREMENT,
        popup_id INT NOT NULL,
        user_ip VARCHAR(45) NOT NULL COMMENT '사용자 IP (IPv6 지원)',
        user_agent TEXT COMMENT '브라우저 정보',
        session_id VARCHAR(255) COMMENT '세션 ID',
        page_url VARCHAR(500) COMMENT '조회된 페이지 URL',
        viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        action ENUM('viewed', 'closed', 'clicked', 'ignored') DEFAULT 'viewed' COMMENT '사용자 액션',
        device_type ENUM('desktop', 'mobile', 'tablet') COMMENT '디바이스 타입',
        FOREIGN KEY (popup_id) REFERENCES hopec_popup_settings(id) ON DELETE CASCADE,
        INDEX idx_popup_user (popup_id, user_ip, viewed_at),
        INDEX idx_analytics (popup_id, viewed_at, action)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='팝업 조회 로그'
    ";
    
    $pdo->exec($sql2);
    echo "<p>✅ hopec_popup_views 테이블이 생성되었습니다.</p>\n";
    
    // 추가 인덱스 생성
    $sql3 = "CREATE INDEX IF NOT EXISTS idx_popup_active_priority ON hopec_popup_settings (is_active, priority DESC, start_date, end_date)";
    $pdo->exec($sql3);
    
    $sql4 = "CREATE INDEX IF NOT EXISTS idx_popup_frequency ON hopec_popup_views (popup_id, user_ip, viewed_at DESC)";
    $pdo->exec($sql4);
    
    echo "<p>✅ 추가 인덱스가 생성되었습니다.</p>\n";
    
    // 샘플 팝업 데이터 삽입 (중복 방지)
    $checkSample = $pdo->query("SELECT COUNT(*) FROM hopec_popup_settings")->fetchColumn();
    
    if ($checkSample == 0) {
        $sampleSql = "
        INSERT INTO hopec_popup_settings (
            title, content, popup_type, display_condition, style_settings, 
            show_frequency, priority, is_active
        ) VALUES (
            '희망씨에 오신 것을 환영합니다!',
            '<div style=\"text-align: center; padding: 20px;\">
                <h3 style=\"color: #84cc16; margin-bottom: 15px;\">사단법인 희망씨</h3>
                <p style=\"line-height: 1.6; margin-bottom: 20px;\">
                    이웃과 친척과 동료와 경쟁하는 삶이 아닌<br>
                    더불어 사는 삶을 위하여 함께해주세요.
                </p>
                <p style=\"color: #666; font-size: 14px;\">
                    청소년 노동인권과 지역사회 연대를 위한 비영리 단체입니다.
                </p>
            </div>',
            'notice',
            '{\"target_pages\": [\"home\"], \"user_type\": [\"visitor\", \"member\"], \"device_type\": [\"desktop\", \"mobile\"], \"time_range\": {\"start\": \"09:00\", \"end\": \"21:00\"}}',
            '{\"width\": \"500\", \"height\": \"auto\", \"bg_color\": \"#ffffff\", \"border_radius\": \"12\", \"animation\": \"fade\", \"overlay_color\": \"rgba(0,0,0,0.5)\"}',
            'once',
            1,
            0
        )";
        
        $pdo->exec($sampleSql);
        echo "<p>✅ 샘플 팝업이 생성되었습니다 (비활성 상태).</p>\n";
    } else {
        echo "<p>ℹ️ 기존 팝업 데이터가 있어 샘플 팝업 생성을 건너뜁니다.</p>\n";
    }
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🎉 설정 완료!</h3>";
    echo "<p>팝업 관리 시스템이 성공적으로 설정되었습니다.</p>";
    echo "<p><strong>다음 단계:</strong></p>";
    echo "<ul>";
    echo "<li>Admin 패널 > 사이트 설정 > 팝업 관리 탭에서 팝업을 관리할 수 있습니다.</li>";
    echo "<li>샘플 팝업이 비활성 상태로 생성되었습니다. 테스트 후 활성화하세요.</li>";
    echo "<li>첫 페이지에서 팝업이 정상적으로 표시되는지 확인해보세요.</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ 오류 발생</h3>";
    echo "<p>테이블 생성 중 오류가 발생했습니다: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>

<style>
body {
    font-family: 'Noto Sans KR', Arial, sans-serif;
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    line-height: 1.6;
}

h2 {
    color: #333;
    border-bottom: 2px solid #84cc16;
    padding-bottom: 10px;
}

p {
    margin: 10px 0;
}

ul {
    margin: 10px 0 10px 20px;
}
</style>