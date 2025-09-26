<?php
/**
 * 문의하기 팝업 모달
 * Footer에서 호출되는 문의하기 팝업입니다.
 */

// 세션 시작 (간단하게)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 간단한 CSRF 토큰 생성
if (!isset($_SESSION['inquiry_csrf_token']) || !isset($_SESSION['inquiry_csrf_time']) || 
    (time() - $_SESSION['inquiry_csrf_time']) > 3600) {
    $_SESSION['inquiry_csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['inquiry_csrf_time'] = time();
}

$csrf_token = $_SESSION['inquiry_csrf_token'];

// 카테고리 목록을 위한 데이터베이스 연결
$pdo = null;
$categories = [];

try {
    // 관리자 시스템과 동일한 데이터베이스 연결 방식 사용
    if (file_exists(__DIR__ . '/admin/env_loader.php')) {
        require_once __DIR__ . '/admin/env_loader.php';
        
        // .env 파일 로드
        loadEnv();
        
        // 데이터베이스 연결 정보
        $host = env('DB_HOST', 'localhost');
        $dbname = env('DB_DATABASE', '');
        $username = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', '');
        $charset = env('DB_CHARSET', 'utf8mb4');
        
        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 카테고리 목록 가져오기 (ID 순으로 정렬)
        $stmt = $pdo->query("SELECT id, name FROM " . get_table_name('inquiry_categories') . " WHERE is_active = 1 ORDER BY id");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } else {
        // 직접 연결 시도 (fallback) - 환경변수 기본값 사용
        $host = 'localhost';
        $dbname = env('DB_DATABASE', '');
        $username = 'root';
        $password = '';
        $charset = 'utf8mb4';
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 카테고리 목록 가져오기 (ID 순으로 정렬)
        $stmt = $pdo->query("SELECT id, name FROM " . get_table_name('inquiry_categories') . " WHERE is_active = 1 ORDER BY id");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    // 오류 발생 시 기본 카테고리 설정
    error_log("Inquiry popup database error: " . $e->getMessage());
    $categories = [
        ['id' => 1, 'name' => '일반문의'],
        ['id' => 2, 'name' => '기술지원'],
        ['id' => 3, 'name' => '제휴문의'],
        ['id' => 4, 'name' => '후원문의'],
        ['id' => 5, 'name' => '자원봉사'],
        ['id' => 6, 'name' => '행사문의'],
        ['id' => 7, 'name' => '기타']
    ];
}
?>

<!-- 문의하기 팝업 모달 -->
<div class="remodal younglabor-inquiry-popup" 
     data-remodal-id="inquiry-modal"
     data-remodal-options="hashTracking: false, closeOnOutsideClick: true">
     
    <!-- 팝업 헤더 -->
    <div class="younglabor-inquiry-header">
        <h3 class="younglabor-inquiry-title">문의하기</h3>
        <button data-remodal-action="close" class="remodal-close younglabor-inquiry-close">
            <i data-lucide="x"></i>
        </button>
    </div>
    
    <!-- 팝업 내용 -->
    <div class="younglabor-inquiry-content">
        <form id="inquiryForm" onsubmit="submitInquiry(event)">
            <!-- CSRF 토큰 -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>" id="csrf_token_field">
            
            <!-- 이름과 문의유형을 같은 행에 배치 -->
            <div class="form-row mb-4">
                <div class="form-col-6">
                    <label for="inquiry_name" class="form-label">이름 <span class="text-red-500">*</span></label>
                    <input type="text" 
                           id="inquiry_name" 
                           name="name" 
                           class="form-control" 
                           required 
                           maxlength="100"
                           placeholder="이름을 입력해주세요">
                </div>
                <div class="form-col-6">
                    <label for="inquiry_category" class="form-label">문의 유형 <span class="text-red-500">*</span></label>
                    <select id="inquiry_category" name="category_id" class="form-control" required>
                        <option value="">선택해주세요</option>
                        <?php 
                        if (!empty($categories)) {
                            foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                            <?php endforeach;
                        } else {
                            // 기본 카테고리 직접 출력 (fallback)
                            $defaultCategories = [
                                ['id' => 1, 'name' => '일반문의'],
                                ['id' => 2, 'name' => '기술지원'],
                                ['id' => 3, 'name' => '제휴문의'],
                                ['id' => 4, 'name' => '후원문의'],
                                ['id' => 5, 'name' => '자원봉사'],
                                ['id' => 6, 'name' => '행사문의'],
                                ['id' => 7, 'name' => '기타']
                            ];
                            foreach ($defaultCategories as $category): ?>
                                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                            <?php endforeach;
                        } ?>
                    </select>
                </div>
            </div>
            
            <!-- 이메일 입력 -->
            <div class="form-group mb-4">
                <label for="inquiry_email" class="form-label">이메일 <span class="text-red-500">*</span></label>
                <input type="email" 
                       id="inquiry_email" 
                       name="email" 
                       class="form-control" 
                       required 
                       maxlength="255"
                       placeholder="example@email.com">
            </div>
            
            <!-- 연락처 입력 (선택사항) -->
            <div class="form-group mb-4">
                <label for="inquiry_phone" class="form-label">연락처</label>
                <input type="tel" 
                       id="inquiry_phone" 
                       name="phone" 
                       class="form-control" 
                       maxlength="20"
                       placeholder="연락처를 입력해주세요 (선택사항)">
            </div>
            
            <!-- 제목 입력 (선택사항) -->
            <div class="form-group mb-4">
                <label for="inquiry_subject" class="form-label">제목</label>
                <input type="text" 
                       id="inquiry_subject" 
                       name="subject" 
                       class="form-control" 
                       maxlength="200"
                       placeholder="문의 제목을 입력해주세요 (선택사항)">
            </div>
            
            <!-- 문의 내용 -->
            <div class="form-group mb-4">
                <label for="inquiry_message" class="form-label">문의 내용 <span class="text-red-500">*</span></label>
                <textarea id="inquiry_message" 
                          name="message" 
                          class="form-control" 
                          rows="6" 
                          required 
                          maxlength="2000"
                          placeholder="문의하실 내용을 자세히 입력해주세요"></textarea>
                <small class="text-gray-500 text-sm">최대 2000자까지 입력 가능합니다.</small>
            </div>
            
            <!-- 개인정보 동의 (강조된 스타일) -->
            <div class="form-group mb-4 privacy-agreement">
                <div class="privacy-box">
                    <label class="checkbox-label">
                        <input type="checkbox" 
                               id="privacy_agree" 
                               name="privacy_agree" 
                               required 
                               class="privacy-checkbox">
                        <span class="privacy-text">
                            <strong>개인정보 수집 및 이용에 동의합니다.</strong> 
                            <span class="required-mark">*</span>
                        </span>
                    </label>
                    <div class="privacy-details">
                        수집된 개인정보는 문의 응답 목적으로만 사용되며, 답변 완료 후 안전하게 삭제됩니다.
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <!-- 팝업 액션 버튼 -->
    <div class="younglabor-inquiry-actions">
        <div class="flex gap-3 justify-end">
            <button type="button" 
                    data-remodal-action="close" 
                    class="btn btn-secondary">
                취소
            </button>
            <button type="submit" 
                    form="inquiryForm" 
                    class="btn btn-primary" 
                    id="submitBtn">
                <span class="btn-text">문의하기</span>
                <span class="loading-spinner hidden">
                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                    전송 중...
                </span>
            </button>
        </div>
    </div>
</div>

<!-- 성공/실패 메시지 모달 -->
<div class="remodal younglabor-message-popup" 
     data-remodal-id="message-modal"
     data-remodal-options="hashTracking: false, closeOnOutsideClick: true">
    <div class="younglabor-message-content">
        <div class="text-center py-6">
            <div class="message-icon mb-4">
                <i data-lucide="check-circle" class="w-16 h-16 text-green-500 mx-auto hidden success-icon"></i>
                <i data-lucide="x-circle" class="w-16 h-16 text-red-500 mx-auto hidden error-icon"></i>
            </div>
            <h3 class="text-xl font-semibold mb-2" id="messageTitle">알림</h3>
            <p class="text-gray-600" id="messageText">메시지 내용</p>
        </div>
        <div class="flex justify-center">
            <button data-remodal-action="close" class="btn btn-primary">
                확인
            </button>
        </div>
    </div>
</div>

<!-- 문의하기 팝업 스타일 -->
<style>
.younglabor-inquiry-popup {
    max-width: 600px;
    width: 90%;
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    padding: 0;
    overflow: hidden;
    font-family: "Noto Sans KR", Arial, sans-serif;
}

.younglabor-inquiry-header {
    background: linear-gradient(135deg, #84cc16, #22c55e);
    color: white;
    padding: 20px 30px;
    position: relative;
}

.younglabor-inquiry-title {
    margin: 0;
    font-size: 1.3em;
    font-weight: 600;
    padding-right: 40px;
}

.younglabor-inquiry-close {
    position: absolute;
    top: 15px;
    right: 20px;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.2s;
}

.younglabor-inquiry-close:hover {
    background: rgba(255, 255, 255, 0.3);
}

.younglabor-inquiry-content {
    padding: 25px 30px;
    max-height: 72vh;
    overflow-y: auto;
}

.form-group {
    margin-bottom: 0.75rem;
}

/* 2열 레이아웃 */
.form-row {
    display: flex;
    gap: 1rem;
    margin-bottom: 0.75rem;
}

.form-col-6 {
    flex: 1;
    min-width: 0; /* 텍스트 오버플로 방지 */
}

.form-label {
    display: block;
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: #374151;
    font-size: 0.875rem;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    transition: border-color 0.2s, box-shadow 0.2s;
    background-color: #ffffff;
}

.form-control:focus {
    outline: none;
    border-color: #84cc16;
    box-shadow: 0 0 0 3px rgba(132, 204, 22, 0.1);
}

.form-control::placeholder {
    color: #9ca3af;
}

textarea.form-control {
    resize: vertical;
    min-height: 120px;
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    cursor: pointer;
    line-height: 1.5;
}

.checkbox-label input[type="checkbox"] {
    width: 16px;
    height: 16px;
    accent-color: #84cc16;
    cursor: pointer;
    flex-shrink: 0;
}

/* 개인정보 동의 섹션 강조 스타일 */
.privacy-agreement {
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 0;
    margin-bottom: 1rem !important;
}

.privacy-box {
    background-color: #f8fafc;
    padding: 1rem;
    border-radius: 6px;
}

.privacy-checkbox {
    width: 18px;
    height: 18px;
    accent-color: #84cc16;
    cursor: pointer;
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.privacy-text {
    font-size: 0.875rem;
    color: #374151;
    line-height: 1.5;
}

.privacy-details {
    font-size: 0.8rem;
    color: #6b7280;
    margin-top: 0.5rem;
    padding-left: 2.25rem; /* 체크박스 너비만큼 들여쓰기 */
    line-height: 1.4;
}

.required-mark {
    color: #ef4444;
    font-weight: bold;
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    cursor: pointer;
    margin: 0;
}

.younglabor-inquiry-actions {
    padding: 20px 30px;
    border-top: 1px solid #e5e7eb;
    background-color: #f9fafb;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
}

.btn-primary {
    background-color: #84cc16;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background-color: #65a30d;
    transform: translateY(-1px);
}

.btn-primary:disabled {
    background-color: #9ca3af;
    cursor: not-allowed;
    transform: none;
}

.btn-secondary {
    background-color: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background-color: #4b5563;
}

.text-red-500 {
    color: #ef4444;
}

.text-gray-500 {
    color: #6b7280;
}

.text-gray-600 {
    color: #4b5563;
}

.loading-spinner {
    display: none;
}

.loading .btn-text {
    display: none;
}

.loading .loading-spinner {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

/* 메시지 모달 스타일 */
.younglabor-message-popup {
    max-width: 400px;
    width: 90%;
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    padding: 0;
    overflow: hidden;
}

.younglabor-message-content {
    padding: 20px;
}

/* 모바일 최적화 */
@media (max-width: 768px) {
    .younglabor-inquiry-popup {
        margin: 20px;
        max-width: calc(100vw - 40px);
    }
    
    .younglabor-inquiry-content {
        padding: 20px;
        max-height: 65vh;
    }
    
    .younglabor-inquiry-actions {
        padding: 15px 20px;
    }
    
    .btn {
        padding: 0.625rem 1.25rem;
        font-size: 0.8rem;
    }
}

/* 애니메이션 */
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* 유틸리티 클래스 */
.hidden {
    display: none !important;
}

.flex {
    display: flex;
}

.items-center {
    align-items: center;
}

.items-start {
    align-items: flex-start;
}

.justify-center {
    justify-content: center;
}

.justify-end {
    justify-content: flex-end;
}

.gap-2 {
    gap: 0.5rem;
}

.gap-3 {
    gap: 0.75rem;
}

.mb-2 {
    margin-bottom: 0.5rem;
}

.mb-4 {
    margin-bottom: 1rem;
}

.mt-1 {
    margin-top: 0.25rem;
}

.text-center {
    text-align: center;
}

.text-sm {
    font-size: 0.875rem;
}

.text-xl {
    font-size: 1.25rem;
}

.font-semibold {
    font-weight: 600;
}

.py-6 {
    padding-top: 1.5rem;
    padding-bottom: 1.5rem;
}

.mx-auto {
    margin-left: auto;
    margin-right: auto;
}

.w-4 {
    width: 1rem;
}

.h-4 {
    height: 1rem;
}

.w-16 {
    width: 4rem;
}

.h-16 {
    height: 4rem;
}

.text-green-500 {
    color: #22c55e;
}

/* Remodal 오버라이드 - 더 강력한 스타일 적용 */
.remodal-overlay.remodal-is-opened {
    background-color: rgba(0, 0, 0, 0.5) !important;
}

.remodal.younglabor-inquiry-popup.remodal-is-opened,
.remodal.younglabor-message-popup.remodal-is-opened {
    opacity: 1 !important;
    visibility: visible !important;
    transform: scale(1) !important;
    transition: opacity 0.3s ease-out, transform 0.3s ease-out !important;
}

/* 반응형 디자인 개선 */
@media (max-width: 480px) {
    .younglabor-inquiry-popup {
        margin: 10px;
        max-width: calc(100vw - 20px);
        max-height: calc(100vh - 20px);
    }
    
    .younglabor-inquiry-content {
        padding: 15px;
        max-height: calc(62vh - 40px);
    }
    
    .form-control {
        padding: 0.65rem;
        font-size: 16px; /* iOS 줌 방지 */
    }
    
    /* 모바일에서는 세로 배치 */
    .form-row {
        flex-direction: column;
        gap: 0;
    }
    
    .form-col-6 {
        margin-bottom: 1rem;
    }
    
    .privacy-details {
        padding-left: 1.5rem; /* 모바일에서 들여쓰기 축소 */
        font-size: 0.75rem;
    }
}

@media (max-width: 768px) and (min-width: 481px) {
    /* 태블릿에서는 2열 유지하되 간격 조정 */
    .form-row {
        gap: 0.75rem;
    }
}
</style>