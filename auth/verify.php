<?php
/**
 * 이메일 인증 페이지
 * Email Verification Page
 */

// 환경 설정 로드
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/email_auth_handler.php';

$message = '';
$success = false;

// 토큰이 있는 경우 인증 처리
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $result = handleEmailVerification($_GET['token']);
    $message = $result['message'];
    $success = $result['success'];
}

// 테마 설정 로드
$theme_config = require __DIR__ . '/../theme/natural-green/config.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>이메일 인증 - 청년노동자인권센터</title>
    <link href="/assets/css/tailwind.css" rel="stylesheet">
    <style>
        :root {
            <?php foreach ($theme_config['colors'] as $key => $value): ?>
            --color-<?= $key ?>: <?= $value ?>;
            <?php endforeach; ?>
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="flex items-center justify-center min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- 로고 및 헤더 -->
            <div class="text-center">
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    이메일 인증
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    청년노동자인권센터 회원가입을 완료하세요
                </p>
            </div>

            <!-- 결과 메시지 -->
            <div class="bg-white py-8 px-6 shadow rounded-lg">
                <?php if (!empty($message)): ?>
                    <div class="mb-6 p-4 rounded-md <?= $success ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' ?>">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <?php if ($success): ?>
                                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                <?php else: ?>
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                <?php endif; ?>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm <?= $success ? 'text-green-800' : 'text-red-800' ?>">
                                    <?= htmlspecialchars($message) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- 토큰이 없는 경우 -->
                    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-800">
                                    유효한 인증 링크가 아닙니다. 이메일에서 전송된 링크를 사용해주세요.
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 액션 버튼들 -->
                <div class="space-y-4">
                    <?php if ($success): ?>
                        <!-- 인증 성공 시 로그인 버튼 -->
                        <div>
                            <a href="/auth/login.php"
                               class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                                로그인하러 가기
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- 인증 실패 시 다시 시도 옵션들 -->
                        <div class="space-y-3">
                            <!-- 새로운 인증 메일 요청 버튼 -->
                            <button type="button" onclick="resendVerificationEmail()"
                                    class="group relative w-full flex justify-center py-2 px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                                인증 메일 다시 보내기
                            </button>

                            <!-- 회원가입 다시 하기 -->
                            <a href="/auth/register.php"
                               class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                회원가입 다시 하기
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- 메인 페이지로 가기 -->
                    <div class="text-center">
                        <a href="/" class="text-sm text-gray-600 hover:text-gray-500">
                            메인 페이지로 돌아가기
                        </a>
                    </div>
                </div>

                <!-- 도움말 -->
                <div class="mt-8 p-4 bg-gray-50 rounded-md">
                    <h3 class="text-sm font-medium text-gray-900 mb-2">이메일 인증 관련 문의</h3>
                    <div class="text-xs text-gray-600 space-y-1">
                        <p>• 인증 메일이 오지 않는 경우 스팸 폴더를 확인해주세요.</p>
                        <p>• 인증 링크는 24시간 동안만 유효합니다.</p>
                        <p>• 문제가 지속되면 <a href="/contact" class="text-blue-600 hover:text-blue-500">고객센터</a>로 문의해주세요.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 인증 메일 재발송 모달 -->
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
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                               placeholder="가입 시 사용한 이메일을 입력하세요">
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
        function resendVerificationEmail() {
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

        // 모달 외부 클릭 시 닫기
        document.getElementById('resendModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeResendModal();
            }
        });
    </script>
</body>
</html>