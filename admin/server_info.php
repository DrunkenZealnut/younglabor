<?php
/**
 * 서버 정보 확인 파일
 * Apache 설정, mod_rewrite 상태 등을 확인
 */

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>서버 정보</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .info-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .info-section h3 { margin-top: 0; color: #333; }
        table { border-collapse: collapse; width: 100%; }
        table th, table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        table th { background-color: #f2f2f2; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
    </style>
</head>
<body>
    <h1>🖥️ 서버 환경 정보</h1>
    
    <div class="info-section">
        <h3>📊 기본 서버 정보</h3>
        <table>
            <tr><th>항목</th><th>값</th></tr>
            <tr><td>Server Software</td><td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ?></td></tr>
            <tr><td>PHP Version</td><td><?= PHP_VERSION ?></td></tr>
            <tr><td>Document Root</td><td><?= $_SERVER['DOCUMENT_ROOT'] ?? 'N/A' ?></td></tr>
            <tr><td>Current Script Path</td><td><?= __FILE__ ?></td></tr>
            <tr><td>HTTP Host</td><td><?= $_SERVER['HTTP_HOST'] ?? 'N/A' ?></td></tr>
            <tr><td>HTTPS</td><td><?= isset($_SERVER['HTTPS']) ? ($_SERVER['HTTPS'] ? 'ON' : 'OFF') : 'NOT SET' ?></td></tr>
        </table>
    </div>
    
    <div class="info-section">
        <h3>🔧 Apache 모듈 상태</h3>
        <table>
            <tr><th>모듈</th><th>상태</th></tr>
            <tr>
                <td>mod_rewrite</td>
                <td>
                    <?php if (function_exists('apache_get_modules')): ?>
                        <?php if (in_array('mod_rewrite', apache_get_modules())): ?>
                            <span class="success">✅ 활성화</span>
                        <?php else: ?>
                            <span class="error">❌ 비활성화</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="warning">⚠️ 확인 불가 (CGI 모드일 수 있음)</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="info-section">
        <h3>📁 파일 시스템 정보</h3>
        <?php
        $admin_dir = __DIR__;
        $root_dir = dirname($admin_dir);
        ?>
        <table>
            <tr><th>경로</th><th>상태</th></tr>
            <tr><td>Admin Directory</td><td><?= $admin_dir ?></td></tr>
            <tr><td>Root Directory</td><td><?= $root_dir ?></td></tr>
            <tr>
                <td>.htaccess (root)</td>
                <td><?= file_exists($root_dir . '/.htaccess') ? '<span class="success">✅ 존재</span>' : '<span class="error">❌ 없음</span>' ?></td>
            </tr>
            <tr>
                <td>.env</td>
                <td><?= file_exists($root_dir . '/.env') ? '<span class="success">✅ 존재</span>' : '<span class="error">❌ 없음</span>' ?></td>
            </tr>
            <tr>
                <td>admin/login.php</td>
                <td><?= file_exists($admin_dir . '/login.php') ? '<span class="success">✅ 존재</span>' : '<span class="error">❌ 없음</span>' ?></td>
            </tr>
            <tr>
                <td>admin/index.php</td>
                <td><?= file_exists($admin_dir . '/index.php') ? '<span class="success">✅ 존재</span>' : '<span class="error">❌ 없음</span>' ?></td>
            </tr>
        </table>
    </div>
    
    <div class="info-section">
        <h3>🔄 URL 테스트</h3>
        <p><strong>현재 URL:</strong> <?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?></p>
        
        <h4>테스트 링크:</h4>
        <ul>
            <li><a href="login.php" target="_blank">login.php 직접 접근</a></li>
            <li><a href="index.php" target="_blank">index.php 직접 접근</a></li>
            <li><a href="debug_login.php" target="_blank">디버그 도구</a></li>
            <li><a href="../" target="_blank">사이트 루트</a></li>
        </ul>
    </div>
    
    <div class="info-section">
        <h3>⚙️ PHP 설정</h3>
        <table>
            <tr><th>설정</th><th>값</th></tr>
            <tr><td>session.cookie_httponly</td><td><?= ini_get('session.cookie_httponly') ? '1' : '0' ?></td></tr>
            <tr><td>session.cookie_secure</td><td><?= ini_get('session.cookie_secure') ? '1' : '0' ?></td></tr>
            <tr><td>session.use_strict_mode</td><td><?= ini_get('session.use_strict_mode') ? '1' : '0' ?></td></tr>
            <tr><td>session.gc_maxlifetime</td><td><?= ini_get('session.gc_maxlifetime') ?></td></tr>
            <tr><td>allow_url_fopen</td><td><?= ini_get('allow_url_fopen') ? '1' : '0' ?></td></tr>
        </table>
    </div>
    
    <div class="info-section">
        <h3>🌐 환경 변수 (일부)</h3>
        <?php
        try {
            require_once 'env_loader.php';
            loadEnv();
            echo '<table>';
            echo '<tr><th>변수</th><th>값</th></tr>';
            echo '<tr><td>BASE_PATH</td><td>' . htmlspecialchars(env('BASE_PATH', 'NOT SET')) . '</td></tr>';
            echo '<tr><td>APP_URL</td><td>' . htmlspecialchars(env('APP_URL', 'NOT SET')) . '</td></tr>';
            echo '<tr><td>DB_DATABASE</td><td>' . htmlspecialchars(env('DB_DATABASE', 'NOT SET')) . '</td></tr>';
            echo '<tr><td>APP_ENV</td><td>' . htmlspecialchars(env('APP_ENV', 'NOT SET')) . '</td></tr>';
            echo '</table>';
        } catch (Exception $e) {
            echo '<p class="error">❌ .env 파일 로드 오류: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        ?>
    </div>
    
    <?php if (isset($_GET['phpinfo'])): ?>
    <div class="info-section">
        <h3>📋 전체 PHP 정보</h3>
        <?php phpinfo(); ?>
    </div>
    <?php else: ?>
    <div class="info-section">
        <p><a href="?phpinfo=1" target="_blank">📋 전체 PHP 정보 보기</a></p>
    </div>
    <?php endif; ?>
</body>
</html>