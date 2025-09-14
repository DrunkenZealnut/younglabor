<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>오류 발생</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background-color: #f8f9fa; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
        }
        .error-container { 
            text-align: center; 
            max-width: 600px; 
        }
        .error-code { 
            font-size: 5rem; 
            font-weight: bold; 
            color: #dc3545; 
        }
        .error-icon { 
            font-size: 4rem; 
            color: #dc3545; 
            margin-bottom: 1rem; 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 error-container">
                <div class="error-icon">⚠️</div>
                <div class="error-code"><?= $code ?? 500 ?></div>
                <h2 class="mb-4">
                    <?php 
                    switch($code ?? 500) {
                        case 404:
                            echo '페이지를 찾을 수 없습니다';
                            break;
                        case 403:
                            echo '접근이 거부되었습니다';
                            break;
                        case 500:
                        default:
                            echo '서버 오류가 발생했습니다';
                            break;
                    }
                    ?>
                </h2>
                
                <?php if (isset($message)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>
                
                <p class="text-muted mb-4">
                    요청하신 페이지를 처리하는 중 문제가 발생했습니다.<br>
                    잠시 후 다시 시도해주세요.
                </p>
                
                <div class="d-flex gap-3 justify-content-center">
                    <a href="javascript:history.back()" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 이전으로
                    </a>
                    <a href="<?= get_base_url() ?>/admin/" class="btn btn-primary">
                        <i class="bi bi-house"></i> 대시보드
                    </a>
                </div>
                
                <?php if (isDevelopmentEnvironment()): ?>
                    <div class="mt-5">
                        <details class="text-start">
                            <summary class="btn btn-outline-info btn-sm">
                                개발자 정보 보기
                            </summary>
                            <div class="mt-3">
                                <div class="alert alert-info">
                                    <strong>요청 정보:</strong><br>
                                    URL: <?= $_SERVER['REQUEST_URI'] ?? 'Unknown' ?><br>
                                    Method: <?= $_SERVER['REQUEST_METHOD'] ?? 'Unknown' ?><br>
                                    Time: <?= date('Y-m-d H:i:s') ?><br>
                                    User Agent: <?= htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown') ?>
                                </div>
                                
                                <?php if (isset($exception) && $exception instanceof Exception): ?>
                                    <div class="alert alert-warning">
                                        <strong>예외 정보:</strong><br>
                                        <strong>파일:</strong> <?= $exception->getFile() ?>:<?= $exception->getLine() ?><br>
                                        <strong>메시지:</strong> <?= htmlspecialchars($exception->getMessage()) ?><br>
                                        <details class="mt-2">
                                            <summary>스택 트레이스</summary>
                                            <pre class="mt-2"><?= htmlspecialchars($exception->getTraceAsString()) ?></pre>
                                        </details>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </details>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>