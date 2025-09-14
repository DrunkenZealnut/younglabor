<?php

/**
 * 웹 라우트 정의 파일
 * MVC 시스템의 URL 라우팅 규칙을 정의합니다.
 */

// 메인 대시보드
$router->get('/', 'DashboardController@index');
$router->get('/dashboard', 'DashboardController@index');

// 게시물 관리 라우트
$router->group(['prefix' => 'posts'], function($router) {
    $router->get('/', 'PostController@index');
    $router->get('/create', 'PostController@create');
    $router->post('/', 'PostController@store');
    $router->get('/{id}', 'PostController@show');
    $router->get('/{id}/edit', 'PostController@edit');
    $router->put('/{id}', 'PostController@update');
    $router->delete('/{id}', 'PostController@destroy');
    
    // 추가 액션
    $router->post('/bulk', 'PostController@bulkAction');
    $router->get('/export', 'PostController@export');
    $router->post('/{id}/toggle-featured', 'PostController@toggleFeatured');
});

// 이벤트 관리 라우트
$router->group(['prefix' => 'events'], function($router) {
    $router->get('/', 'EventController@index');
    $router->get('/create', 'EventController@create');
    $router->post('/', 'EventController@store');
    $router->get('/{id}', 'EventController@show');
    $router->get('/{id}/edit', 'EventController@edit');
    $router->put('/{id}', 'EventController@update');
    $router->delete('/{id}', 'EventController@destroy');
    
    // 이벤트 특화 액션
    $router->post('/{id}/update-status', 'EventController@updateStatus');
    $router->get('/{id}/participants', 'EventController@participants');
    $router->post('/{id}/participants/export', 'EventController@exportParticipants');
    $router->post('/bulk', 'EventController@bulkAction');
    $router->get('/export', 'EventController@export');
    $router->get('/calendar', 'EventController@calendar');
});

// 메뉴 관리 라우트
$router->group(['prefix' => 'menus'], function($router) {
    $router->get('/', 'MenuController@index');
    $router->get('/create', 'MenuController@create');
    $router->post('/', 'MenuController@store');
    $router->get('/{id}', 'MenuController@show');
    $router->get('/{id}/edit', 'MenuController@edit');
    $router->put('/{id}', 'MenuController@update');
    $router->delete('/{id}', 'MenuController@destroy');
    
    // 메뉴 특화 액션
    $router->post('/{id}/move', 'MenuController@move');
    $router->post('/sort', 'MenuController@sort');
    $router->post('/toggle-all', 'MenuController@toggleAll');
    $router->post('/test-url', 'MenuController@testUrl');
    $router->get('/{id}/preview', 'MenuController@preview');
});

// 문의 관리 라우트
$router->group(['prefix' => 'inquiries'], function($router) {
    $router->get('/', 'InquiryController@index');
    $router->get('/{id}', 'InquiryController@view');
    $router->post('/{id}/update-status', 'InquiryController@updateStatus');
    $router->post('/{id}/add-response', 'InquiryController@addResponse');
    $router->delete('/{id}', 'InquiryController@delete');
    $router->post('/bulk', 'InquiryController@bulkAction');
    $router->get('/export', 'InquiryController@export');
    
    // API 엔드포인트
    $router->get('/api/pending', 'InquiryController@api_pending');
    $router->get('/api/stats', 'InquiryController@api_stats');
});

// 사용자 관리 라우트 (향후 확장)
$router->group(['prefix' => 'users'], function($router) {
    $router->get('/', 'UserController@index');
    $router->get('/create', 'UserController@create');
    $router->post('/', 'UserController@store');
    $router->get('/{id}', 'UserController@show');
    $router->get('/{id}/edit', 'UserController@edit');
    $router->put('/{id}', 'UserController@update');
    $router->delete('/{id}', 'UserController@destroy');
    
    // 사용자 특화 액션
    $router->post('/{id}/toggle-status', 'UserController@toggleStatus');
    $router->post('/{id}/reset-password', 'UserController@resetPassword');
    $router->post('/bulk', 'UserController@bulkAction');
    $router->get('/export', 'UserController@export');
});

