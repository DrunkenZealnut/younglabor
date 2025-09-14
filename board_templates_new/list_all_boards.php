<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';

echo "<h3>모든 게시판 목록</h3>";

try {
    $stmt = $pdo->prepare("SELECT id, board_name, write_level, is_active FROM labor_rights_boards ORDER BY id");
    $stmt->execute();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>게시판명</th><th>Write Level</th><th>활성상태</th><th>CAPTCHA 필요</th></tr>";
    
    while ($board = $stmt->fetch()) {
        require_once 'captcha_helper.php';
        $need_captcha = is_captcha_required($board['id'], 'GENERAL');
        $captcha_text = $need_captcha ? 'Yes' : 'No';
        
        echo "<tr>";
        echo "<td>{$board['id']}</td>";
        echo "<td>{$board['board_name']}</td>";
        echo "<td>{$board['write_level']}</td>";
        echo "<td>" . ($board['is_active'] ? 'Active' : 'Inactive') . "</td>";
        echo "<td style='color: " . ($need_captcha ? 'red' : 'green') . ";'><strong>$captcha_text</strong></td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p>오류: " . $e->getMessage() . "</p>";
}
?>