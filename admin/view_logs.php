<?php
/**
 * 로그 확인 페이지
 */
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>로그 확인</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .log-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .log-entry { margin: 10px 0; padding: 10px; background: #f9f9f9; border-left: 3px solid #007cba; }
        .timestamp { font-weight: bold; color: #666; }
        .message { color: #333; margin: 5px 0; }
        .data { background: #f0f8ff; padding: 5px; font-family: monospace; font-size: 12px; white-space: pre-wrap; }
        .button { padding: 10px 20px; background: #007cba; color: white; border: none; cursor: pointer; border-radius: 3px; text-decoration: none; display: inline-block; }
        .error { border-left-color: #dc3545; }
        .success { border-left-color: #28a745; }
    </style>
</head>
<body>
    <h1>📋 로그 확인</h1>
    
    <p>
        <a href="login.php" class="button">🔙 로그인 페이지</a>
        <a href="simple_login_test.php" class="button">🧪 테스트 도구</a>
        <a href="" class="button">🔄 새로고침</a>
    </p>
    
    <div class="log-section">
        <h2>📝 실제 로그인 로그 (actual_login.log)</h2>
        <?php
        $log_file = __DIR__ . '/../logs/actual_login.log';
        if (file_exists($log_file)) {
            $content = file_get_contents($log_file);
            $logs = array_filter(explode("\n", $content));
            
            if (!empty($logs)) {
                echo '<div style="max-height: 600px; overflow-y: auto;">';
                foreach (array_reverse(array_slice($logs, -20)) as $log_line) {
                    $entry = json_decode($log_line, true);
                    if ($entry) {
                        $css_class = '';
                        if (strpos($entry['message'], '오류') !== false || strpos($entry['message'], 'error') !== false) {
                            $css_class = 'error';
                        } else if (strpos($entry['message'], '성공') !== false || strpos($entry['message'], 'success') !== false) {
                            $css_class = 'success';
                        }
                        
                        echo '<div class="log-entry ' . $css_class . '">';
                        echo '<div class="timestamp">' . htmlspecialchars($entry['timestamp']) . ' [세션: ' . htmlspecialchars($entry['session_id']) . ']</div>';
                        echo '<div class="message">' . htmlspecialchars($entry['message']) . '</div>';
                        if ($entry['data']) {
                            echo '<div class="data">' . htmlspecialchars(print_r($entry['data'], true)) . '</div>';
                        }
                        echo '</div>';
                    }
                }
                echo '</div>';
            } else {
                echo '<p>로그 데이터가 없습니다.</p>';
            }
        } else {
            echo '<p>로그 파일이 없습니다: ' . htmlspecialchars($log_file) . '</p>';
        }
        ?>
    </div>
    
    <div class="log-section">
        <h2>🧪 테스트 로그인 로그 (simple_login.log)</h2>
        <?php
        $test_log_file = __DIR__ . '/../logs/simple_login.log';
        if (file_exists($test_log_file)) {
            $content = file_get_contents($test_log_file);
            $logs = array_filter(explode("\n", $content));
            
            if (!empty($logs)) {
                echo '<div style="max-height: 400px; overflow-y: auto;">';
                foreach (array_reverse(array_slice($logs, -10)) as $log_line) {
                    $entry = json_decode($log_line, true);
                    if ($entry) {
                        echo '<div class="log-entry">';
                        echo '<div class="timestamp">' . htmlspecialchars($entry['timestamp']) . '</div>';
                        echo '<div class="message">' . htmlspecialchars($entry['message']) . '</div>';
                        if ($entry['data']) {
                            echo '<div class="data">' . htmlspecialchars(print_r($entry['data'], true)) . '</div>';
                        }
                        echo '</div>';
                    }
                }
                echo '</div>';
            } else {
                echo '<p>테스트 로그가 없습니다.</p>';
            }
        } else {
            echo '<p>테스트 로그 파일이 없습니다.</p>';
        }
        ?>
    </div>
    
    <div class="log-section">
        <h2>🔐 Auth 검증 로그 (auth_debug.log)</h2>
        <?php
        $auth_log_file = __DIR__ . '/../logs/auth_debug.log';
        if (file_exists($auth_log_file)) {
            $content = file_get_contents($auth_log_file);
            $logs = array_filter(explode("\n", $content));
            
            if (!empty($logs)) {
                echo '<div style="max-height: 400px; overflow-y: auto;">';
                foreach (array_reverse(array_slice($logs, -10)) as $log_line) {
                    $entry = json_decode($log_line, true);
                    if ($entry) {
                        $css_class = '';
                        if (strpos($entry['message'], '실패') !== false || strpos($entry['message'], '만료') !== false) {
                            $css_class = 'error';
                        }
                        
                        echo '<div class="log-entry ' . $css_class . '">';
                        echo '<div class="timestamp">' . htmlspecialchars($entry['timestamp']) . ' [세션: ' . htmlspecialchars($entry['session_id']) . ']</div>';
                        echo '<div class="message">' . htmlspecialchars($entry['message']) . '</div>';
                        if ($entry['data']) {
                            echo '<div class="data">' . htmlspecialchars(print_r($entry['data'], true)) . '</div>';
                        }
                        echo '</div>';
                    }
                }
                echo '</div>';
            } else {
                echo '<p>Auth 로그가 없습니다.</p>';
            }
        } else {
            echo '<p>Auth 로그 파일이 없습니다.</p>';
        }
        ?>
    </div>
    
    <div class="log-section">
        <h2>🗑️ 로그 관리</h2>
        <form method="POST">
            <button type="submit" name="clear_logs" value="1" class="button" style="background: #dc3545;" 
                    onclick="return confirm('모든 로그를 삭제하시겠습니까?')">
                🗑️ 모든 로그 삭제
            </button>
        </form>
        
        <?php
        if (isset($_POST['clear_logs'])) {
            @unlink($log_file);
            @unlink($test_log_file);
            @unlink($auth_log_file);
            echo '<div style="color: green; margin: 10px 0;">✅ 로그가 삭제되었습니다. <a href="">새로고침</a></div>';
        }
        ?>
    </div>
</body>
</html>