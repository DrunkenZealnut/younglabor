<?php
/**
 * 초기 설정 위저드 - 메인 페이지
 * 새로운 조직에서 사이트를 쉽게 설정할 수 있도록 도와주는 위저드
 */

session_start();

// .env 파일이 이미 설정되어 있는지 확인
$envPath = dirname(__DIR__, 2) . '/.env';
$envExists = file_exists($envPath);

// 데이터베이스 연결 테스트 (선택적)
$dbConnected = false;
if ($envExists) {
    try {
        require_once dirname(__DIR__, 2) . '/includes/EnvLoader.php';
        EnvLoader::load();
        
        $host = env('DB_HOST', 'localhost');
        $database = env('DB_DATABASE', '');
        $username = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', '');
        
        if (!empty($database)) {
            $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbConnected = true;
        }
    } catch (Exception $e) {
        $dbConnected = false;
    }
}

// 위저드 단계 정의
$steps = [
    1 => ['title' => '프로젝트 기본 정보', 'file' => 'step1.php'],
    2 => ['title' => '데이터베이스 설정', 'file' => 'step2.php'],
    3 => ['title' => '조직 정보', 'file' => 'step3.php'],
    4 => ['title' => '테마 및 디자인', 'file' => 'step4.php'],
    5 => ['title' => '완료 및 요약', 'file' => 'step5.php']
];

