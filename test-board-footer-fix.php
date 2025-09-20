<?php
/**
 * Test script to verify board footer overlap fix
 * This script simulates the fixed board.php structure
 */

// Set up basic constants
if (!defined('HOPEC_BASE_PATH')) {
    define('HOPEC_BASE_PATH', __DIR__);
}

// Mock board data
$board = [
    'board_name' => '테스트 게시판',
    'board_description' => 'HTML 구조 수정 후 Footer 겹침 테스트'
];
$board_id = 999;

// Set up basic variables for testing
$config = [
    'board_title' => $board['board_name'],
    'board_description' => $board['board_description'],
    'enable_search' => true,
    'show_write_button' => true,
    'detail_url' => 'detail.php',
    'write_url' => 'write.php',
    'board_skin' => 'basic'
];

// Mock posts data
$posts = [
    [
        'post_id' => 1,
        'title' => '수정된 HTML 구조 테스트',
        'author_name' => '관리자',
        'created_at' => date('Y-m-d H:i:s'),
        'view_count' => 10,
        'attachment_count' => 0,
        'is_notice' => false
    ],
    [
        'post_id' => 2,
        'title' => 'main 태그가 제대로 flex 작동하는지 확인',
        'author_name' => '개발자',
        'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        'view_count' => 5,
        'attachment_count' => 1,
        'is_notice' => false
    ]
];

$total_posts = count($posts);
$total_pages = 1;
$current_page = 1;
$search_type = 'all';
$search_keyword = '';
$board_skin = 'basic';

// Include header
if (file_exists(__DIR__ . '/includes/header.php')) {
    include __DIR__ . '/includes/header.php';
} else {
    // Simple header for testing with fixed structure
    echo '<!DOCTYPE html>
    <html lang="ko">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>게시판 Footer 수정 테스트</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="/hopec/theme/natural-green/styles/globals.css" rel="stylesheet">
        <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    </head>
    <body class="min-vh-100 d-flex flex-column">
    <header class="bg-white shadow-sm">
        <div class="container py-3">
            <h1 class="h4 mb-0">테스트 헤더</h1>
        </div>
    </header>
    <div id="wrapper"><div id="container_wr"><div id="container">';
}

// Main tag with flex class (like the fixed board.php)
echo '<main id="main" role="main" class="flex-1">' . "\n";

// Include the board template
include __DIR__ . '/board_templates/board_list.php';

echo '</main>' . "\n";

// Include footer
if (file_exists(__DIR__ . '/includes/footer.php')) {
    include __DIR__ . '/includes/footer.php';
} else {
    // Simple footer for testing
    echo '
    </div></div></div>
    <footer id="ft" class="bg-white border-t border-lime-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 py-10 text-gray-600">
            <div class="text-center">
                <p><strong>✅ 수정 완료!</strong> 이제 footer가 게시판 내용을 가리지 않습니다</p>
                <p>main 태그 추가 + wrapper flex 구조로 해결됨</p>
                <p>Copyright © 2019 더불어사는 삶 사단법인 희망씨.</p>
            </div>
        </div>
    </footer>
    </body>
    </html>';
}

// Add simple theme function if not exists
if (!function_exists('getThemeClass')) {
    function getThemeClass($type, $category, $shade = null) {
        $themeMapping = [
            'text' => [
                'primary' => [
                    '600' => 'text-green-600',
                    '900' => 'text-green-900'
                ],
                'text' => [
                    '400' => 'text-gray-400',
                    '500' => 'text-gray-500',
                    '600' => 'text-gray-600'
                ]
            ],
            'bg' => [
                'background' => [
                    '50' => 'bg-gray-50',
                    '100' => 'bg-gray-100'
                ],
                'warning' => [
                    '50' => 'bg-yellow-50'
                ],
                'danger' => [
                    '100' => 'bg-red-100'
                ]
            ]
        ];
        
        if (isset($themeMapping[$type][$category])) {
            if (is_array($themeMapping[$type][$category]) && $shade !== null) {
                return $themeMapping[$type][$category][$shade] ?? '';
            }
            return $themeMapping[$type][$category] ?? '';
        }
        
        return '';
    }
}
?>