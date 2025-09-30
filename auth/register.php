<?php
/**
 * 사용자 회원가입 페이지
 * User Registration Page
 */

// 환경 설정 로드
require_once __DIR__ . '/../bootstrap/app.php';

// CSRF 토큰 생성
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 이미 로그인된 사용자는 메인 페이지로 리다이렉트
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) {
    header('Location: ' . env('BASE_PATH', '') . '/');
    exit;
}

// POST 요청 처리 (회원가입)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 디버깅 활성화
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    require_once __DIR__ . '/email_auth_handler.php';

    try {
        $result = handleUserRegistration($_POST);

        if ($result['success']) {
            $_SESSION['registration_success'] = true;
            $_SESSION['registration_email'] = $result['email'] ?? '';
            header('Location: ' . env('BASE_PATH', '') . '/auth/registration-success.php');
            exit;
        } else {
            $error_message = $result['message'];
            $form_data = $_POST; // 폼 데이터 유지
        }
    } catch (Exception $e) {
        // 화면에 직접 출력
        die('<h1>회원가입 오류</h1><pre>' .
            '오류 메시지: ' . $e->getMessage() . "\n" .
            '파일: ' . $e->getFile() . "\n" .
            '라인: ' . $e->getLine() . "\n\n" .
            '스택 트레이스:\n' . $e->getTraceAsString() .
            '</pre>');
    }
}

