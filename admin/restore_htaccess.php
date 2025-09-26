<?php
/**
 * .htaccess 복구 도구
 * PHP 라우터에서 원본 .htaccess 파일로 되돌리는 도구
 * 
 * 사용법:
 * 1. 브라우저에서 이 파일에 접근
 * 2. 또는 명령행에서: php restore_htaccess.php
 */

// 보안: 관리자만 접근 가능하도록 설정
session_start();
$isAdmin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$isCommandLine = php_sapi_name() === 'cli';

if (!$isAdmin && !$isCommandLine) {
    // 간단한 패스워드 인증 (실제 운영에서는 더 강력한 인증 사용)
    if (!isset($_POST['password']) || $_POST['password'] !== 'hopec_restore') {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>.htaccess 복구 도구</title>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
                .form-group { margin: 10px 0; }
                input[type="password"] { padding: 8px; width: 200px; }
                button { padding: 10px 20px; background: #007cba; color: white; border: none; cursor: pointer; }
                button:hover { background: #005a87; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 4px; }
            </style>
        </head>
        <body>
            <h1>.htaccess 복구 도구</h1>
            
            <div class="warning">
                <strong>주의:</strong> 이 도구는 현재 PHP 라우터 기반 .htaccess를 원본으로 되돌립니다.
                PHP 라우터 기능이 비활성화되므로 신중히 사용하세요.
            </div>
            
            <form method="post">
                <div class="form-group">
                    <label for="password">관리자 비밀번호:</label><br>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit">복구 실행</button>
            </form>
            
            <h2>현재 상태</h2>
            <?php
            $htaccessPath = dirname(__DIR__) . '/.htaccess';
            $backupPath = dirname(__DIR__) . '/.htaccess.backup';
            
            echo '<ul>';
            echo '<li>현재 .htaccess 파일: ' . (file_exists($htaccessPath) ? '존재' : '없음') . '</li>';
            echo '<li>백업 .htaccess.backup 파일: ' . (file_exists($backupPath) ? '존재' : '없음') . '</li>';
            
            if (file_exists($htaccessPath)) {
                $content = file_get_contents($htaccessPath);
                if (strpos($content, 'PHP 라우터와 함께 사용') !== false) {
                    echo '<li>현재 모드: <strong>PHP 라우터 모드</strong></li>';
                } else {
                    echo '<li>현재 모드: <strong>원본 .htaccess 모드</strong></li>';
                }
            }
            echo '</ul>';
            ?>
        </body>
        </html>
        <?php
        exit;
    }
}

// 복구 실행
$htaccessPath = dirname(__DIR__) . '/.htaccess';
$backupPath = dirname(__DIR__) . '/.htaccess.backup';
$routerPath = dirname(__DIR__) . '/includes/router.php';
$routesPath = dirname(__DIR__) . '/includes/routes.php';

$results = [];
$success = true;

try {
    // 1. 백업 파일 존재 확인
    if (!file_exists($backupPath)) {
        throw new Exception('백업 파일(.htaccess.backup)이 존재하지 않습니다.');
    }
    
    $results[] = '✓ 백업 파일 발견';
    
    // 2. 현재 .htaccess 파일 백업 (복원 전 상태 저장)
    if (file_exists($htaccessPath)) {
        $restoreBackupPath = $htaccessPath . '.router_backup.' . date('Y-m-d_H-i-s');
        if (copy($htaccessPath, $restoreBackupPath)) {
            $results[] = "✓ 현재 .htaccess 파일을 {$restoreBackupPath}에 백업";
        }
    }
    
    // 3. 원본 .htaccess 복원
    if (copy($backupPath, $htaccessPath)) {
        $results[] = '✓ 원본 .htaccess 파일 복원 완료';
    } else {
        throw new Exception('.htaccess 파일 복원에 실패했습니다.');
    }
    
    // 4. PHP 라우터 파일들 비활성화 (삭제하지 않고 백업)
    if (file_exists($routerPath)) {
        $routerBackup = $routerPath . '.disabled.' . date('Y-m-d_H-i-s');
        if (rename($routerPath, $routerBackup)) {
            $results[] = "✓ router.php를 {$routerBackup}으로 비활성화";
        }
    }
    
    if (file_exists($routesPath)) {
        $routesBackup = $routesPath . '.disabled.' . date('Y-m-d_H-i-s');
        if (rename($routesPath, $routesBackup)) {
            $results[] = "✓ routes.php를 {$routesBackup}으로 비활성화";
        }
    }
    
    // 5. index.php 복원 (라우터 코드 제거)
    $indexPath = dirname(__DIR__) . '/index.php';
    if (file_exists($indexPath)) {
        $indexContent = file_get_contents($indexPath);
        
        // 라우터 관련 코드 제거
        $routerCode = '// PHP 라우터 시스템 로드
require_once __DIR__ . \'/includes/router.php\';

// 라우트 정의 로드
$router = require_once __DIR__ . \'/includes/routes.php\';

// 라우터로 요청 처리 시도
if ($router->dispatch()) {
    // 라우터에서 처리됨, 종료
    exit;
}

// 라우터에서 처리되지 않은 경우 기존 로직 사용 (fallback)';

        $originalCode = '// Fix URLs containing ${PROJECT_SLUG}
$request_uri = $_SERVER[\'REQUEST_URI\'] ?? \'\';
if (strpos($request_uri, \'${PROJECT_SLUG}\') !== false || 
    strpos($request_uri, \'%7BPROJECT_SLUG%7D\') !== false ||
    strpos($request_uri, \'$%7BPROJECT_SLUG%7D\') !== false) {
    
    $fixedUri = str_replace(
        [\'${PROJECT_SLUG}\', \'%7BPROJECT_SLUG%7D\', \'$%7BPROJECT_SLUG%7D\'],
        \'hopec\',
        $request_uri
    );
    
    header(\'Location: \' . $fixedUri);
    exit;
}

// 간단한 라우팅 처리 (board/list/{id} URL)
$parsed_url = parse_url($request_uri);
$path = $parsed_url[\'path\'] ?? \'\';

// /hopec/ 접두사 제거 (로컬 환경)
if (strpos($path, \'/hopec/\') === 0) {
    $path = substr($path, 6); // "/hopec/" 제거
}

// board/list/{id} 패턴 매칭
if (preg_match(\'/^board\/list\/(\d+)\/?$/\', $path, $matches)) {
    $board_id = (int)$matches[1];
    $_GET[\'id\'] = $board_id; // board.php에서 사용할 수 있도록 설정
    
    // board.php로 라우팅
    if (file_exists(__DIR__ . \'/board.php\')) {
        include __DIR__ . \'/board.php\';
        exit;
    }
}';
        
        $restoredContent = str_replace($routerCode, $originalCode, $indexContent);
        
        if (file_put_contents($indexPath, $restoredContent)) {
            $results[] = '✓ index.php에서 라우터 코드 제거 완료';
        }
    }
    
    $results[] = '🎉 복구 완료! 원본 .htaccess 모드로 전환되었습니다.';
    
} catch (Exception $e) {
    $success = false;
    $results[] = '❌ 오류: ' . $e->getMessage();
}

// 결과 출력
if ($isCommandLine) {
    // 명령행 출력
    foreach ($results as $result) {
        echo $result . "\n";
    }
} else {
    // 웹 브라우저 출력
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>.htaccess 복구 결과</title>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
            .success { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 20px 0; border-radius: 4px; color: #155724; }
            .error { background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 20px 0; border-radius: 4px; color: #721c24; }
            .result { margin: 10px 0; padding: 5px 0; }
            a { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #007cba; color: white; text-decoration: none; border-radius: 4px; }
            a:hover { background: #005a87; }
        </style>
    </head>
    <body>
        <h1>.htaccess 복구 결과</h1>
        
        <div class="<?php echo $success ? 'success' : 'error'; ?>">
            <?php if ($success): ?>
                <h3>✅ 복구 성공!</h3>
                <p>PHP 라우터에서 원본 .htaccess 모드로 성공적으로 전환되었습니다.</p>
            <?php else: ?>
                <h3>❌ 복구 실패</h3>
                <p>복구 과정에서 오류가 발생했습니다.</p>
            <?php endif; ?>
        </div>
        
        <h3>실행 결과:</h3>
        <?php foreach ($results as $result): ?>
            <div class="result"><?php echo htmlspecialchars($result); ?></div>
        <?php endforeach; ?>
        
        <h3>다음 단계:</h3>
        <ul>
            <li>웹사이트가 정상 작동하는지 확인하세요</li>
            <li>필요시 다시 PHP 라우터 모드로 전환할 수 있습니다</li>
            <li>비활성화된 라우터 파일들은 나중에 삭제하거나 재사용할 수 있습니다</li>
        </ul>
        
        <a href="../">홈페이지로 이동</a>
    </body>
    </html>
    <?php
}
?>