$currentStep = isset($_GET['step']) ? (int)$_GET['step'] : 1;
if ($currentStep < 1 || $currentStep > 5) $currentStep = 1;
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>조직 웹사이트 초기 설정 위저드</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .wizard-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin: 2rem auto;
            max-width: 900px;
        }
        
        .wizard-header {
            background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 2rem 0;
            text-align: center;
        }
        
        .wizard-header h1 {
            margin: 0;
            font-weight: 300;
            font-size: 2.5rem;
        }
        
        .wizard-header p {
            margin: 0.5rem 0 0;
            opacity: 0.9;
        }
        
        .step-indicator {
            background: #f8f9fa;
            padding: 1.5rem 0;
            border-bottom: 2px solid #e9ecef;
        }
        
        .step-indicator .step {
            display: inline-block;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            line-height: 40px;
            text-align: center;
            margin: 0 0.5rem;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .step-indicator .step.active {
            background: #007bff;
            color: white;
            transform: scale(1.1);
        }
        
        .step-indicator .step.completed {
            background: #28a745;
            color: white;
        }
        
        .step-indicator .step.inactive {
            background: #e9ecef;
            color: #6c757d;
        }
        
        .wizard-content {
            padding: 3rem;
            min-height: 400px;
        }
        
        .status-card {
            border-left: 4px solid #007bff;
            margin-bottom: 2rem;
        }
        
        .status-card.success {
            border-left-color: #28a745;
        }
        
        .status-card.warning {
            border-left-color: #ffc107;
        }
        
        .status-card.danger {
            border-left-color: #dc3545;
        }
        
        .wizard-navigation {
            background: #f8f9fa;
            padding: 1.5rem 3rem;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .btn-wizard {
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        
        .btn-wizard:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .feature-card {
            text-align: center;
            padding: 2rem;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .feature-card:hover {
            border-color: #007bff;
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.2);
        }
        
        .feature-icon {
            font-size: 3rem;
            color: #007bff;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="wizard-container">
            <!-- 헤더 -->
            <div class="wizard-header">
                <h1><i class="bi bi-magic"></i> 조직 웹사이트 설정 위저드</h1>
                <p>새로운 조직 웹사이트를 쉽고 빠르게 설정하세요</p>
            </div>
            
            <!-- 진행 단계 표시 -->
            <div class="step-indicator text-center">
                <?php foreach ($steps as $stepNum => $stepInfo): ?>
                    <span class="step <?= $stepNum < $currentStep ? 'completed' : ($stepNum == $currentStep ? 'active' : 'inactive') ?>">
                        <?= $stepNum ?>
                    </span>
                    <?php if ($stepNum < count($steps)): ?>
                        <i class="bi bi-arrow-right mx-2 text-muted"></i>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            
            <!-- 메인 콘텐츠 -->
            <div class="wizard-content">
                <?php if ($currentStep == 1 && !isset($_GET['start'])): ?>
                    <!-- 시작 페이지 -->
                    <div class="text-center">
                        <div class="mb-4">
                            <i class="bi bi-house-heart feature-icon"></i>
                            <h2>새로운 조직 웹사이트 시작하기</h2>
                            <p class="lead text-muted">이 위저드를 통해 몇 분 안에 완전한 조직 웹사이트를 구축할 수 있습니다.</p>
                        </div>
                        
                        <!-- 현재 상태 -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card status-card <?= $envExists ? 'success' : 'warning' ?>">
                                    <div class="card-body">
                                        <h5><i class="bi bi-gear"></i> 환경 설정</h5>
                                        <p class="mb-0"><?= $envExists ? '✅ .env 파일이 존재합니다' : '⚠️ .env 파일을 생성해야 합니다' ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card status-card <?= $dbConnected ? 'success' : 'warning' ?>">
                                    <div class="card-body">
                                        <h5><i class="bi bi-database"></i> 데이터베이스</h5>
                                        <p class="mb-0"><?= $dbConnected ? '✅ 데이터베이스 연결됨' : '⚠️ 데이터베이스 설정 필요' ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 기능 소개 -->
                        <div class="feature-grid">
                            <div class="feature-card">
                                <i class="bi bi-palette feature-icon"></i>
                                <h5>커스텀 테마</h5>
                                <p>조직의 브랜드에 맞는 색상과 디자인을 쉽게 설정할 수 있습니다.</p>
                            </div>
                            <div class="feature-card">
                                <i class="bi bi-people feature-icon"></i>
                                <h5>조직 정보 관리</h5>
                                <p>조직의 연락처, 소셜미디어, 법적 정보를 중앙에서 관리합니다.</p>
                            </div>
                            <div class="feature-card">
                                <i class="bi bi-shield-check feature-icon"></i>
                                <h5>보안 설정</h5>
                                <p>안전한 웹사이트 운영을 위한 보안 설정을 자동으로 구성합니다.</p>
                            </div>
                            <div class="feature-card">
                                <i class="bi bi-speedometer2 feature-icon"></i>
                                <h5>성능 최적화</h5>
                                <p>빠른 로딩 속도와 SEO 최적화로 더 많은 방문자를 유치합니다.</p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- 실제 위저드 단계 -->
                    <?php
                    $stepFile = __DIR__ . '/' . $steps[$currentStep]['file'];
                    if (file_exists($stepFile)) {
                        include $stepFile;
                    } else {
                        echo "<div class='alert alert-danger'>단계 파일을 찾을 수 없습니다: " . htmlspecialchars($steps[$currentStep]['file']) . "</div>";
                    }
                    ?>
                <?php endif; ?>
            </div>
            
            <!-- 네비게이션 -->
            <div class="wizard-navigation">
                <div>
                    <?php if ($currentStep > 1 && isset($_GET['start'])): ?>
                        <a href="?step=<?= $currentStep - 1 ?>&start=1" class="btn btn-outline-secondary btn-wizard">
                            <i class="bi bi-arrow-left"></i> 이전
                        </a>
                    <?php endif; ?>
                </div>
                
                <div>
                    <span class="text-muted">
                        <?= isset($_GET['start']) ? $steps[$currentStep]['title'] : '시작하기' ?>
                    </span>
                </div>
                
                <div>
                    <?php if (!isset($_GET['start'])): ?>
                        <a href="?step=1&start=1" class="btn btn-primary btn-wizard">
                            시작하기 <i class="bi bi-arrow-right"></i>
                        </a>
                    <?php elseif ($currentStep < 5): ?>
                        <a href="?step=<?= $currentStep + 1 ?>&start=1" class="btn btn-primary btn-wizard">
                            다음 <i class="bi bi-arrow-right"></i>
                        </a>
                    <?php else: ?>
                        <a href="../" class="btn btn-success btn-wizard">
                            설정 완료 <i class="bi bi-check2"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>