// 테마 설정 로드
$theme_config_file = __DIR__ . '/../theme/natural-green/config/theme.php';
if (file_exists($theme_config_file)) {
    $theme_config = require $theme_config_file;
} else {
    // 기본 색상 설정
    $theme_config = [
        'primary' => '#84cc16',
        'secondary' => '#16a34a',
        'success' => '#65a30d',
    ];
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입 - 청년노동자인권센터</title>
    <link href="<?= env('BASE_PATH', '') ?>/css/tailwind-optimized.css" rel="stylesheet">
    <style>
        :root {
            --color-primary: <?= $theme_config['primary'] ?? '#84cc16' ?>;
            --color-secondary: <?= $theme_config['secondary'] ?? '#16a34a' ?>;
            --color-success: <?= $theme_config['success'] ?? '#65a30d' ?>;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="flex items-center justify-center min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- 로고 및 헤더 -->
            <div class="text-center">
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    회원가입
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    청년노동자인권센터에 오신 것을 환영합니다
                </p>
            </div>

            <!-- 회원가입 폼 -->
            <div class="bg-white py-8 px-6 shadow rounded-lg">
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
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= env('BASE_PATH', '') ?>/auth/register.php" class="space-y-6">
                    <!-- CSRF 토큰 -->
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <!-- 이메일 -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            이메일 주소 *
                        </label>
                        <input type="email"
                               id="email"
                               name="email"
                               required
                               value="<?= htmlspecialchars($form_data['email'] ?? '') ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                               placeholder="example@email.com">
                        <p class="mt-1 text-xs text-gray-500">이메일 주소로 인증 메일이 발송됩니다.</p>
                    </div>

                    <!-- 이름 (닉네임) -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            이름(닉네임) *
                        </label>
                        <input type="text"
                               id="name"
                               name="name"
                               required
                               value="<?= htmlspecialchars($form_data['name'] ?? '') ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                               placeholder="활동에 사용할 이름을 입력해주세요">
                        <p class="mt-1 text-xs text-gray-500">2-20자 사이로 입력해주세요. 나중에 변경 가능합니다.</p>
                    </div>

                    <!-- 비밀번호 -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            비밀번호 *
                        </label>
                        <input type="password"
                               id="password"
                               name="password"
                               required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                               placeholder="8자리 이상, 영문+숫자+특수문자 조합">
                        <p class="mt-1 text-xs text-gray-500">8자리 이상, 영문자, 숫자, 특수문자를 모두 포함해야 합니다.</p>
                    </div>

                    <!-- 비밀번호 확인 -->
                    <div>
                        <label for="password_confirm" class="block text-sm font-medium text-gray-700">
                            비밀번호 확인 *
                        </label>
                        <input type="password"
                               id="password_confirm"
                               name="password_confirm"
                               required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                               placeholder="위와 동일한 비밀번호를 입력하세요">
                    </div>


                    <!-- 개인정보 처리방침 동의 -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="agree_privacy"
                                   name="agree_privacy"
                                   type="checkbox"
                                   required
                                   class="focus:ring-green-500 h-4 w-4 text-green-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="agree_privacy" class="font-medium text-gray-700">
                                개인정보 처리방침에 동의합니다 *
                            </label>
                            <p class="text-gray-500">
                                <a href="<?= env('BASE_PATH', '') ?>/privacy-policy" target="_blank" class="text-green-600 hover:text-green-500">
                                    개인정보 처리방침 보기
                                </a>
                            </p>
                        </div>
                    </div>

                    <!-- 이용약관 동의 -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="agree_terms"
                                   name="agree_terms"
                                   type="checkbox"
                                   required
                                   class="focus:ring-green-500 h-4 w-4 text-green-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="agree_terms" class="font-medium text-gray-700">
                                이용약관에 동의합니다 *
                            </label>
                            <p class="text-gray-500">
                                <a href="<?= env('BASE_PATH', '') ?>/terms-of-service" target="_blank" class="text-green-600 hover:text-green-500">
                                    이용약관 보기
                                </a>
                            </p>
                        </div>
                    </div>

                    <!-- 회원가입 버튼 -->
                    <div>
                        <button type="submit"
                                class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                            이메일 인증으로 회원가입
                        </button>
                        <p class="mt-2 text-xs text-center text-gray-500">
                            가입 후 이메일 인증을 완료해야 서비스를 이용할 수 있습니다.
                        </p>
                    </div>

                    <!-- 로그인 링크 -->
                    <div class="text-center">
                        <p class="text-sm text-gray-600">
                            이미 계정이 있으신가요?
                            <a href="<?= env('BASE_PATH', '') ?>/auth/login.php" class="font-medium text-green-600 hover:text-green-500">
                                로그인하기
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 클라이언트 사이드 유효성 검사 -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const password = document.getElementById('password');
            const passwordConfirm = document.getElementById('password_confirm');
            const username = document.getElementById('username');

            // 비밀번호 확인 검증
            function validatePasswordMatch() {
                if (password.value !== passwordConfirm.value) {
                    passwordConfirm.setCustomValidity('비밀번호가 일치하지 않습니다.');
                } else {
                    passwordConfirm.setCustomValidity('');
                }
            }

            // 비밀번호 강도 검증
            function validatePasswordStrength() {
                const value = password.value;
                const hasLetter = /[a-zA-Z]/.test(value);
                const hasNumber = /\d/.test(value);
                const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(value);
                const isLongEnough = value.length >= 8;

                if (!isLongEnough || !hasLetter || !hasNumber || !hasSpecial) {
                    password.setCustomValidity('8자리 이상, 영문자, 숫자, 특수문자를 모두 포함해야 합니다.');
                } else {
                    password.setCustomValidity('');
                }
            }

            // 이름 검증 (간소화)
            function validateName() {
                const value = document.getElementById('name').value;
                const isValid = value.length >= 2 && value.length <= 20;

                if (!isValid) {
                    document.getElementById('name').setCustomValidity('이름은 2-20자 사이여야 합니다.');
                } else {
                    document.getElementById('name').setCustomValidity('');
                }
            }

            // 이벤트 리스너 등록
            password.addEventListener('input', validatePasswordStrength);
            passwordConfirm.addEventListener('input', validatePasswordMatch);
            document.getElementById('name').addEventListener('input', validateName);

            // 폼 제출 시 최종 검증
            form.addEventListener('submit', function(e) {
                validatePasswordStrength();
                validatePasswordMatch();
                validateName();

                if (!form.checkValidity()) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>