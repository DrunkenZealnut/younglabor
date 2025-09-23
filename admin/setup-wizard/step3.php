<?php
/**
 * 설정 위저드 3단계: 조직 정보
 */

// 현재 .env 값 읽기
$envPath = dirname(__DIR__, 2) . '/.env';
$currentValues = [];

if (file_exists($envPath)) {
    require_once dirname(__DIR__, 2) . '/includes/EnvLoader.php';
    EnvLoader::load();
    
    $currentValues = [
        'ORG_DESCRIPTION' => env('ORG_DESCRIPTION', ''),
        'CONTACT_EMAIL' => env('CONTACT_EMAIL', ''),
        'CONTACT_PHONE' => env('CONTACT_PHONE', ''),
        'ORG_ADDRESS' => env('ORG_ADDRESS', ''),
        'BANK_ACCOUNT_HOLDER' => env('BANK_ACCOUNT_HOLDER', ''),
        'BANK_ACCOUNT_NUMBER' => env('BANK_ACCOUNT_NUMBER', ''),
        'BANK_NAME' => env('BANK_NAME', ''),
        'ORG_FACEBOOK' => env('ORG_FACEBOOK', ''),
        'ORG_INSTAGRAM' => env('ORG_INSTAGRAM', ''),
        'ORG_YOUTUBE' => env('ORG_YOUTUBE', ''),
        'ORG_BLOG' => env('ORG_BLOG', ''),
        'FEATURE_DONATIONS' => env('FEATURE_DONATIONS', 'true'),
        'FEATURE_EVENTS' => env('FEATURE_EVENTS', 'true'),
        'FEATURE_GALLERY' => env('FEATURE_GALLERY', 'true'),
        'FEATURE_NEWSLETTER' => env('FEATURE_NEWSLETTER', 'true')
    ];
}

