<?php
/**
 * 테마 미리보기 페이지
 * AJAX로 호출되어 테마의 실제 모습을 미리 보여줍니다.
 */

// 에러 리포팅 설정
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 기본 인증 및 설정
require_once 'auth.php';
require_once 'db.php';
require_once '../includes/theme_functions.php';

// CORS 헤더 설정 (AJAX 요청용)
header('Content-Type: text/html; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// 미리보기 요청 처리
$theme_id = $_GET['theme_id'] ?? '';
$preview_type = $_GET['type'] ?? 'card'; // card, full, iframe

if (empty($theme_id)) {
    http_response_code(400);
    echo '<div class="alert alert-danger">테마 ID가 지정되지 않았습니다.</div>';
    exit;
}

// 테마 데이터 가져오기
$theme_data = null;

// 1. 기본 테마에서 찾기
$default_themes = getDefaultThemes();
if (isset($default_themes[$theme_id])) {
    $theme_data = $default_themes[$theme_id];
} else {
    // 2. 커스텀 테마에서 찾기
    $custom_themes = getCustomThemes($pdo);
    if (isset($custom_themes[$theme_id])) {
        $theme_data = $custom_themes[$theme_id];
    }
}

if (!$theme_data) {
    http_response_code(404);
    echo '<div class="alert alert-danger">테마를 찾을 수 없습니다: ' . htmlspecialchars($theme_id) . '</div>';
    exit;
}

// CSS 생성
$theme_css = generateThemeCSS($theme_data['settings']);

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>테마 미리보기 - <?= htmlspecialchars($theme_data['name']) ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- 테마 CSS -->
    <style>
        <?= $theme_css ?>
        
        /* 미리보기 전용 스타일 */
        body {
            margin: 0;
            padding: <?= $preview_type === 'card' ? '20px' : '0' ?>;
            background: var(--light-color, #f8f9fa);
        }
        
        .preview-container {
            max-width: <?= $preview_type === 'full' ? '100%' : '800px' ?>;
            margin: 0 auto;
        }
        
        .preview-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 40px 0;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .preview-section {
            background: white;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .color-sample {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            display: inline-block;
            margin: 5px;
            border: 2px solid rgba(0,0,0,0.1);
        }
        
        .font-sample {
            padding: 15px;
            margin: 10px 0;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }
        
        <?php if ($preview_type === 'iframe'): ?>
        html, body {
            height: 100%;
            overflow-x: hidden;
        }
        .preview-container {
            height: 100vh;
            overflow-y: auto;
        }
        <?php endif; ?>
    </style>
</head>
<body>

<div class="preview-container">
    <!-- 미리보기 헤더 -->
    <div class="preview-header">
        <div class="container">
            <h1><?= htmlspecialchars($theme_data['name']) ?></h1>
            <p class="lead"><?= htmlspecialchars($theme_data['description'] ?? '') ?></p>
        </div>
    </div>
    
    <?php if ($preview_type !== 'card'): ?>
    <!-- 색상 팔레트 섹션 -->
    <div class="preview-section">
        <h3>색상 팔레트</h3>
        <div class="row">
            <?php 
            $color_labels = [
                'primary_color' => '주 색상',
                'secondary_color' => '보조 색상',
                'success_color' => '성공',
                'info_color' => '정보',
                'warning_color' => '경고',
                'danger_color' => '위험',
                'light_color' => '밝은 색상',
                'dark_color' => '어두운 색상'
            ];
            
            foreach ($color_labels as $key => $label):
                if (isset($theme_data['settings'][$key])): 
            ?>
            <div class="col-md-3 col-6 text-center mb-3">
                <div class="color-sample" style="background-color: <?= $theme_data['settings'][$key] ?>"></div>
                <div class="small mt-2">
                    <strong><?= $label ?></strong><br>
                    <code><?= $theme_data['settings'][$key] ?></code>
                </div>
            </div>
            <?php endif; endforeach; ?>
        </div>
    </div>
    
    <!-- 타이포그래피 섹션 -->
    <div class="preview-section">
        <h3>타이포그래피</h3>
        
        <div class="font-sample" style="font-family: <?= $theme_data['settings']['heading_font'] ?? 'inherit' ?>">
            <h1>제목 1 - <?= $theme_data['settings']['heading_font'] ?? 'Default' ?></h1>
            <h2>제목 2 - 한글 제목 샘플</h2>
            <h3>제목 3 - Typography Sample</h3>
        </div>
        
        <div class="font-sample" style="font-family: <?= $theme_data['settings']['body_font'] ?? 'inherit' ?>; font-size: <?= $theme_data['settings']['font_size_base'] ?? '1rem' ?>">
            <p><strong>본문 폰트:</strong> <?= $theme_data['settings']['body_font'] ?? 'Default' ?></p>
            <p><strong>기본 크기:</strong> <?= $theme_data['settings']['font_size_base'] ?? '1rem' ?></p>
            <p>이것은 본문 텍스트의 예시입니다. 한글과 English가 함께 표시되어 폰트의 모습을 확인할 수 있습니다. Lorem ipsum dolor sit amet, consectetur adipiscing elit. 노동권 보호와 근로자의 권익 향상을 위한 다양한 정보를 제공합니다.</p>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- 컴포넌트 미리보기 -->
    <div class="preview-section">
        <h3>UI 컴포넌트</h3>
        
        <!-- 버튼 샘플 -->
        <div class="mb-4">
            <h5>버튼</h5>
            <button class="btn btn-primary me-2">주 버튼</button>
            <button class="btn btn-secondary me-2">보조 버튼</button>
            <button class="btn btn-success me-2">성공</button>
            <button class="btn btn-info me-2">정보</button>
            <button class="btn btn-warning me-2">경고</button>
            <button class="btn btn-danger">위험</button>
        </div>
        
        <!-- 알림 샘플 -->
        <div class="mb-4">
            <h5>알림</h5>
            <div class="alert alert-primary" role="alert">
                <i class="bi bi-info-circle"></i> 이것은 주요 정보 알림입니다.
            </div>
            <div class="alert alert-success" role="alert">
                <i class="bi bi-check-circle"></i> 작업이 성공적으로 완료되었습니다.
            </div>
            <div class="alert alert-warning" role="alert">
                <i class="bi bi-exclamation-triangle"></i> 주의가 필요한 상황입니다.
            </div>
        </div>
        
        <!-- 카드 샘플 -->
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="card-title mb-0">카드 제목</h6>
                    </div>
                    <div class="card-body">
                        <p class="card-text">카드 내용이 여기에 표시됩니다. 이 테마로 카드가 어떻게 보이는지 확인해보세요.</p>
                        <a href="#" class="btn btn-primary btn-sm">더 보기</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Simple Card</h6>
                        <p class="card-text">간단한 카드 스타일의 예시입니다.</p>
                        <small class="text-muted">2시간 전</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-success">
                    <div class="card-body">
                        <h6 class="card-title text-success">
                            <i class="bi bi-check-circle"></i> 성공 카드
                        </h6>
                        <p class="card-text">테마의 성공 색상을 활용한 카드입니다.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($preview_type === 'full'): ?>
    <!-- 테이블 샘플 -->
    <div class="preview-section">
        <h3>테이블</h3>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>제목</th>
                        <th>작성자</th>
                        <th>작성일</th>
                        <th>상태</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>노동법 개정안 안내</td>
                        <td>관리자</td>
                        <td>2024-09-01</td>
                        <td><span class="badge bg-success">게시중</span></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>근로시간 단축 관련 공지</td>
                        <td>홍길동</td>
                        <td>2024-08-30</td>
                        <td><span class="badge bg-warning">검토중</span></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>임금 체불 신고 방법</td>
                        <td>김노동</td>
                        <td>2024-08-28</td>
                        <td><span class="badge bg-primary">완료</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- 폼 샘플 -->
    <div class="preview-section">
        <h3>폼 요소</h3>
        <form>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="sampleInput" class="form-label">제목</label>
                        <input type="text" class="form-control" id="sampleInput" placeholder="제목을 입력하세요">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="sampleSelect" class="form-label">카테고리</label>
                        <select class="form-select" id="sampleSelect">
                            <option>공지사항</option>
                            <option>자료실</option>
                            <option>문의사항</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="sampleTextarea" class="form-label">내용</label>
                <textarea class="form-control" id="sampleTextarea" rows="4" placeholder="내용을 입력하세요"></textarea>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="sampleCheck">
                <label class="form-check-label" for="sampleCheck">
                    공지사항으로 등록
                </label>
            </div>
            <button type="submit" class="btn btn-primary">저장</button>
            <button type="button" class="btn btn-secondary ms-2">취소</button>
        </form>
    </div>
    <?php endif; ?>
    
    <!-- 미리보기 푸터 -->
    <?php if ($preview_type !== 'card'): ?>
    <div class="preview-section text-center">
        <p class="text-muted">
            <i class="bi bi-palette"></i>
            테마 미리보기 - <?= htmlspecialchars($theme_data['name']) ?>
        </p>
        <small class="text-muted">
            실제 사이트에 적용되면 이와 같은 모습으로 표시됩니다.
        </small>
    </div>
    <?php endif; ?>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php if ($preview_type === 'iframe'): ?>
<script>
// iframe 내에서의 링크 클릭 방지
document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('a');
    links.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
        });
    });
    
    // 폼 제출 방지
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
        });
    });
});
</script>
<?php endif; ?>

</body>
</html>