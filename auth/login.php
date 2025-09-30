<?php
/**
 * 사용자 로그인 페이지
 * User Login Page
 */

// 환경 설정 로드
require_once __DIR__ . '/../bootstrap/app.php';

// 이미 로그인된 사용자는 메인 페이지로 리다이렉트
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) {
    header('Location: /');
    exit;
}

// POST 요청 처리 (로그인)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/email_auth_handler.php';

    $result = handleUserLogin($_POST);

    if ($result['success']) {
        // 리다이렉트 URL 처리
        $redirect_url = $_SESSION['login_redirect'] ?? '/';
        unset($_SESSION['login_redirect']);

        header('Location: ' . $redirect_url);
        exit;
    } else {
        $error_message = $result['message'];
        $login_attempts = $result['attempts'] ?? 0;
    }
}

// 페이지 제목 설정
$pageTitle = '로그인 - 청년노동자인권센터';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --color-primary: #3a7a4e;
            --color-secondary: #16a34a;
            --color-success: #65a30d;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="flex items-center justify-center min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- 로고 및 헤더 -->
            <div class="text-center">
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    로그인
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    청년노동자인권센터 계정으로 로그인하세요
                </p>
            </div>

            <!-- 로그인 폼 -->
            <div class="bg-white py-8 px-6 shadow rounded-lg">
                <!-- 성공 메시지 -->
                <?php if (isset($_GET['registered'])): ?>
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-800">회원가입이 완료되었습니다. 로그인해주세요.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['logout'])): ?>
                    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-800">성공적으로 로그아웃되었습니다.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['access_denied'])): ?>
                    <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-800">로그인이 필요합니다. 계정으로 로그인해주세요.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 에러 메시지 -->
                <?php if (isset($error_message)): ?>
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-800"><?= htmlspecialchars($error_message) ?></p>
                                <?php if (isset($login_attempts) && $login_attempts > 0): ?>
                                    <p class="text-xs text-red-600 mt-1">로그인 실패 횟수: <?= $login_attempts ?>/5</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 소셜 로그인 -->
                <div class="space-y-3 mb-6">
                    <div class="text-center">
                        <span class="text-sm text-gray-500">소셜 계정으로 로그인</span>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <!-- 구글 로그인 -->
                        <button type="button" onclick="loginWithGoogle()"
                                class="flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                            <svg class="w-5 h-5" viewBox="0 0 24 24">
                                <path fill="#4285f4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34a853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#fbbc05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="#ea4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                        </button>

                        <!-- 네이버 로그인 -->
                        <button type="button" onclick="loginWithNaver()"
                                class="flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-white bg-green-500 hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                            <span class="font-bold text-lg">N</span>
                        </button>

                        <!-- 카카오 로그인 -->
                        <button type="button" onclick="loginWithKakao()"
                                class="flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-900 bg-yellow-400 hover:bg-yellow-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors duration-200">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 3c5.799 0 10.5 3.664 10.5 8.185 0 4.52-4.701 8.184-10.5 8.184a13.5 13.5 0 0 1-1.727-.11l-4.408 2.883c-.501.265-.678.236-.472-.413l.892-3.678c-2.88-1.46-4.785-3.99-4.785-6.866C1.5 6.665 6.201 3 12 3z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- 구분선 -->
                <div class="relative mb-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">또는 이메일로 로그인</span>
                    </div>
                </div>

                <!-- 일반 로그인 폼 -->
                <form method="POST" action="/auth/login.php" class="space-y-6">
                    <!-- CSRF 토큰 -->
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <!-- 이메일 또는 사용자명 -->
                    <div>
                        <label for="login_id" class="block text-sm font-medium text-gray-700">
                            이메일 또는 사용자명
                        </label>
                        <input type="text"
                               id="login_id"
                               name="login_id"
                               required
                               value="<?= htmlspecialchars($_POST['login_id'] ?? '') ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                               placeholder="이메일 주소 또는 사용자명을 입력하세요">
                    </div>

                    <!-- 비밀번호 -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            비밀번호
                        </label>
                        <input type="password"
                               id="password"
                               name="password"
                               required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                               placeholder="비밀번호를 입력하세요">
                    </div>

                    <!-- 로그인 유지 옵션 -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember_me"
                                   name="remember_me"
                                   type="checkbox"
                                   class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            <label for="remember_me" class="ml-2 block text-sm text-gray-900">
                                로그인 상태 유지
                            </label>
                        </div>

                        <div class="text-sm">
                            <a href="/auth/forgot-password.php" class="font-medium text-green-600 hover:text-green-500">
                                비밀번호를 잊으셨나요?
                            </a>
                        </div>
                    </div>

                    <!-- 로그인 버튼 -->
                    <div>
                        <button type="submit"
                                class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                            로그인
                        </button>
                    </div>

                    <!-- 회원가입 링크 -->
                    <div class="text-center">
                        <p class="text-sm text-gray-600">
                            아직 계정이 없으신가요?
                            <a href="/auth/register.php" class="font-medium text-green-600 hover:text-green-500">
                                회원가입하기
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 소셜 로그인 스크립트 -->
    <script>
        function loginWithGoogle() {
            // Google OAuth 연동 (구현 예정)
            alert('구글 로그인 기능은 준비 중입니다.');
            // window.location.href = '/auth/social/google';
        }

        function loginWithNaver() {
            // Naver OAuth 연동 (구현 예정)
            alert('네이버 로그인 기능은 준비 중입니다.');
            // window.location.href = '/auth/social/naver';
        }

        function loginWithKakao() {
            // Kakao OAuth 연동 (구현 예정)
            alert('카카오 로그인 기능은 준비 중입니다.');
            // window.location.href = '/auth/social/kakao';
        }

        // 로그인 폼 검증
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const loginId = document.getElementById('login_id');
            const password = document.getElementById('password');

            form.addEventListener('submit', function(e) {
                if (!loginId.value.trim() || !password.value.trim()) {
                    e.preventDefault();
                    alert('이메일/사용자명과 비밀번호를 모두 입력해주세요.');
                }
            });
        });
    </script>
</body>
</html>