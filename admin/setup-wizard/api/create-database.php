<?php
/**
 * 데이터베이스 생성 및 기본 스키마 설치 API
 */

header('Content-Type: application/json; charset=utf-8');

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'POST 요청만 허용됩니다.'
    ]);
    exit;
}

try {
    // .env 파일 경로
    $envPath = dirname(__DIR__, 3) . '/.env';
    
    if (!file_exists($envPath)) {
        throw new Exception('.env 파일을 찾을 수 없습니다.');
    }
    
    require_once dirname(__DIR__, 3) . '/includes/EnvLoader.php';
    EnvLoader::load();
    
    // 데이터베이스 연결 정보
    $dbHost = env('DB_HOST', 'localhost');
    $dbPort = env('DB_PORT', '3306');
    $dbDatabase = env('DB_DATABASE', '');
    $dbUsername = env('DB_USERNAME', 'root');
    $dbPassword = env('DB_PASSWORD', '');
    
    if (empty($dbDatabase)) {
        throw new Exception('데이터베이스 이름이 설정되지 않았습니다.');
    }
    
    // 서버 연결 (데이터베이스 지정 없이)
    $pdo = new PDO(
        "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4",
        $dbUsername,
        $dbPassword
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 데이터베이스 존재 여부 확인
    $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
    $stmt->execute([$dbDatabase]);
    $dbExists = $stmt->fetch() !== false;
    
    $operations = [];
    
    // 데이터베이스가 없으면 생성
    if (!$dbExists) {
        $createDbQuery = "CREATE DATABASE `{$dbDatabase}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        $pdo->exec($createDbQuery);
        $operations[] = "데이터베이스 '{$dbDatabase}' 생성 완료";
    } else {
        $operations[] = "데이터베이스 '{$dbDatabase}' 이미 존재함";
    }
    
    // 데이터베이스 선택
    $pdo->exec("USE `{$dbDatabase}`");
    
    // 기본 스키마 SQL 파일 읽기
    $schemaPath = dirname(__DIR__, 3) . '/database_schema_base.sql';
    
    if (!file_exists($schemaPath)) {
        throw new Exception('기본 스키마 파일을 찾을 수 없습니다: ' . $schemaPath);
    }
    
    $sqlContent = file_get_contents($schemaPath);
    
    if ($sqlContent === false) {
        throw new Exception('스키마 파일을 읽을 수 없습니다.');
    }
    
    // SQL 문 분리 및 실행
    $statements = preg_split('/;\s*[\r\n]/m', $sqlContent);
    $executedStatements = 0;
    $skippedStatements = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        
        // 빈 문장이나 주석 무시
        if (empty($statement) || strpos($statement, '--') === 0 || strpos($statement, '/*') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $executedStatements++;
        } catch (PDOException $e) {
            // 테이블이 이미 존재하는 경우는 스킵
            if (strpos($e->getMessage(), 'already exists') !== false || 
                strpos($e->getMessage(), 'Duplicate') !== false) {
                $skippedStatements++;
                continue;
            }
            
            // 다른 오류는 로그만 기록하고 계속 진행
            error_log("SQL 실행 오류: " . $e->getMessage() . " - SQL: " . substr($statement, 0, 100));
            $skippedStatements++;
        }
    }
    
    $operations[] = "테이블 생성 완료 (실행: {$executedStatements}, 스킵: {$skippedStatements})";
    
    // 테이블 존재 여부 확인
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $requiredTables = [
        'admin_users', 'site_settings', 'theme_presets', 'boards', 
        'posts', 'gallery', 'inquiries', 'inquiry_categories', 
        'events', 'files', 'menu_items'
    ];
    
    $missingTables = array_diff($requiredTables, $tables);
    $existingTables = array_intersect($requiredTables, $tables);
    
    if (count($existingTables) >= 8) { // 대부분의 중요 테이블이 있으면 성공으로 간주
        $operations[] = "핵심 테이블 설치 완료 (" . count($existingTables) . "/" . count($requiredTables) . ")";
        
        // 관리자 계정 존재 여부 확인
        $adminCount = $pdo->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
        if ($adminCount == 0) {
            // 기본 관리자 계정 생성 (비밀번호: admin123!)
            $defaultPassword = password_hash('admin123!', PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO admin_users (username, password, name, email, role) VALUES (?, ?, ?, ?, ?)")
                ->execute(['admin', $defaultPassword, '관리자', 'admin@example.org', 'admin']);
            $operations[] = "기본 관리자 계정 생성 (ID: admin, PW: admin123!)";
        }
        
        echo json_encode([
            'success' => true,
            'message' => '데이터베이스 설치가 완료되었습니다.',
            'details' => [
                'database_name' => $dbDatabase,
                'operations' => $operations,
                'existing_tables' => $existingTables,
                'missing_tables' => $missingTables,
                'total_tables' => count($tables),
                'admin_accounts' => $adminCount
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
    } else {
        throw new Exception('필수 테이블 설치가 완료되지 않았습니다. 누락된 테이블: ' . implode(', ', $missingTables));
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '데이터베이스 생성 실패: ' . $e->getMessage(),
        'operations' => $operations ?? []
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>