// 폼 처리
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $formData = [
            'ORG_DESCRIPTION' => trim($_POST['org_description'] ?? ''),
            'CONTACT_EMAIL' => trim($_POST['contact_email'] ?? ''),
            'CONTACT_PHONE' => trim($_POST['contact_phone'] ?? ''),
            'ORG_ADDRESS' => trim($_POST['org_address'] ?? ''),
            'BANK_ACCOUNT_HOLDER' => trim($_POST['bank_holder'] ?? ''),
            'BANK_ACCOUNT_NUMBER' => trim($_POST['bank_number'] ?? ''),
            'BANK_NAME' => trim($_POST['bank_name'] ?? ''),
            'ORG_FACEBOOK' => trim($_POST['facebook'] ?? ''),
            'ORG_INSTAGRAM' => trim($_POST['instagram'] ?? ''),
            'ORG_YOUTUBE' => trim($_POST['youtube'] ?? ''),
            'ORG_BLOG' => trim($_POST['blog'] ?? ''),
            'FEATURE_DONATIONS' => isset($_POST['feature_donations']) ? 'true' : 'false',
            'FEATURE_EVENTS' => isset($_POST['feature_events']) ? 'true' : 'false',
            'FEATURE_GALLERY' => isset($_POST['feature_gallery']) ? 'true' : 'false',
            'FEATURE_NEWSLETTER' => isset($_POST['feature_newsletter']) ? 'true' : 'false'
        ];
        
        // 이메일 유효성 검사
        if (!empty($formData['CONTACT_EMAIL']) && !filter_var($formData['CONTACT_EMAIL'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('유효한 이메일 주소를 입력해주세요.');
        }
        
        // .env 파일 업데이트
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            
            foreach ($formData as $key => $value) {
                $pattern = "/^$key=.*$/m";
                $replacement = "$key=" . ($value ? $value : '');
                
                if (preg_match($pattern, $envContent)) {
                    $envContent = preg_replace($pattern, $replacement, $envContent);
                } else {
                    // 키가 없으면 추가
                    $envContent .= "\n$replacement";
                }
            }
            
            file_put_contents($envPath, $envContent);
        }
        
        $success = true;
        $currentValues = array_merge($currentValues, $formData);
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="text-center mb-4">
            <h2><i class="bi bi-building text-primary"></i> 조직 정보</h2>
            <p class="text-muted">조직의 상세 정보와 연락처, 기능 설정을 구성합니다.</p>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> 조직 정보가 성공적으로 저장되었습니다!
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
            <!-- 기본 정보 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-info-circle"></i> 기본 정보</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="org_description" class="form-label">조직 소개</label>
                        <textarea class="form-control" id="org_description" name="org_description" rows="3" 
                                  placeholder="조직의 목적과 활동에 대해 간략히 설명해주세요"><?= htmlspecialchars($currentValues['ORG_DESCRIPTION'] ?? '') ?></textarea>
                        <div class="form-text">웹사이트 메인 페이지와 SEO에 사용됩니다.</div>
                    </div>
                </div>
            </div>
            
            <!-- 연락처 정보 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-telephone"></i> 연락처 정보</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contact_email" class="form-label">이메일</label>
                            <input type="email" class="form-control" id="contact_email" name="contact_email" 
                                   value="<?= htmlspecialchars($currentValues['CONTACT_EMAIL'] ?? '') ?>" 
                                   placeholder="info@organization.org">
                            <div class="form-text">문의나 연락을 받을 대표 이메일</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="contact_phone" class="form-label">전화번호</label>
                            <input type="tel" class="form-control" id="contact_phone" name="contact_phone" 
                                   value="<?= htmlspecialchars($currentValues['CONTACT_PHONE'] ?? '') ?>" 
                                   placeholder="02-1234-5678">
                            <div class="form-text">대표 연락처</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="org_address" class="form-label">주소</label>
                        <textarea class="form-control" id="org_address" name="org_address" rows="2" 
                                  placeholder="조직의 주소를 입력하세요"><?= htmlspecialchars($currentValues['ORG_ADDRESS'] ?? '') ?></textarea>
                        <div class="form-text">사업자 등록 주소 또는 대표 주소</div>
                    </div>
                </div>
            </div>
            
            <!-- 후원 계좌 정보 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-credit-card"></i> 후원 계좌 정보</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="bank_name" class="form-label">은행명</label>
                            <select class="form-select" id="bank_name" name="bank_name">
                                <option value="">선택하세요</option>
                                <option value="KB국민은행" <?= ($currentValues['BANK_NAME'] ?? '') === 'KB국민은행' ? 'selected' : '' ?>>KB국민은행</option>
                                <option value="신한은행" <?= ($currentValues['BANK_NAME'] ?? '') === '신한은행' ? 'selected' : '' ?>>신한은행</option>
                                <option value="우리은행" <?= ($currentValues['BANK_NAME'] ?? '') === '우리은행' ? 'selected' : '' ?>>우리은행</option>
                                <option value="하나은행" <?= ($currentValues['BANK_NAME'] ?? '') === '하나은행' ? 'selected' : '' ?>>하나은행</option>
                                <option value="NH농협은행" <?= ($currentValues['BANK_NAME'] ?? '') === 'NH농협은행' ? 'selected' : '' ?>>NH농협은행</option>
                                <option value="IBK기업은행" <?= ($currentValues['BANK_NAME'] ?? '') === 'IBK기업은행' ? 'selected' : '' ?>>IBK기업은행</option>
                                <option value="SC제일은행" <?= ($currentValues['BANK_NAME'] ?? '') === 'SC제일은행' ? 'selected' : '' ?>>SC제일은행</option>
                                <option value="카카오뱅크" <?= ($currentValues['BANK_NAME'] ?? '') === '카카오뱅크' ? 'selected' : '' ?>>카카오뱅크</option>
                                <option value="토스뱅크" <?= ($currentValues['BANK_NAME'] ?? '') === '토스뱅크' ? 'selected' : '' ?>>토스뱅크</option>
                                <option value="기타" <?= !in_array($currentValues['BANK_NAME'] ?? '', ['', 'KB국민은행', '신한은행', '우리은행', '하나은행', 'NH농협은행', 'IBK기업은행', 'SC제일은행', '카카오뱅크', '토스뱅크']) ? 'selected' : '' ?>>기타</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="bank_holder" class="form-label">예금주</label>
                            <input type="text" class="form-control" id="bank_holder" name="bank_holder" 
                                   value="<?= htmlspecialchars($currentValues['BANK_ACCOUNT_HOLDER'] ?? '') ?>" 
                                   placeholder="조직명 또는 대표자명">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="bank_number" class="form-label">계좌번호</label>
                            <input type="text" class="form-control" id="bank_number" name="bank_number" 
                                   value="<?= htmlspecialchars($currentValues['BANK_ACCOUNT_NUMBER'] ?? '') ?>" 
                                   placeholder="123-456-789012">
                            <div class="form-text">'-' 포함하여 입력</div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 후원 계좌 정보는 후원 페이지에 표시됩니다. 나중에 변경할 수 있습니다.
                    </div>
                </div>
            </div>
            
            <!-- 소셜 미디어 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-share"></i> 소셜 미디어</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="facebook" class="form-label">
                                <i class="bi bi-facebook text-primary"></i> Facebook
                            </label>
                            <input type="url" class="form-control" id="facebook" name="facebook" 
                                   value="<?= htmlspecialchars($currentValues['ORG_FACEBOOK'] ?? '') ?>" 
                                   placeholder="https://facebook.com/yourpage">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="instagram" class="form-label">
                                <i class="bi bi-instagram text-danger"></i> Instagram
                            </label>
                            <input type="url" class="form-control" id="instagram" name="instagram" 
                                   value="<?= htmlspecialchars($currentValues['ORG_INSTAGRAM'] ?? '') ?>" 
                                   placeholder="https://instagram.com/youraccount">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="youtube" class="form-label">
                                <i class="bi bi-youtube text-danger"></i> YouTube
                            </label>
                            <input type="url" class="form-control" id="youtube" name="youtube" 
                                   value="<?= htmlspecialchars($currentValues['ORG_YOUTUBE'] ?? '') ?>" 
                                   placeholder="https://youtube.com/c/yourchannel">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="blog" class="form-label">
                                <i class="bi bi-journal-text text-info"></i> 블로그/홈페이지
                            </label>
                            <input type="url" class="form-control" id="blog" name="blog" 
                                   value="<?= htmlspecialchars($currentValues['ORG_BLOG'] ?? '') ?>" 
                                   placeholder="https://yourblog.com">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 기능 설정 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-toggles"></i> 웹사이트 기능 설정</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">웹사이트에서 사용할 기능들을 선택하세요. 나중에 변경할 수 있습니다.</p>
                    
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="feature_donations" name="feature_donations" 
                                       <?= ($currentValues['FEATURE_DONATIONS'] ?? 'true') === 'true' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="feature_donations">
                                    <i class="bi bi-heart"></i> 후원 시스템
                                </label>
                            </div>
                            <small class="text-muted">후원 페이지와 계좌 정보 표시</small>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="feature_events" name="feature_events" 
                                       <?= ($currentValues['FEATURE_EVENTS'] ?? 'true') === 'true' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="feature_events">
                                    <i class="bi bi-calendar-event"></i> 이벤트 관리
                                </label>
                            </div>
                            <small class="text-muted">행사 및 이벤트 게시</small>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="feature_gallery" name="feature_gallery" 
                                       <?= ($currentValues['FEATURE_GALLERY'] ?? 'true') === 'true' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="feature_gallery">
                                    <i class="bi bi-images"></i> 갤러리
                                </label>
                            </div>
                            <small class="text-muted">사진 및 동영상 갤러리</small>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="feature_newsletter" name="feature_newsletter" 
                                       <?= ($currentValues['FEATURE_NEWSLETTER'] ?? 'true') === 'true' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="feature_newsletter">
                                    <i class="bi bi-envelope"></i> 뉴스레터
                                </label>
                            </div>
                            <small class="text-muted">소식지 및 공지사항</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save"></i> 저장하고 계속
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 은행명 기타 선택 시 직접 입력
    const bankSelect = document.getElementById('bank_name');
    bankSelect.addEventListener('change', function() {
        if (this.value === '기타') {
            const customBank = prompt('은행명을 직접 입력하세요:');
            if (customBank && customBank.trim()) {
                const newOption = new Option(customBank.trim(), customBank.trim(), true, true);
                this.add(newOption);
            } else {
                this.value = '';
            }
        }
    });
    
    // 계좌번호 포맷팅 (하이픈 자동 삽입)
    const accountInput = document.getElementById('bank_number');
    accountInput.addEventListener('input', function() {
        let value = this.value.replace(/[^\d]/g, '');
        
        // 일반적인 계좌번호 형식에 맞춰 하이픈 삽입
        if (value.length > 3) {
            if (value.length <= 6) {
                value = value.replace(/(\d{3})(\d+)/, '$1-$2');
            } else {
                value = value.replace(/(\d{3})(\d{3})(\d+)/, '$1-$2-$3');
            }
        }
        
        this.value = value;
    });
    
    // Bootstrap 유효성 검사
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