    </div></div></div><!-- #wrapper 닫기 태그들 -->
    
    <?php 
    // Natural Green 테마 푸터 포함
    $naturalGreenFooter = HOPEC_BASE_PATH . '/theme/natural-green/includes/footer.php';
    if (file_exists($naturalGreenFooter)) {
        include $naturalGreenFooter;
    }
    ?>
    
    <!-- jQuery 로드 (필요한 경우만) -->
    <script>
    if (typeof jQuery === 'undefined') {
        document.write('<script src="https://code.jquery.com/jquery-3.6.0.min.js"><\/script>');
    }
    </script>
    
    <!-- Remodal 라이브러리는 header.php에서 이미 로드됨 -->
    
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
    
    // 홈페이지 감지 조건을 확장
    $isHomePage = (
        // 기본 홈페이지 경로들
        $currentPath === '/' || 
        $currentPath === '/index.php' || 
        $requestUri === '/' || 
        $requestUri === '/index.php' ||
        // currentSlug 기반 감지
        (!isset($currentSlug) || $currentSlug === 'home' || empty($currentSlug)) ||
        // 스크립트 이름 기반 감지 (index.php로 끝나는 경우)
        (basename($scriptName) === 'index.php' && dirname($scriptName) === '') ||
        // 홈페이지 관련 파라미터가 없는 경우
        (empty($_GET) && $currentPath === '/')
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
    
</body>
</html><?php