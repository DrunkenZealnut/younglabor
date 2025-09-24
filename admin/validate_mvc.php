<?php
/**
 * MVC 시스템 검증 스크립트
 * 데이터베이스 연결 없이 주요 컴포넌트들을 테스트
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>MVC 시스템 검증 결과</h2>\n";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    .component { margin: 10px 0; padding: 10px; border: 1px solid #ddd; }
</style>\n";

$results = [];

// 1. 핵심 파일들 존재 확인
echo "<div class='component'><h3>1. 핵심 파일 존재 확인</h3>\n";
$core_files = [
    'mvc/core/Container.php' => 'DI 컨테이너',
    'mvc/services/PerformanceService.php' => '성능 모니터링',
    'mvc/services/CacheService.php' => '캐싱 서비스',
    'mvc/services/FileService.php' => '파일 서비스',
    'mvc/views/View.php' => '뷰 시스템',
    'mvc/bootstrap.php' => 'MVC 부트스트랩'
];

foreach ($core_files as $file => $desc) {
    if (file_exists($file)) {
        echo "<span class='success'>✅ {$desc}: {$file}</span><br>\n";
        $results['files'][] = true;
    } else {
        echo "<span class='error'>❌ {$desc}: {$file} - 파일이 없습니다</span><br>\n";
        $results['files'][] = false;
    }
}
echo "</div>\n";

// 2. PHP 문법 검사
echo "<div class='component'><h3>2. PHP 문법 검사</h3>\n";
foreach ($core_files as $file => $desc) {
    if (file_exists($file)) {
        $output = shell_exec("php -l $file 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "<span class='success'>✅ {$desc}: 문법 정상</span><br>\n";
            $results['syntax'][] = true;
        } else {
            echo "<span class='error'>❌ {$desc}: 문법 오류 - {$output}</span><br>\n";
            $results['syntax'][] = false;
        }
    }
}
echo "</div>\n";

// 3. 디렉토리 권한 확인
echo "<div class='component'><h3>3. 디렉토리 권한 확인</h3>\n";
$directories = [
    'mvc/cache' => '캐시 디렉토리',
    'mvc/logs' => '로그 디렉토리'
];

foreach ($directories as $dir => $desc) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "<span class='success'>✅ {$desc}: 쓰기 권한 정상</span><br>\n";
            $results['permissions'][] = true;
        } else {
            echo "<span class='error'>❌ {$desc}: 쓰기 권한 없음</span><br>\n";
            $results['permissions'][] = false;
        }
    } else {
        echo "<span class='error'>❌ {$desc}: 디렉토리가 없습니다</span><br>\n";
        $results['permissions'][] = false;
    }
}
echo "</div>\n";

// 4. 클래스 로딩 테스트
echo "<div class='component'><h3>4. 클래스 로딩 테스트</h3>\n";
try {
    require_once 'mvc/core/Container.php';
    echo "<span class='success'>✅ Container 클래스 로딩 성공</span><br>\n";
    $results['loading'][] = true;
    
    $container = Container::getInstance();
    echo "<span class='success'>✅ Container 인스턴스 생성 성공</span><br>\n";
    $results['loading'][] = true;
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Container 로딩 실패: {$e->getMessage()}</span><br>\n";
    $results['loading'][] = false;
}

try {
    require_once 'mvc/services/CacheService.php';
    echo "<span class='success'>✅ CacheService 클래스 로딩 성공</span><br>\n";
    $results['loading'][] = true;
    
} catch (Exception $e) {
    echo "<span class='error'>❌ CacheService 로딩 실패: {$e->getMessage()}</span><br>\n";
    $results['loading'][] = false;
}

try {
    require_once 'mvc/views/View.php';
    echo "<span class='success'>✅ View 클래스 로딩 성공</span><br>\n";
    $results['loading'][] = true;
    
} catch (Exception $e) {
    echo "<span class='error'>❌ View 로딩 실패: {$e->getMessage()}</span><br>\n";
    $results['loading'][] = false;
}
echo "</div>\n";

// 5. 기본 기능 테스트
echo "<div class='component'><h3>5. 기본 기능 테스트</h3>\n";

// Cache Service 테스트
try {
    $cacheConfig = [
        'enabled' => true,
        'path' => 'mvc/cache/',
        'default_lifetime' => 3600
    ];
    $cacheService = new CacheService($cacheConfig);
    
    // 캐시 쓰기/읽기 테스트
    $testKey = 'test_' . time();
    $testValue = ['message' => 'MVC 시스템 테스트', 'timestamp' => time()];
    
    $cacheService->put($testKey, $testValue, 60);
    $retrieved = $cacheService->get($testKey);
    
    if ($retrieved && $retrieved['message'] === $testValue['message']) {
        echo "<span class='success'>✅ 캐시 시스템: 쓰기/읽기 정상</span><br>\n";
        $results['functions'][] = true;
    } else {
        echo "<span class='error'>❌ 캐시 시스템: 쓰기/읽기 실패</span><br>\n";
        $results['functions'][] = false;
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ 캐시 시스템 테스트 실패: {$e->getMessage()}</span><br>\n";
    $results['functions'][] = false;
}

// View 시스템 테스트
try {
    $view = new View();
    $view->set('test_message', 'MVC 뷰 시스템 작동 중');
    
    echo "<span class='success'>✅ View 시스템: 인스턴스 생성 및 데이터 설정 정상</span><br>\n";
    $results['functions'][] = true;
    
} catch (Exception $e) {
    echo "<span class='error'>❌ View 시스템 테스트 실패: {$e->getMessage()}</span><br>\n";
    $results['functions'][] = false;
}
echo "</div>\n";

// 6. 전체 결과 요약
echo "<div class='component'><h3>6. 전체 결과 요약</h3>\n";
$total_tests = 0;
$passed_tests = 0;

foreach ($results as $category => $tests) {
    $total_tests += count($tests);
    $passed_tests += array_sum($tests);
}

$success_rate = $total_tests > 0 ? round(($passed_tests / $total_tests) * 100, 2) : 0;

if ($success_rate >= 90) {
    echo "<span class='success'>🎉 MVC 시스템 상태: 우수 ({$success_rate}%)</span><br>\n";
    echo "<span class='info'>전체 {$total_tests}개 테스트 중 {$passed_tests}개 통과</span><br>\n";
    echo "<span class='info'>✅ MVC 시스템이 정상적으로 구성되어 있습니다.</span><br>\n";
} else if ($success_rate >= 70) {
    echo "<span class='info'>⚠️ MVC 시스템 상태: 양호 ({$success_rate}%)</span><br>\n";
    echo "<span class='info'>전체 {$total_tests}개 테스트 중 {$passed_tests}개 통과</span><br>\n";
    echo "<span class='info'>일부 문제가 있지만 기본 기능은 작동합니다.</span><br>\n";
} else {
    echo "<span class='error'>🚨 MVC 시스템 상태: 주의 필요 ({$success_rate}%)</span><br>\n";
    echo "<span class='error'>전체 {$total_tests}개 테스트 중 {$passed_tests}개만 통과</span><br>\n";
    echo "<span class='error'>시스템 점검이 필요합니다.</span><br>\n";
}

echo "<br><span class='info'>📝 테스트 완료 시간: " . date('Y-m-d H:i:s') . "</span><br>\n";
echo "</div>\n";
?>