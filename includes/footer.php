<!-- Content wrapper ends here -->
    
    <?php 
    // Natural Green 테마 푸터 포함
    $naturalGreenFooter = PROJECT_BASE_PATH . '/theme/natural-green/includes/footer.php';
    if (file_exists($naturalGreenFooter)) {
        include $naturalGreenFooter;
    }
    ?>
    
    <!-- jQuery와 Remodal은 header.php에서 이미 로드됨 -->
    
    <script>
    // Lucide 아이콘 초기화
    try {
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    } catch(e) {
        console.log('Lucide icons initialization skipped');
    }
    </script>
    
    <?php
    // 팝업 엔진 로드 - 홈페이지 감지 로직 개선
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $currentPath = parse_url($requestUri, PHP_URL_PATH) ?? '/';
    
    // 홈페이지 감지 조건 - 메인페이지만 엄격하게 감지
    $isHomePage = (
        // 루트 경로만 허용 (정확한 메인페이지)
        ($currentPath === '/' && (empty($_GET) || (isset($_GET['page']) && $_GET['page'] === 'home'))) ||
        ($currentPath === '/index.php' && (empty($_GET) || (isset($_GET['page']) && $_GET['page'] === 'home'))) ||
        // currentSlug가 명시적으로 home인 경우만
        (isset($currentSlug) && $currentSlug === 'home')
    );
    
    // 디버깅 정보 (개발 환경에서만)
    if (defined('HOPEC_DEBUG') && HOPEC_DEBUG) {
        error_log("Popup Debug - REQUEST_URI: {$requestUri}, SCRIPT_NAME: {$scriptName}, currentPath: {$currentPath}, isHomePage: " . ($isHomePage ? 'true' : 'false'));
    }
    
    if ($isHomePage) {
        try {
            include __DIR__ . '/popup/popup-engine.php';
        } catch (Exception $e) {
            error_log("Popup Engine Load Error: " . $e->getMessage());
        }
    }
    ?>
    
    <!-- 문의하기 팝업 로드 -->
    <?php include __DIR__ . '/../inquiry-popup.php'; ?>
    
    <!-- 문의하기 팝업 JavaScript -->
    <script>
    // Define a base URL for fetch calls
    const BASE_URL = '<?= rtrim(app_url('/'), '/') ?>';
    
    // 라이브러리 로딩 대기 함수
    function waitForLibraries(callback) {
        const maxAttempts = 50; // 최대 5초 대기
        let attempts = 0;

        const checkLibraries = () => {
            attempts++;
            if (
                (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.remodal) ||
                (typeof $ !== 'undefined' && $.fn && $.fn.remodal)
            ) {
                callback();
            } else if (attempts < maxAttempts) {
                setTimeout(checkLibraries, 100);
            } else {
                alert('팝업 라이브러리를 로드할 수 없습니다.\n페이지를 새로고침 후 다시 시도해주세요.');
            }
        };

        checkLibraries();
    }
    
    // 문의하기 모달 열기
    function openInquiryModal() {
        waitForLibraries(() => {
            var inquiryModal = $('[data-remodal-id=inquiry-modal]').remodal();
            inquiryModal.open();
        });
    }
    
    // 메시지 모달 표시
    function showMessage(title, message, isSuccess = true) {
        waitForLibraries(() => {
            // 아이콘 설정
            $('.success-icon, .error-icon').addClass('hidden');
            if (isSuccess) {
                $('.success-icon').removeClass('hidden');
            } else {
                $('.error-icon').removeClass('hidden');
            }
            
            // 메시지 설정
            $('#messageTitle').text(title);
            const $messageText = $('#messageText');
            $messageText.text(message);
            $messageText.css('white-space', 'pre-line'); // preserve newlines safely
            
            // 모달 표시
            var messageModal = $('[data-remodal-id=message-modal]').remodal();
            messageModal.open();
        });
    }
    
    // 문의하기 폼 제출
    function submitInquiry(event) {
        event.preventDefault();
        
        const form = document.getElementById('inquiryForm');
        const submitBtn = document.getElementById('submitBtn');
        
        // 로딩 상태 시작
        submitBtn.disabled = true;
        submitBtn.classList.add('loading');
        
        // 먼저 최신 CSRF 토큰을 가져옵니다
        fetch(`${BASE_URL}/get-csrf-token.php`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(tokenResult => {
            if (!tokenResult.success) {
                throw new Error('CSRF 토큰을 가져올 수 없습니다.');
            }
            
            const formData = new FormData(form);
            
            // 폼 데이터를 JSON으로 변환
            const data = {};
            for (let [key, value] of formData.entries()) {
                if (key === 'privacy_agree') {
                    data[key] = value === 'on';
                } else {
                    data[key] = value;
                }
            }
            
            // 최신 CSRF 토큰 사용
            data.csrf_token = tokenResult.csrf_token;
            
            // 폼의 토큰 필드도 업데이트
            const csrfTokenField = document.getElementById('csrf_token_field');
            if (csrfTokenField) {
                csrfTokenField.value = tokenResult.csrf_token;
            }
            
            // AJAX 요청
            return fetch(`${BASE_URL}/submit-inquiry.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('네트워크 오류가 발생했습니다.');
            }
            return response.json();
        })
        .then(result => {
            if (result.success) {
                // 새로운 CSRF 토큰 업데이트
                const csrfTokenField = document.getElementById('csrf_token_field');
                if (result.new_csrf_token && csrfTokenField) {
                    csrfTokenField.value = result.new_csrf_token;
                }
                
                // 성공 시 문의하기 모달 닫기
                waitForLibraries(() => {
                    var inquiryModal = $('[data-remodal-id=inquiry-modal]').remodal();
                    inquiryModal.close();
                    
                    // 폼 초기화
                    form.reset();
                    
                    // 성공 메시지 표시
                    setTimeout(() => {
                        showMessage('문의 접수 완료', result.message, true);
                    }, 300);
                });
                
            } else {
                // 실패 메시지 표시
                showMessage('문의 접수 실패', result.message, false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('오류', '문의 접수 중 오류가 발생했습니다.\n' + error.message + '\n페이지를 새로고침 후 다시 시도해주세요.', false);
        })
        .finally(() => {
            // 로딩 상태 종료
            submitBtn.disabled = false;
            submitBtn.classList.remove('loading');
        });
    }
    
    // 문자 수 제한 표시 (선택사항)
    document.addEventListener('DOMContentLoaded', function() {
        const messageTextarea = document.getElementById('inquiry_message');
        if (messageTextarea) {
            // 실시간 문자 수 카운터 (선택사항)
            messageTextarea.addEventListener('input', function() {
                const currentLength = this.value.length;
                const maxLength = 2000;
                
                if (currentLength > maxLength - 100) {
                    console.log(`${currentLength}/${maxLength} 문자`);
                }
            });
        }
        
        // 폼 유효성 검사 실시간 피드백
        const form = document.getElementById('inquiryForm');
        if (form) {
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                field.addEventListener('blur', function() {
                    if (this.value.trim() === '') {
                        this.style.borderColor = '#ef4444';
                    } else {
                        this.style.borderColor = '#d1d5db';
                    }
                });
                
                field.addEventListener('input', function() {
                    if (this.value.trim() !== '') {
                        this.style.borderColor = '#d1d5db';
                    }
                });
            });
        }
    });
    </script>
    
</body>
</html><?php