<?php
/**
 * SchemaParser 사용 예제
 * 
 * SQL 스키마에서 자동으로 BoardTableConfig를 생성하는 방법을 보여줍니다.
 */

require_once __DIR__ . '/../src/Config/SchemaParser.php';
require_once __DIR__ . '/../src/Config/BoardTableConfig.php';

use BoardTemplates\Config\SchemaParser;

echo "=== SQL 스키마 기반 자동 설정 생성 예제 ===\n\n";

// 예제 SQL 스키마
$exampleSql = "
CREATE TABLE my_board_posts (
    post_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    author_name VARCHAR(100),
    view_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE my_board_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE my_board_attachments (
    attachment_id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    file_size INT,
    mime_type VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE my_board_comments (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    parent_id INT NULL,
    content TEXT NOT NULL,
    author_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";

try {
    // 1. SQL 문자열에서 스키마 파싱
    echo "1. SQL 스키마 파싱 중...\n";
    $parser = new SchemaParser();
    $tableConfig = $parser->parseFromSqlString($exampleSql);
    
    echo "   ✓ 감지된 테이블 접두사: '{$tableConfig->getTablePrefix()}'\n";
    echo "   ✓ 발견된 테이블들:\n";
    
    foreach ($tableConfig->getAllTableNames() as $key => $tableName) {
        echo "     - {$key}: {$tableName}\n";
    }
    
    // 2. 테이블명 및 컬럼명 테스트
    echo "\n2. 테이블/컬럼 매핑 테스트:\n";
    
    $postsTable = $tableConfig->getTableName('posts');
    echo "   게시글 테이블: {$postsTable}\n";
    
    $titleColumn = $tableConfig->getColumnName('posts', 'title');
    echo "   제목 컬럼: {$titleColumn}\n";
    
    $authorColumn = $tableConfig->getColumnName('posts', 'author');
    echo "   작성자 컬럼: {$authorColumn}\n";
    
    // 3. SELECT 쿼리 생성 예제
    echo "\n3. 자동 생성된 SQL 쿼리 예제:\n";
    
    $selectColumns = $tableConfig->getSelectColumns('posts', ['id', 'title', 'author', 'created_at']);
    $postsTable = $tableConfig->getTableName('posts');
    $categoriesTable = $tableConfig->getTableName('categories');
    
    $sql = "
SELECT {$selectColumns}
FROM {$postsTable} p
JOIN {$categoriesTable} c ON p.category_id = c.category_id
WHERE p.is_active = 1
ORDER BY p.created_at DESC
LIMIT 10";
    
    echo "   생성된 쿼리:\n";
    echo "   " . trim($sql) . "\n";
    
    // 4. 설정 파일로 내보내기
    echo "\n4. 설정 파일 생성:\n";
    $configPath = __DIR__ . '/generated_table_config.php';
    $parser->exportToConfigFile($tableConfig, $configPath);
    echo "   ✓ 설정 파일 생성: {$configPath}\n";
    
    // 5. JSON 형태로 출력
    echo "\n5. JSON 형태 설정:\n";
    echo $parser->toJson($tableConfig) . "\n";
    
    echo "\n=== 파싱 완료! ===\n";
    
} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "\n";
    echo "스택 트레이스:\n" . $e->getTraceAsString() . "\n";
}

// 데이터베이스 연결 예제 (주석 처리)
/*
echo "\n=== 데이터베이스 직접 연결 예제 ===\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=your_database', 'username', 'password');
    $parser = new SchemaParser();
    $tableConfig = $parser->parseFromDatabase($pdo);
    
    echo "데이터베이스에서 자동 생성된 설정:\n";
    print_r($tableConfig->getAllTableNames());
    
} catch (Exception $e) {
    echo "데이터베이스 연결 실패: " . $e->getMessage() . "\n";
}
*/
?>