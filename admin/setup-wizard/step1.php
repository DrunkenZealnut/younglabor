<?php
/**
 * 설정 위저드 1단계: 프로젝트 기본 정보
 */

// 현재 .env 값 읽기
$envPath = dirname(__DIR__, 2) . '/.env';
$currentValues = [];

if (file_exists($envPath)) {
    require_once dirname(__DIR__, 2) . '/includes/EnvLoader.php';
    EnvLoader::load();
    
    $currentValues = [
        'PROJECT_NAME' => env('PROJECT_NAME', ''),
        'PROJECT_SLUG' => env('PROJECT_SLUG', ''),
        'ORG_NAME_SHORT' => env('ORG_NAME_SHORT', ''),
        'ORG_NAME_FULL' => env('ORG_NAME_FULL', ''),
        'ORG_NAME_EN' => env('ORG_NAME_EN', ''),
        'PRODUCTION_DOMAIN' => env('PRODUCTION_DOMAIN', ''),
        'LOCAL_PORT' => env('LOCAL_PORT', '8080')
    ];
}

// 폼 처리
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $projectName = trim($_POST['project_name'] ?? '');
        $projectSlug = trim($_POST['project_slug'] ?? '');
        $orgShort = trim($_POST['org_short'] ?? '');
        $orgFull = trim($_POST['org_full'] ?? '');
        $orgEn = trim($_POST['org_en'] ?? '');
        $domain = trim($_POST['domain'] ?? '');
        $port = trim($_POST['port'] ?? '8080');
        
        // 유효성 검사
        if (empty($projectName) || empty($projectSlug) || empty($orgShort)) {
            throw new Exception('필수 필드를 모두 입력해주세요.');
        }
        
        // 프로젝트 슬러그 검증 (영문자, 숫자, 언더스코어만 허용)
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $projectSlug)) {
            throw new Exception('프로젝트 슬러그는 영문자, 숫자, 언더스코어만 사용할 수 있습니다.');
        }
        
        // .env 파일 업데이트 또는 생성
        $envTemplate = dirname(__DIR__, 2) . '/.env.example';
        $envContent = file_get_contents($envTemplate);
        
        // 값 치환
        $replacements = [
            'YOUR_ORGANIZATION_PROJECT' => $projectName,
            'your_organization' => $projectSlug,
            '조직명' => $orgShort,
            '사단법인 조직명' => $orgFull,
            'YOUR_ORG' => $orgEn,
            'your-organization.org' => $domain,
            '8080' => $port
        ];
        
        foreach ($replacements as $search => $replace) {
            if (!empty($replace)) {
                $envContent = str_replace($search, $replace, $envContent);
            }
        }
        
        // .env 파일 저장
        file_put_contents($envPath, $envContent);
        
        $success = true;
        
        // 값 새로고침
        $currentValues = [
            'PROJECT_NAME' => $projectName,
            'PROJECT_SLUG' => $projectSlug,
            'ORG_NAME_SHORT' => $orgShort,
            'ORG_NAME_FULL' => $orgFull,
            'ORG_NAME_EN' => $orgEn,
            'PRODUCTION_DOMAIN' => $domain,
            'LOCAL_PORT' => $port
        ];
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="text-center mb-4">
            <h2><i class="bi bi-info-circle text-primary"></i> 프로젝트 기본 정보</h2>
            <p class="text-muted">새로운 조직 웹사이트의 기본 정보를 설정합니다.</p>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> 프로젝트 기본 정보가 성공적으로 저장되었습니다!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="needs-validation" novalidate>
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-building"></i> 프로젝트 정보</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="project_name" class="form-label">프로젝트 이름 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="project_name" name="project_name" 
                                   value="<?= htmlspecialchars($currentValues['PROJECT_NAME'] ?? '') ?>" 
                                   placeholder="예: HOPEC 웹사이트" required>
                            <div class="form-text">이 프로젝트의 전체 이름을 입력하세요.</div>
                            <div class="invalid-feedback">프로젝트 이름을 입력해주세요.</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="project_slug" class="form-label">프로젝트 슬러그 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="project_slug" name="project_slug" 
                                   value="<?= htmlspecialchars($currentValues['PROJECT_SLUG'] ?? '') ?>" 
                                   placeholder="예: hopec" pattern="[a-zA-Z0-9_]+" required>
                            <div class="form-text">영문자, 숫자, 언더스코어만 사용 (데이터베이스, URL 등에 사용)</div>
                            <div class="invalid-feedback">영문자, 숫자, 언더스코어만 사용할 수 있습니다.</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="bi bi-people"></i> 조직 정보</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="org_short" class="form-label">조직 짧은 이름 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="org_short" name="org_short" 
                                   value="<?= htmlspecialchars($currentValues['ORG_NAME_SHORT'] ?? '') ?>" 
                                   placeholder="예: 희망씨" required>
                            <div class="form-text">사이트 제목 등에 주로 사용되는 짧은 이름</div>
                            <div class="invalid-feedback">조직의 짧은 이름을 입력해주세요.</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="org_full" class="form-label">조직 정식 명칭</label>
                            <input type="text" class="form-control" id="org_full" name="org_full" 
                                   value="<?= htmlspecialchars($currentValues['ORG_NAME_FULL'] ?? '') ?>" 
                                   placeholder="예: 사단법인 희망씨">
                            <div class="form-text">법적 문서에 사용되는 정식 명칭</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="org_en" class="form-label">조직 영문 이름</label>
                        <input type="text" class="form-control" id="org_en" name="org_en" 
                               value="<?= htmlspecialchars($currentValues['ORG_NAME_EN'] ?? '') ?>" 
                               placeholder="예: HOPEC">
                        <div class="form-text">영문 사이트나 국제 대외활동에 사용</div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="bi bi-globe"></i> 도메인 설정</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="domain" class="form-label">운영 도메인</label>
                            <input type="text" class="form-control" id="domain" name="domain" 
                                   value="<?= htmlspecialchars($currentValues['PRODUCTION_DOMAIN'] ?? '') ?>" 
                                   placeholder="예: hopec.co.kr">
                            <div class="form-text">실제 서비스할 도메인 (없으면 나중에 설정 가능)</div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="port" class="form-label">로컬 포트</label>
                            <input type="number" class="form-control" id="port" name="port" 
                                   value="<?= htmlspecialchars($currentValues['LOCAL_PORT'] ?? '8080') ?>" 
                                   min="3000" max="9999">
                            <div class="form-text">개발 시 사용할 포트</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save"></i> 저장하고 계속
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 프로젝트 이름에서 슬러그 자동 생성
    const projectNameInput = document.getElementById('project_name');
    const projectSlugInput = document.getElementById('project_slug');
    
    projectNameInput.addEventListener('input', function() {
        const name = this.value;
        const slug = name.toLowerCase()
                        .replace(/[^a-z0-9가-힣\s]/g, '')
                        .replace(/\s+/g, '_')
                        .replace(/[가-힣]/g, '') // 한글 제거
                        .substring(0, 20);
        
        if (projectSlugInput.value === '' || projectSlugInput.value === slug) {
            projectSlugInput.value = slug;
        }
    });
    
    // 조직 짧은 이름에서 영문 이름 자동 생성 (선택적)
    const orgShortInput = document.getElementById('org_short');
    const orgEnInput = document.getElementById('org_en');
    
    orgShortInput.addEventListener('input', function() {
        if (orgEnInput.value === '') {
            // 간단한 한글->영문 변환 (실제로는 사용자가 직접 입력해야 함)
            const korToEng = {
                '희망': 'HOPE',
                '씨': 'C',
                '복지': 'WELFARE',
                '시민': 'CITIZEN',
                '환경': 'ENVIRONMENT'
            };
            
            let engName = this.value;
            Object.keys(korToEng).forEach(kor => {
                engName = engName.replace(kor, korToEng[kor]);
            });
            
            orgEnInput.placeholder = `예: ${engName.toUpperCase()}`;
        }
    });
    
    // Bootstrap 유효성 검사 활성화
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
});
</script>