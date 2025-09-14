<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚨 강력한 캐시 무효화 테스트</title>
    
    <!-- 🔥 극강의 캐시 무효화 -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="-1">
    <meta http-equiv="Last-Modified" content="<?= gmdate('D, d M Y H:i:s T') ?>">
    <meta name="cache-buster" content="<?= uniqid('cb_', true) ?>">
    
    <!-- CSS 로딩 순서 테스트 -->
    <?php
    $timestamp = time();
    $unique = uniqid();
    ?>
    
    <link rel="stylesheet" href="theme/natural-green/styles/globals.css?force=<?= $timestamp ?>&id=<?= $unique ?>&v=<?= rand() ?>">
    <link rel="stylesheet" href="css/theme/theme.css?force=<?= $timestamp ?>&id=<?= $unique ?>&v=<?= rand() ?>&priority=high">
    
    <style>
        body { 
            margin: 20px; 
            font-family: 'Noto Sans KR', sans-serif; 
            background: white;
        }
        
        .test-box { 
            margin: 15px 0; 
            padding: 20px; 
            border: 2px solid #ddd; 
            border-radius: 8px; 
        }
        
        .success-test { 
            background: #e8f5e8; 
            border-color: #4caf50; 
        }
        
        .error-test { 
            background: #ffe8e8; 
            border-color: #f44336; 
        }
        
        /* 🎯 실제 메뉴 스타일 재현 */
        .menu-simulation {
            display: inline-block;
            padding: 10px 15px;
            margin: 10px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            transition: all 0.3s ease;
            cursor: pointer;
            font-weight: 500;
        }
        
        .menu-simulation:hover {
            color: var(--lime-600) !important;
            border-color: var(--lime-600);
            background: rgba(var(--lime-600-rgb), 0.1);
        }
        
        .color-display {
            display: inline-block;
            width: 50px;
            height: 50px;
            border: 2px solid #333;
            margin: 10px;
            border-radius: 5px;
        }
        
        .variable-info {
            font-family: monospace;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h1>🚨 Admin 색상 #2F2352 강력 캐시 무효화 테스트</h1>
    
    <div class="test-box">
        <h2>📊 현재 상태 확인</h2>
        <p><strong>페이지 로드 시간:</strong> <?= date('Y-m-d H:i:s') ?></p>
        <p><strong>캐시 버스터:</strong> <?= $timestamp ?></p>
        <p><strong>고유 ID:</strong> <?= $unique ?></p>
        <p><strong>CSS 파일 수정시간:</strong> 
        <?php 
        $cssPath = 'css/theme/theme.css';
        echo file_exists($cssPath) ? date('H:i:s', filemtime($cssPath)) : '파일 없음';
        ?>
        </p>
    </div>
    
    <div class="test-box" id="color-test">
        <h2>🎨 --lime-600 색상 실시간 확인</h2>
        <div class="variable-info">
            <p><strong>현재 --lime-600 값:</strong> <span id="lime-600-current">확인 중...</span></p>
            <p><strong>Admin 설정값:</strong> <span style="background: #2F2352; color: white; padding: 5px 10px; border-radius: 3px;">#2F2352 (어두운 보라)</span></p>
            <p><strong>이전 기본값:</strong> <span style="background: #65a30d; color: white; padding: 5px 10px; border-radius: 3px;">#65a30d (녹색)</span></p>
        </div>
        <div class="color-display" id="lime-600-display" style="background-color: var(--lime-600);"></div>
    </div>
    
    <div class="test-box">
        <h2>🖱️ 실제 메뉴 호버 테스트 (마우스를 올려보세요!)</h2>
        <div class="menu-simulation" style="color: #666;">
            희망씨 소개 (호버시 #2F2352 색상)
        </div>
        <div class="menu-simulation" style="color: #666;">
            희망씨 사업 (호버시 어두운 보라색)
        </div>
        <div class="menu-simulation" style="color: #666;">
            희망씨 후원안내 (호버 테스트)
        </div>
    </div>
    
    <div class="test-box" id="result-box">
        <h2 id="result-title">⏳ 테스트 진행 중...</h2>
        <div id="result-content">
            <p>JavaScript로 실시간 색상값을 확인하고 있습니다...</p>
        </div>
    </div>
    
    <div class="test-box">
        <h2>🔧 문제 해결 단계</h2>
        <ol>
            <li><strong>하드 리프레시:</strong> Cmd+Shift+R (Mac) / Ctrl+Shift+R (Windows)</li>
            <li><strong>시크릿 모드:</strong> 새 시크릿 창에서 테스트</li>
            <li><strong>개발자 도구:</strong> F12 → Application → Storage → Clear storage</li>
            <li><strong>완전 캐시 삭제:</strong> 브라우저 설정에서 모든 캐시 삭제</li>
        </ol>
    </div>

    <script>
        function checkColorValues() {
            const root = document.documentElement;
            const limeValue = getComputedStyle(root).getPropertyValue('--lime-600').trim();
            
            // 색상값 표시 업데이트
            const currentSpan = document.getElementById('lime-600-current');
            const displayDiv = document.getElementById('lime-600-display');
            const resultBox = document.getElementById('result-box');
            const resultTitle = document.getElementById('result-title');
            const resultContent = document.getElementById('result-content');
            
            if (currentSpan) {
                currentSpan.textContent = limeValue || '값 없음';
                currentSpan.style.background = limeValue;
                currentSpan.style.color = 'white';
                currentSpan.style.padding = '5px 10px';
                currentSpan.style.borderRadius = '3px';
            }
            
            if (displayDiv) {
                displayDiv.style.backgroundColor = limeValue;
            }
            
            // 결과 판정
            const targetColor = '#2f2352'; // Admin 설정값
            const oldColor = '#65a30d'; // 이전 기본값
            
            if (limeValue.toLowerCase().replace(/\s/g, '') === targetColor || 
                limeValue.includes('47, 35, 82') || 
                limeValue.toLowerCase().includes('2f2352')) {
                
                // 성공!
                resultBox.className = 'test-box success-test';
                resultTitle.innerHTML = '✅ 성공! Admin 색상이 적용되었습니다!';
                resultContent.innerHTML = `
                    <p><strong>--lime-600</strong> = <strong>${limeValue}</strong></p>
                    <p>🎉 메뉴 호버시 어두운 보라색(#2F2352)이 표시됩니다!</p>
                    <p>이제 웹사이트의 상단 메뉴에 마우스를 올려보세요.</p>
                `;
                
            } else if (limeValue.toLowerCase().includes('65a30d') || 
                       limeValue.includes('101, 163, 13')) {
                
                // 여전히 기존 색상
                resultBox.className = 'test-box error-test';
                resultTitle.innerHTML = '❌ 아직 기존 색상이 표시됩니다';
                resultContent.innerHTML = `
                    <p><strong>현재값:</strong> ${limeValue} (녹색)</p>
                    <p><strong>원하는값:</strong> #2F2352 (어두운 보라)</p>
                    <p>🔄 더 강력한 캐시 무효화가 필요합니다:</p>
                    <ul>
                        <li>시크릿 모드에서 테스트</li>
                        <li>다른 브라우저에서 테스트</li>
                        <li>브라우저 완전 재시작</li>
                    </ul>
                `;
                
            } else {
                // 다른 색상
                resultBox.className = 'test-box';
                resultTitle.innerHTML = '🤔 예상과 다른 색상이 감지되었습니다';
                resultContent.innerHTML = `
                    <p><strong>감지된 색상:</strong> ${limeValue}</p>
                    <p>CSS 변수가 다른 값으로 설정되어 있을 수 있습니다.</p>
                `;
            }
        }
        
        // 페이지 로드 후 즉시 실행
        document.addEventListener('DOMContentLoaded', checkColorValues);
        
        // 3초 후 한번 더 체크 (CSS 로딩 완료 후)
        setTimeout(checkColorValues, 3000);
        
        // 페이지 포커스시 체크
        window.addEventListener('focus', checkColorValues);
        
        // 5초마다 지속적으로 체크
        setInterval(checkColorValues, 5000);
    </script>
    
    <div class="test-box">
        <h2>🌐 브라우저별 테스트 링크</h2>
        <p>다른 브라우저나 시크릿 모드에서도 테스트해보세요:</p>
        <ul>
            <li><strong>Chrome:</strong> Ctrl+Shift+N (시크릿)</li>
            <li><strong>Safari:</strong> Cmd+Shift+N (프라이빗)</li>
            <li><strong>Firefox:</strong> Ctrl+Shift+P (프라이빗)</li>
        </ul>
    </div>
    
</body>
</html>