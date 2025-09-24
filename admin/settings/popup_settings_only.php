<?php 
include '../auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// DB 연결
require_once '../db.php';

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>팝업 관리 - <?= htmlspecialchars($admin_title) ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Remodal CSS -->
    <link rel="stylesheet" href="../../js/remodal/remodal.css">
    <link rel="stylesheet" href="../../js/remodal/remodal-default-theme.css">
</head>
<body>
    <div class="container mt-4">
        <h1><i class="bi bi-gear"></i> 팝업 관리</h1>
        
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-window-stack"></i> 팝업 설정</h5>
            </div>
            <div class="card-body">
                <?php
                try {
                    include __DIR__ . '/popup/popup-manager.php';
                } catch (Exception $e) {
                    echo '<div class="alert alert-danger">팝업 관리자 로드 오류: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                ?>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Remodal JS -->
    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <script src="../../js/remodal/remodal.js"></script>
</body>
</html>