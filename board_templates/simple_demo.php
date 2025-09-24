<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>간단한 테마 데모</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <?php
    // 안전한 테마 시스템 로드
    require_once __DIR__ . '/theme_integration_safe.php';
    renderSafeBoardTheme();
    ?>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">🎨 간단한 테마 데모</h1>
        
        <div class="board-surface p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">게시판 스타일 테스트</h2>
            <p class="mb-4">이 영역은 board-surface 클래스가 적용된 영역입니다.</p>
            
            <div class="flex gap-4 mb-4">
                <button class="btn-primary px-4 py-2 rounded">Primary 버튼</button>
                <button class="bg-gray-500 text-white px-4 py-2 rounded">일반 버튼</button>
            </div>
            
            <p><a href="#" class="underline">링크 스타일 테스트</a></p>
        </div>
        
        <div class="bg-white p-4 rounded shadow">
            <h3 class="font-semibold mb-2">적용된 CSS 변수:</h3>
            <ul class="text-sm space-y-1">
                <li><strong>Primary:</strong> <span style="color: var(--theme-primary)">●</span> var(--theme-primary)</li>
                <li><strong>Secondary:</strong> <span style="color: var(--theme-secondary)">●</span> var(--theme-secondary)</li>
                <li><strong>Success:</strong> <span style="color: var(--theme-success)">●</span> var(--theme-success)</li>
                <li><strong>Error:</strong> <span style="color: var(--theme-error)">●</span> var(--theme-error)</li>
            </ul>
        </div>
        
        <div class="mt-4 p-4 bg-blue-50 rounded">
            <p><strong>테스트 성공!</strong> 500 에러 없이 페이지가 로드되었습니다.</p>
            <p>이제 board_list.php 등의 다른 템플릿에서도 안전한 테마 시스템을 사용할 수 있습니다.</p>
        </div>
    </div>
</body>
</html>