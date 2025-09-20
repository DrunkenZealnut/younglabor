<?php
/**
 * hopec_posts 테이블 구조 확인 및 분석 스크립트
 * 관리자 게시글 작성 시스템 개선을 위한 데이터베이스 구조 분석
 */

require_once '../includes/db.php';

header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html>
<html lang='ko'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Structure Check</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 20px; background: #f8f9fa; }
        .container { max-width: 1200px; margin: 0 auto; }
        .section { background: white; margin: 20px 0; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1, h2 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f4f4f4; font-weight: bold; }
        .success { color: #28a745; }
        .warning { color: #ffc107; }
        .danger { color: #dc3545; }
        .info { color: #17a2b8; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 4px; font-family: monospace; white-space: pre-wrap; }
        .status-ok { background: #d4edda; color: #155724; }
        .status-missing { background: #f8d7da; color: #721c24; }
        .status-exists { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🔍 hopec_posts 테이블 구조 분석</h1>";

try {
    // 1. hopec_posts 테이블 존재 여부 확인
    echo "<div class='section'>";
    echo "<h2>1. 테이블 존재 여부 확인</h2>";
    
    $check_table_sql = "SHOW TABLES LIKE 'hopec_posts'";
    $check_stmt = $pdo->prepare($check_table_sql);
    $check_stmt->execute();
    $table_exists = $check_stmt->rowCount() > 0;
    
    if ($table_exists) {
        echo "<p class='success'>✅ hopec_posts 테이블이 존재합니다.</p>";
    } else {
        echo "<p class='danger'>❌ hopec_posts 테이블이 존재하지 않습니다!</p>";
        echo "<div class='code'>-- hopec_posts 테이블 생성이 필요합니다.</div>";
        echo "</div></body></html>";
        exit;
    }
    echo "</div>";

    // 2. 현재 테이블 구조 확인
    echo "<div class='section'>";
    echo "<h2>2. 현재 테이블 구조</h2>";
    
    $desc_sql = "DESCRIBE hopec_posts";
    $desc_stmt = $pdo->prepare($desc_sql);
    $desc_stmt->execute();
    $current_columns = $desc_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>컬럼명</th><th>데이터 타입</th><th>NULL 허용</th><th>키</th><th>기본값</th><th>Extra</th></tr>";
    
    $existing_columns = [];
    foreach ($current_columns as $column) {
        $existing_columns[] = $column['Field'];
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";

    // 3. 필요한 컬럼들 정의 (참조 텍스트 기준)
    $required_columns = [
        // 기본 필수 컬럼들
        'wr_id' => ['type' => 'INT AUTO_INCREMENT PRIMARY KEY', 'required' => true, 'description' => '게시글 고유 ID'],
        'board_type' => ['type' => 'VARCHAR(50)', 'required' => true, 'description' => '게시판 타입'],
        'wr_subject' => ['type' => 'VARCHAR(255)', 'required' => true, 'description' => '제목 (255자 제한)'],
        'wr_content' => ['type' => 'TEXT', 'required' => true, 'description' => '내용 (65536자 제한)'],
        'wr_name' => ['type' => 'VARCHAR(100)', 'required' => true, 'description' => '작성자명'],
        'wr_datetime' => ['type' => 'DATETIME', 'required' => true, 'description' => '작성일시'],
        'wr_ip' => ['type' => 'VARCHAR(45)', 'required' => false, 'description' => '작성자 IP'],
        
        // 작성자 정보 컬럼들
        'wr_email' => ['type' => 'VARCHAR(255)', 'required' => false, 'description' => '이메일'],
        'wr_homepage' => ['type' => 'VARCHAR(255)', 'required' => false, 'description' => '홈페이지 URL'],
        'wr_password' => ['type' => 'VARCHAR(255)', 'required' => false, 'description' => '비밀번호'],
        'mb_id' => ['type' => 'VARCHAR(20)', 'required' => false, 'description' => '회원 ID'],
        
        // 분류/카테고리
        'ca_name' => ['type' => 'VARCHAR(255)', 'required' => false, 'description' => '분류/카테고리'],
        
        // 링크 컬럼들
        'wr_link1' => ['type' => 'VARCHAR(1000)', 'required' => false, 'description' => '링크 1'],
        'wr_link2' => ['type' => 'VARCHAR(1000)', 'required' => false, 'description' => '링크 2'],
        'wr_link1_hit' => ['type' => 'INT DEFAULT 0', 'required' => false, 'description' => '링크1 클릭수'],
        'wr_link2_hit' => ['type' => 'INT DEFAULT 0', 'required' => false, 'description' => '링크2 클릭수'],
        
        // 옵션 컬럼들
        'wr_option' => ['type' => 'SET("html1","html2","secret","mail","notice")', 'required' => false, 'description' => '게시글 옵션'],
        
        // 시스템 자동 생성 컬럼들
        'wr_num' => ['type' => 'INT DEFAULT 0', 'required' => false, 'description' => '게시글 번호'],
        'wr_reply' => ['type' => 'VARCHAR(10) DEFAULT ""', 'required' => false, 'description' => '답글 구조'],
        'wr_parent' => ['type' => 'INT DEFAULT 0', 'required' => false, 'description' => '부모 게시글 ID'],
        'wr_is_comment' => ['type' => 'TINYINT DEFAULT 0', 'required' => false, 'description' => '댓글 여부'],
        'wr_comment' => ['type' => 'INT DEFAULT 0', 'required' => false, 'description' => '댓글 수'],
        'wr_comment_reply' => ['type' => 'VARCHAR(5) DEFAULT ""', 'required' => false, 'description' => '댓글 답글'],
        'wr_last' => ['type' => 'DATETIME', 'required' => false, 'description' => '최종 수정일시'],
        'wr_hit' => ['type' => 'INT DEFAULT 0', 'required' => false, 'description' => '조회수'],
        'wr_good' => ['type' => 'INT DEFAULT 0', 'required' => false, 'description' => '추천 수'],
        'wr_nogood' => ['type' => 'INT DEFAULT 0', 'required' => false, 'description' => '비추천 수'],
        'wr_file' => ['type' => 'INT DEFAULT 0', 'required' => false, 'description' => '첨부파일 수'],
        
        // SNS 관련
        'wr_facebook_user' => ['type' => 'VARCHAR(255)', 'required' => false, 'description' => 'Facebook 사용자'],
        'wr_twitter_user' => ['type' => 'VARCHAR(255)', 'required' => false, 'description' => 'Twitter 사용자'],
        
        // 확장 필드들 (wr_1 ~ wr_10)
        'wr_1' => ['type' => 'VARCHAR(255)', 'required' => false, 'description' => '확장 필드 1'],
        'wr_2' => ['type' => 'VARCHAR(255)', 'required' => false, 'description' => '확장 필드 2'],
        'wr_3' => ['type' => 'VARCHAR(255)', 'required' => false, 'description' => '확장 필드 3'],
        'wr_4' => ['type' => 'VARCHAR(255)', 'required' => false, 'description' => '확장 필드 4'],
        'wr_5' => ['type' => 'VARCHAR(255)', 'required' => false, 'description' => '확장 필드 5'],
        'wr_6' => ['type' => 'VARCHAR(255)', 'required' => false, 'description' => '확장 필드 6'],
        'wr_7' => ['type' => 'VARCHAR(255)', 'required' => false, 'description' => '확장 필드 7'],
        'wr_8' => ['type' => 'VARCHAR(255)', 'required' => false, 'description' => '확장 필드 8'],
        'wr_9' => ['type' => 'VARCHAR(255)', 'required' => false, 'description' => '확장 필드 9'],
        'wr_10' => ['type' => 'VARCHAR(255)', 'required' => false, 'description' => '확장 필드 10']
    ];

    // 4. 컬럼 비교 분석
    echo "<div class='section'>";
    echo "<h2>3. 필요 컬럼 비교 분석</h2>";
    
    $missing_columns = [];
    $existing_but_check_needed = [];
    
    echo "<table>";
    echo "<tr><th>컬럼명</th><th>필요 여부</th><th>상태</th><th>데이터 타입</th><th>설명</th></tr>";
    
    foreach ($required_columns as $column_name => $column_info) {
        $exists = in_array($column_name, $existing_columns);
        $status_class = $exists ? 'status-exists' : 'status-missing';
        $status_text = $exists ? '✅ 존재함' : '❌ 누락됨';
        
        if (!$exists && $column_info['required']) {
            $missing_columns[] = $column_name;
        } elseif (!$exists && !$column_info['required']) {
            $existing_but_check_needed[] = $column_name;
        }
        
        echo "<tr class='{$status_class}'>";
        echo "<td><strong>" . htmlspecialchars($column_name) . "</strong></td>";
        echo "<td>" . ($column_info['required'] ? '<span class="danger">필수</span>' : '<span class="info">선택</span>') . "</td>";
        echo "<td>{$status_text}</td>";
        echo "<td>" . htmlspecialchars($column_info['type']) . "</td>";
        echo "<td>" . htmlspecialchars($column_info['description']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";

    // 5. ALTER TABLE 스크립트 생성
    echo "<div class='section'>";
    echo "<h2>4. 테이블 구조 개선 SQL 스크립트</h2>";
    
    if (empty($missing_columns) && empty($existing_but_check_needed)) {
        echo "<p class='success'>🎉 모든 필요한 컬럼이 존재합니다!</p>";
    } else {
        echo "<h3>📝 실행해야 할 ALTER TABLE 스크립트:</h3>";
        
        $alter_scripts = [];
        
        // 누락된 필수 컬럼들
        if (!empty($missing_columns)) {
            echo "<h4 class='danger'>⚠️ 누락된 필수 컬럼들:</h4>";
            foreach ($missing_columns as $column) {
                $column_def = $required_columns[$column];
                $alter_scripts[] = "ALTER TABLE hopec_posts ADD COLUMN {$column} {$column_def['type']};";
            }
        }
        
        // 누락된 선택적 컬럼들 (개선을 위해 권장)
        if (!empty($existing_but_check_needed)) {
            echo "<h4 class='warning'>💡 개선을 위해 추가 권장 컬럼들:</h4>";
            foreach ($existing_but_check_needed as $column) {
                $column_def = $required_columns[$column];
                $alter_scripts[] = "ALTER TABLE hopec_posts ADD COLUMN {$column} {$column_def['type']};";
            }
        }
        
        if (!empty($alter_scripts)) {
            echo "<div class='code'>";
            echo "-- hopec_posts 테이블 구조 개선 스크립트\n";
            echo "-- 실행 전에 반드시 백업을 수행하세요!\n\n";
            
            foreach ($alter_scripts as $script) {
                echo $script . "\n";
            }
            
            echo "\n-- 인덱스 추가 (성능 최적화)\n";
            echo "ALTER TABLE hopec_posts ADD INDEX idx_board_type (board_type);\n";
            echo "ALTER TABLE hopec_posts ADD INDEX idx_wr_datetime (wr_datetime);\n";
            echo "ALTER TABLE hopec_posts ADD INDEX idx_wr_is_comment (wr_is_comment);\n";
            echo "ALTER TABLE hopec_posts ADD INDEX idx_ca_name (ca_name);\n";
            echo "ALTER TABLE hopec_posts ADD INDEX idx_mb_id (mb_id);\n";
            echo "</div>";
        }
    }
    echo "</div>";

    // 6. 현재 데이터 샘플 확인
    echo "<div class='section'>";
    echo "<h2>5. 현재 데이터 샘플</h2>";
    
    $sample_sql = "SELECT * FROM hopec_posts ORDER BY wr_datetime DESC LIMIT 5";
    $sample_stmt = $pdo->prepare($sample_sql);
    $sample_stmt->execute();
    $sample_data = $sample_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($sample_data)) {
        echo "<p>최근 게시글 5개 샘플:</p>";
        echo "<table>";
        echo "<tr>";
        foreach (array_keys($sample_data[0]) as $column) {
            echo "<th>" . htmlspecialchars($column) . "</th>";
        }
        echo "</tr>";
        
        foreach ($sample_data as $row) {
            echo "<tr>";
            foreach ($row as $value) {
                $display_value = $value;
                if (strlen($display_value) > 50) {
                    $display_value = substr($display_value, 0, 50) . '...';
                }
                echo "<td>" . htmlspecialchars($display_value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='info'>테이블에 데이터가 없습니다.</p>";
    }
    echo "</div>";

} catch (PDOException $e) {
    echo "<div class='section'>";
    echo "<h2 class='danger'>❌ 데이터베이스 오류</h2>";
    echo "<p>오류 메시지: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<div class='section'>";
echo "<h2>📋 다음 단계</h2>";
echo "<ol>";
echo "<li><strong>백업 수행</strong>: 변경 전에 반드시 데이터베이스 백업</li>";
echo "<li><strong>스크립트 실행</strong>: 위의 ALTER TABLE 스크립트들을 순서대로 실행</li>";
echo "<li><strong>데이터 검증</strong>: 변경 후 기존 데이터 무결성 확인</li>";
echo "<li><strong>애플리케이션 업데이트</strong>: 새로운 컬럼들을 활용하도록 코드 수정</li>";
echo "</ol>";
echo "</div>";

echo "</div></body></html>";
?>