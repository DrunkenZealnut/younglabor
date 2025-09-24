<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - 페이지를 찾을 수 없습니다 | 관리자</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid d-flex align-items-center justify-content-center min-vh-100">
        <div class="row justify-content-center w-100">
            <div class="col-lg-6 col-md-8 col-sm-10">
                <div class="card shadow-lg border-0">
                    <div class="card-body text-center p-5">
                        <!-- 에러 아이콘 -->
                        <div class="mb-4">
                            <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 5rem;"></i>
                        </div>
                        
                        <!-- 에러 코드 -->
                        <h1 class="display-1 fw-bold text-primary mb-0">404</h1>
                        <h2 class="h3 text-dark mb-3">페이지를 찾을 수 없습니다</h2>
                        
                        <!-- 에러 메시지 -->
                        <p class="text-muted mb-4 fs-5">
                            요청하신 페이지가 존재하지 않거나 이동되었습니다.<br>
                            URL을 다시 확인해주세요.
                        </p>
                        
                        <!-- 요청된 경로 표시 -->
                        <?php if (isset($_SERVER['REQUEST_URI'])): ?>
                        <div class="alert alert-light border mb-4">
                            <small class="text-muted">요청된 경로:</small><br>
                            <code class="text-primary"><?= htmlspecialchars($_SERVER['REQUEST_URI']) ?></code>
                        </div>
                        <?php endif; ?>
                        
                        <!-- 액션 버튼들 -->
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <a href="<?= admin_url('mvc/" class="btn btn-primary btn-lg px-4">
                                <i class="bi bi-house"></i> 관리자 홈으로
                            </a>
                            <button onclick="history.back()" class="btn btn-outline-secondary btn-lg px-4">
                                <i class="bi bi-arrow-left"></i> 이전 페이지
                            </button>
                        </div>
                        
                        <!-- 도움말 링크 -->
                        <div class="mt-4 pt-3 border-top">
                            <p class="text-muted small mb-2">자주 찾는 관리 메뉴:</p>
                            <div class="d-flex justify-content-center gap-3 flex-wrap">
                                <a href="<?= admin_url('mvc/posts" class="text-decoration-none small">
                                    <i class="bi bi-file-text"></i> 게시물 관리
                                </a>
                                <a href="<?= admin_url('mvc/events" class="text-decoration-none small">
                                    <i class="bi bi-calendar-event"></i> 이벤트 관리
                                </a>
                                <a href="<?= admin_url('mvc/inquiries" class="text-decoration-none small">
                                    <i class="bi bi-envelope"></i> 문의 관리
                                </a>
                                <a href="<?= admin_url('mvc/menus" class="text-decoration-none small">
                                    <i class="bi bi-list"></i> 메뉴 관리
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 추가 정보 -->
                <div class="text-center mt-3">
                    <small class="text-muted">
                        문제가 지속되면 시스템 관리자에게 문의해주세요.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // 페이지 로드 후 몇 초 후에 자동으로 홈으로 리다이렉트 (선택적)
    // setTimeout(function() {
    //     if (confirm('자동으로 관리자 홈페이지로 이동하시겠습니까?')) {
    //         window.location.href = '/admin/mvc/';
    //     }
    // }, 10000); // 10초 후
    
    // 키보드 단축키
    document.addEventListener('keydown', function(e) {
        if (e.key === 'h' || e.key === 'H') {
            window.location.href = '/admin/mvc/';
        } else if (e.key === 'Backspace' && !e.target.matches('input, textarea')) {
            e.preventDefault();
            history.back();
        }
    });
    </script>

    <style>
    .min-vh-100 {
        min-height: 100vh;
    }
    
    .card {
        border-radius: 1rem;
    }
    
    .btn {
        border-radius: 0.5rem;
        transition: all 0.3s ease;
    }
    
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    a:hover {
        color: #0d6efd !important;
    }
    
    /* 반응형 디자인 */
    @media (max-width: 768px) {
        .display-1 {
            font-size: 4rem;
        }
        
        .card-body {
            padding: 2rem 1.5rem !important;
        }
        
        .btn-lg {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
        }
        
        .d-flex.gap-3 {
            flex-direction: column;
            gap: 0.5rem !important;
        }
        
        .d-flex.gap-3.flex-wrap a {
            display: block;
            padding: 0.5rem;
        }
    }
    
    /* 애니메이션 */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .card {
        animation: fadeInUp 0.6s ease-out;
    }
    
    .bi-exclamation-triangle-fill {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
        100% {
            transform: scale(1);
        }
    }
    </style>
</body>
</html>