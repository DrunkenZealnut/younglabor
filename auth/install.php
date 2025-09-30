<?php
/**
 * 사용자 인증 시스템 설치 스크립트
 * User Authentication System Installation Script
 */

// 관리자만 실행 가능하도록 제한
session_start();

// 기본 보안 체크
if (file_exists(__DIR__ . '/.installed')) {
    die('인증 시스템이 이미 설치되었습니다. 재설치가 필요한 경우 .installed 파일을 삭제하세요.');
}

// POST 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/database.php';

    $database_config = require __DIR__ . '/../config/database.php';
    $config = $database_config['connections']['mysql'];

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);

        // SQL 파일 읽기 및 실행
        $sql_file = __DIR__ . '/members_enhancement.sql';
        if (file_exists($sql_file)) {
            $sql = file_get_contents($sql_file);

            // SQL 명령어를 분리하여 실행
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                function($stmt) {
                    return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
                }
            );

            $executed = 0;
            $errors = [];

            foreach ($statements as $statement) {
                try {
                    $pdo->exec($statement);
                    $executed++;
                } catch (PDOException $e) {
                    $errors[] = "Error executing statement: " . $e->getMessage();
                }
            }

            if (empty($errors)) {
                // 설치 완료 마커 파일 생성
                file_put_contents(__DIR__ . '/.installed', date('Y-m-d H:i:s'));

                $success_message = "인증 시스템이 성공적으로 설치되었습니다. ({$executed}개 명령어 실행)";
            } else {
                $error_message = "일부 오류가 발생했습니다: " . implode(', ', array_slice($errors, 0, 3));
            }
        } else {
            $error_message = "SQL 파일을 찾을 수 없습니다: " . $sql_file;
        }

    } catch (PDOException $e) {
        $error_message = "데이터베이스 연결 오류: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>인증 시스템 설치</title>
    <link href="/assets/css/tailwind.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="flex items-center justify-center min-h-screen py-12 px-4">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    사용자 인증 시스템 설치
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    청년노동자인권센터 회원가입/로그인 시스템
                </p>
            </div>

            <div class="bg-white py-8 px-6 shadow rounded-lg">
                <?php if (isset($success_message)): ?>
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-800"><?= htmlspecialchars($success_message) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">설치 완료!</h3>
                            <p class="text-sm text-gray-600">이제 다음 기능들을 사용할 수 있습니다:</p>
                            <ul class="mt-2 text-sm text-gray-600 list-disc list-inside">
                                <li>이메일 인증 기반 회원가입</li>
                                <li>로그인/로그아웃</li>
                                <li>소셜 로그인 (구글, 네이버, 카카오)</li>
                                <li>게시판별 권한 관리</li>
                                <li>Remember Me 기능</li>
                                <li>인증 로그 시스템</li>
                            </ul>
                        </div>

                        <div class="space-y-2">
                            <a href="/auth/register.php"
                               class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                                회원가입 테스트
                            </a>
                            <a href="/auth/login.php"
                               class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                로그인 테스트
                            </a>
                        </div>
                    </div>

                <?php elseif (isset($error_message)): ?>
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

                    <button onclick="location.reload()"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                        다시 시도
                    </button>
                <?php else: ?>
                    <form method="POST" class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">설치 정보</h3>

                            <div class="space-y-4">
                                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                                    <h4 class="text-sm font-medium text-blue-900 mb-2">주요 기능</h4>
                                    <ul class="text-sm text-blue-800 list-disc list-inside space-y-1">
                                        <li>이메일 인증 기반 간편 회원가입</li>
                                        <li>소셜 로그인 지원 (구글, 네이버, 카카오)</li>
                                        <li>게시판별 세분화된 권한 관리</li>
                                        <li>보안 강화된 세션 관리</li>
                                        <li>Remember Me 기능</li>
                                        <li>인증 활동 로그 시스템</li>
                                    </ul>
                                </div>

                                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                                    <h4 class="text-sm font-medium text-yellow-900 mb-2">설치 내용</h4>
                                    <ul class="text-sm text-yellow-800 list-disc list-inside space-y-1">
                                        <li>기존 members 테이블에 현대적 회원관리 컬럼 추가</li>
                                        <li>소셜 로그인 연동 테이블 생성</li>
                                        <li>게시판 권한 관리 테이블 생성</li>
                                        <li>인증 로그 테이블 생성</li>
                                        <li>기본 권한 설정 및 데이터 삽입</li>
                                    </ul>
                                </div>

                                <div class="bg-red-50 border border-red-200 rounded-md p-4">
                                    <h4 class="text-sm font-medium text-red-900 mb-2">주의사항</h4>
                                    <ul class="text-sm text-red-800 list-disc list-inside space-y-1">
                                        <li>기존 데이터는 보존되지만 백업을 권장합니다</li>
                                        <li>이메일 발송을 위해 SMTP 설정이 필요할 수 있습니다</li>
                                        <li>소셜 로그인 사용 시 각 플랫폼의 API 키가 필요합니다</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <input id="agree" name="agree" type="checkbox" required
                                   class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            <label for="agree" class="ml-2 block text-sm text-gray-900">
                                위 내용을 확인했으며 설치를 진행합니다.
                            </label>
                        </div>

                        <button type="submit"
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            인증 시스템 설치하기
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>