// 설정 관리 라우트
$router->group(['prefix' => 'settings'], function($router) {
    $router->get('/', 'SettingsController@index');
    $router->get('/{group}', 'SettingsController@group');
    $router->post('/save', 'SettingsController@save');
    $router->post('/reset', 'SettingsController@reset');
    
    // 특정 설정 그룹
    $router->get('/general', 'SettingsController@general');
    $router->get('/email', 'SettingsController@email');
    $router->get('/security', 'SettingsController@security');
    $router->get('/performance', 'SettingsController@performance');
});

// 통계 및 분석 라우트
$router->group(['prefix' => 'analytics'], function($router) {
    $router->get('/', 'AnalyticsController@index');
    $router->get('/visitors', 'AnalyticsController@visitors');
    $router->get('/content', 'AnalyticsController@content');
    $router->get('/events', 'AnalyticsController@events');
    $router->get('/inquiries', 'AnalyticsController@inquiries');
    
    // API 엔드포인트
    $router->get('/api/dashboard-stats', 'AnalyticsController@dashboardStats');
    $router->get('/api/visitor-chart', 'AnalyticsController@visitorChart');
    $router->get('/api/popular-content', 'AnalyticsController@popularContent');
});

// 파일 관리 라우트
$router->group(['prefix' => 'files'], function($router) {
    $router->get('/', 'FileController@index');
    $router->post('/upload', 'FileController@upload');
    $router->delete('/{id}', 'FileController@delete');
    $router->get('/{id}/download', 'FileController@download');
    $router->post('/bulk-delete', 'FileController@bulkDelete');
    
    // 이미지 처리
    $router->post('/resize', 'FileController@resize');
    $router->post('/optimize', 'FileController@optimize');
});

// 시스템 관리 라우트
$router->group(['prefix' => 'system'], function($router) {
    $router->get('/', 'SystemController@index');
    $router->get('/info', 'SystemController@info');
    $router->get('/logs', 'SystemController@logs');
    $router->post('/clear-cache', 'SystemController@clearCache');
    $router->post('/optimize', 'SystemController@optimize');
    $router->get('/backup', 'SystemController@backup');
    $router->post('/maintenance', 'SystemController@toggleMaintenance');
});

// API 라우트 그룹
$router->group(['prefix' => 'api'], function($router) {
    // 대시보드 위젯용 API
    $router->get('/dashboard/recent-posts', 'ApiController@recentPosts');
    $router->get('/dashboard/upcoming-events', 'ApiController@upcomingEvents');
    $router->get('/dashboard/pending-inquiries', 'ApiController@pendingInquiries');
    $router->get('/dashboard/statistics', 'ApiController@statistics');
    
    // 검색 API
    $router->get('/search/posts', 'ApiController@searchPosts');
    $router->get('/search/events', 'ApiController@searchEvents');
    $router->get('/search/users', 'ApiController@searchUsers');
    
    // 자동완성 API
    $router->get('/autocomplete/tags', 'ApiController@autocompleteTags');
    $router->get('/autocomplete/locations', 'ApiController@autocompleteLocations');
    $router->get('/autocomplete/categories', 'ApiController@autocompleteCategories');
});

// 인증 관련 라우트 (향후 확장)
$router->group(['prefix' => 'auth'], function($router) {
    $router->get('/login', 'AuthController@showLogin');
    $router->post('/login', 'AuthController@login');
    $router->post('/logout', 'AuthController@logout');
    $router->get('/profile', 'AuthController@profile');
    $router->post('/profile', 'AuthController@updateProfile');
});

// 개발/테스트용 라우트 (production에서는 비활성화)
if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
    $router->group(['prefix' => 'dev'], function($router) {
        $router->get('/routes', 'DevController@routes');
        $router->get('/phpinfo', 'DevController@phpinfo');
        $router->get('/test-db', 'DevController@testDatabase');
        $router->get('/test-email', 'DevController@testEmail');
        $router->get('/generate-test-data', 'DevController@generateTestData');
    });
}

// 에러 페이지 라우트
$router->get('/error/403', function() {
    http_response_code(403);
    include __DIR__ . '/../views/errors/403.php';
});

$router->get('/error/500', function() {
    http_response_code(500);
    include __DIR__ . '/../views/errors/500.php';
});

// 기본 404 처리는 Router 클래스에서 자동 처리됨