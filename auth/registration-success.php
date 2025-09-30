<?php
/**
 * 회원가입 성공 페이지
 * Registration Success Page
 */

// 환경 설정 로드
require_once __DIR__ . '/../bootstrap/app.php';

// 회원가입 성공 여부 확인
if (!isset($_SESSION['registration_success']) || !$_SESSION['registration_success']) {
    header('Location: ' . env('BASE_PATH', '') . '/auth/register.php');
    exit;
}

$email = $_SESSION['registration_email'] ?? '';

// 세션 정리
unset($_SESSION['registration_success']);
unset($_SESSION['registration_email']);

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
    <title>회원가입 완료 - 청년노동자인권센터</title>
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
            <!-- 성공 아이콘 -->
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100">
                    <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    회원가입 완료!
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    청년노동자인권센터에 가입해주셔서 감사합니다
                </p>
            </div>

            <!-- 메인 컨텐츠 -->
            <div class="bg-white py-8 px-6 shadow rounded-lg">
                <!-- 이메일 인증 안내 -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">이메일 인증이 필요합니다</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>회원가입이 완료되었지만, 서비스 이용을 위해서는 이메일 인증이 필요합니다.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 다음 단계 안내 -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900">다음 단계:</h3>

                    <div class="space-y-3">
                        <!-- 1단계 -->
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-600 font-semibold text-sm">
                                    1
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">이메일 확인</p>
                                <p class="text-sm text-gray-600">
                                    <?php if ($email): ?>
                                        <strong><?= htmlspecialchars($email) ?></strong>로 인증 메일이 발송되었습니다.
                                    <?php else: ?>
                                        가입 시 입력한 이메일로 인증 메일이 발송되었습니다.
                                    <?php endif ?>
                                </p>
                            </div>
                        </div>

                        <!-- 2단계 -->
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-600 font-semibold text-sm">
                                    2
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">인증 링크 클릭</p>
                                <p class="text-sm text-gray-600">
                                    이메일에 포함된 "이메일 인증하기" 버튼을 클릭해주세요.
                                </p>
                            </div>
                        </div>

                        <!-- 3단계 -->
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-600 font-semibold text-sm">
                                    3
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">로그인 및 서비스 이용</p>
                                <p class="text-sm text-gray-600">
                                    인증 완료 후 로그인하여 모든 서비스를 이용하실 수 있습니다.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 액션 버튼들 -->
                <div class="mt-8 space-y-3">
                    <!-- 로그인 페이지로 가기 -->
                    <a href="<?= env('BASE_PATH', '') ?>/auth/login.php"
                       class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                        로그인 페이지로 가기
                    </a>

                    <!-- 메인 페이지로 가기 -->
                    <a href="<?= env('BASE_PATH', '') ?>/"
                       class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                        메인 페이지로 가기
                    </a>
                </div>

                <!-- 문제 해결 -->
                <div class="mt-8 p-4 bg-gray-50 rounded-md">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">이메일이 오지 않나요?</h4>
                    <div class="text-xs text-gray-600 space-y-1">
                        <p>• 스팸 폴더를 확인해주세요.</p>
                        <p>• 이메일 주소가 정확한지 확인해주세요.</p>
                        <p>• 최대 10분 정도 소요될 수 있습니다.</p>
                    </div>
                    <div class="mt-3">
                        <button onclick="resendEmail()"
                                class="text-sm text-blue-600 hover:text-blue-500 font-medium">
                            인증 메일 다시 보내기
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 재발송 모달 -->
    <div id="resendModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">인증 메일 다시 보내기</h3>
                <form id="resendForm" class="space-y-4">
                    <div>
                        <label for="resend_email" class="block text-sm font-medium text-gray-700">
                            이메일 주소
                        </label>
                        <input type="email" id="resend_email" name="email" required
                               value="<?= htmlspecialchars($email) ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>
                    <div class="flex space-x-3">
                        <button type="submit"
                                class="flex-1 bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                            전송
                        </button>
                        <button type="button" onclick="closeResendModal()"
                                class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            취소
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function resendEmail() {
            document.getElementById('resendModal').classList.remove('hidden');
        }

        function closeResendModal() {
            document.getElementById('resendModal').classList.add('hidden');
        }

        document.getElementById('resendForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const email = document.getElementById('resend_email').value;

            // AJAX 요청으로 인증 메일 재발송
            fetch('/auth/resend-verification.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email: email
                })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    closeResendModal();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('오류가 발생했습니다. 다시 시도해주세요.');
            });
        });

        // 자동으로 5초 후에 새로고침 버튼 표시
        setTimeout(function() {
            const refreshButton = document.createElement('button');
            refreshButton.textContent = '페이지 새로고침';
            refreshButton.className = 'mt-2 text-sm text-gray-500 hover:text-gray-700';
            refreshButton.onclick = function() { location.reload(); };

            const container = document.querySelector('.bg-gray-50.rounded-md');
            if (container) {
                container.appendChild(refreshButton);
            }
        }, 5000);

        // 모달 외부 클릭 시 닫기
        document.getElementById('resendModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeResendModal();
            }
        });
    </script>
</body>
</html>